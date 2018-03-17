<?php
/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Controller\Adminhtml\Infusionsoft;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Renegade\Infusionsoft\Api\TokenRepositoryInterface;
use Renegade\Infusionsoft\Service\InfusionsoftService;

class Deauthorize extends Action
{
    /**
     * Context object
     *
     * @var Context
     */
    private $context;

    /**
     * Token repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * Deauthorize constructor.
     *
     * @param Context                  $context
     * @param TokenRepositoryInterface $tokenRepository
     * @param ScopeConfigInterface     $scopeConfig
     */
    public function __construct(
        Context $context,
        TokenRepositoryInterface $tokenRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->tokenRepository = $tokenRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute controller action
     *
     * @return void
     */
    public function execute()
    {
        $infusionsoftClientId = $this->scopeConfig
            ->getValue(InfusionsoftService::XML_PATH_RENEGADE_INFUSIONSOFT_CONFIG_CLIENT_ID);

        if ($infusionsoftClientId) {
            try {
                $this->tokenRepository->deleteByClientId($infusionsoftClientId);
                $this->messageManager->addSuccessMessage(__('Token was successfully deleted'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('An error occurred. Token was not deleted.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No Infusionsoft client id'));
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
