<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Service;

use Infusionsoft\Infusionsoft;
use Infusionsoft\InfusionsoftFactory;
use Infusionsoft\Token;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Intl\DateTimeFactory;
use Psr\Log\LoggerInterface;
use Renegade\Infusionsoft\Api\Data\TokenInterface;
use Renegade\Infusionsoft\Api\Data\TokenInterfaceFactory;
use Renegade\Infusionsoft\Api\TokenRepositoryInterface;

/**
 * Class InfusionsoftService
 *
 * @package Renegade\Infusionsoft\Service
 */
class InfusionsoftService
{
    const XML_PATH_RENEGADE_INFUSIONSOFT_GENERAL_ENABLED      = 'renegade_infusionsoft/general/enabled';
    const XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_ID     = 'renegade_infusionsoft/config/client_id';
    const XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_SECRET = 'renegade_infusionsoft/config/client_secret';

    /**
     * Infusionsoft instance
     *
     * @var Infusionsoft
     */
    private $infusionsoft;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Logger class
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Backend URL builder
     *
     * @var UrlInterface
     */
    private $backendUrlBuilder;

    /**
     * HTTP Request object
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Core Config resource object
     *
     * @var Config
     */
    private $resourceConfig;

    /**
     * Encryptor object
     *
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Token repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Token factory
     *
     * @var TokenInterfaceFactory
     */
    private $tokenFactory;

    /** @var InfusionsoftFactory */
    private $infusionsoftFactory;

    /** @var DateTimeFactory */
    private $dateTimeFactory;

    /**
     * InfusionsoftService constructor.
     *
     * @param RequestInterface         $request
     * @param ScopeConfigInterface     $scopeConfig
     * @param Config                   $resourceConfig
     * @param UrlInterface             $backendUrlBuilder
     * @param LoggerInterface          $logger
     * @param EncryptorInterface       $encryptor
     * @param TokenRepositoryInterface $tokenRepository
     * @param TokenInterfaceFactory    $tokenFactory
     * @param InfusionsoftFactory      $infusionsoftFactory
     * @param DateTimeFactory          $dateTimeFactory
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        Config $resourceConfig,
        UrlInterface $backendUrlBuilder,
        LoggerInterface $logger,
        EncryptorInterface $encryptor,
        TokenRepositoryInterface $tokenRepository,
        TokenInterfaceFactory $tokenFactory,
        InfusionsoftFactory $infusionsoftFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->backendUrlBuilder = $backendUrlBuilder;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->tokenRepository = $tokenRepository;
        $this->tokenFactory = $tokenFactory;
        $this->infusionsoftFactory = $infusionsoftFactory;
        $this->dateTimeFactory = $dateTimeFactory;

        $this->initToken();
    }

    /**
     * Get Infusionsoft instance
     *
     * @return Infusionsoft
     * @throws \Exception
     */
    public function getInfusionsoft()
    {
        if (!$this->infusionsoft) {
            $clientId = $this->scopeConfig->getValue(self::XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_ID);
            $clientSecret = $this->encryptor->decrypt(
                $this->scopeConfig->getValue(self::XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_SECRET)
            );

            if (!$clientId || !$clientSecret) {
                $this->logger->error(__('Missing configuration value(s).'));
            }

            $redirectUri = $this->backendUrlBuilder->getUrl('renegade/infusionsoft/authorizationCallback');

            $this->infusionsoft = $this->infusionsoftFactory->create(
                [
                    'config' =>
                        [
                            'clientId'     => $clientId,
                            'clientSecret' => $clientSecret,
                            'redirectUri'  => $redirectUri,
                        ],
                ]
            );
        }

        return $this->infusionsoft;
    }

    /**
     * Has valid token object
     *
     * @return bool
     */
    public function hasValidToken()
    {
        return ($this->getInfusionsoft()->getToken() instanceof Token) && !$this->getInfusionsoft()->isTokenExpired();
    }

    /**
     * Get Authorization URL
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return $this->getInfusionsoft()->getAuthorizationUrl();
    }

    /**
     * Get stored token
     *
     * @return Token|null
     */
    public function getStoredToken()
    {
        try {
            $storedToken = $this->tokenRepository->getByClientId(
                $this->scopeConfig->getValue(self::XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_ID)
            );

            if ($storedToken) {
                return unserialize($this->encryptor->decrypt($storedToken->getToken()));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    /**
     * Process authorization response from Infusionsoft
     *
     * @return void
     */
    public function processAuthorizationResponse()
    {
        $code = $this->request->getParam('code');

        if ($code && !$this->getInfusionsoft()->getToken()) {
            /** @var Token $token */
            $token = $this->getInfusionsoft()->requestAccessToken($code);

            $this->storeToken($token);
        }
    }

    /**
     * Save token to database
     *
     * @param Token $infusionsoftToken
     *
     * @return void
     */
    public function storeToken(Token $infusionsoftToken)
    {
        $clientId = $this->scopeConfig->getValue(self::XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_ID);

        try {
            $storedToken = $this->tokenRepository->getByClientId($clientId);
        } catch (NoSuchEntityException $e) {
            /** @var TokenInterface $storedToken */
            $storedToken = $this->tokenFactory->create();

            $storedToken->setClientId($clientId);
        }

        $expiresAt = $this->dateTimeFactory->create();
        $expiresAt->setTimestamp($infusionsoftToken->getEndOfLife());

        $storedToken
            ->setToken($this->encryptor->encrypt(serialize($infusionsoftToken)))
            ->setExpiresAt($expiresAt);

        $this->tokenRepository->save($storedToken);
    }

    /**
     * Refresh access token
     *
     * @throws AlreadyExistsException
     *
     * @return bool
     */
    public function refreshToken()
    {
        /** @var Token $newToken */
        $newToken = $this->getInfusionsoft()->refreshAccessToken();

        $this->storeToken($newToken);

        return true;
    }

    /**
     * Init token
     *
     * @return void
     */
    protected function initToken()
    {
        $this->getInfusionsoft()->setToken($this->getStoredToken());
    }

    /**
     * Is Infusionsoft integration enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_RENEGADE_INFUSIONSOFT_GENERAL_ENABLED);
    }
}
