<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Renegade\Infusionsoft\Api\Data\TokenInterface;
use Renegade\Infusionsoft\Api\TokenRepositoryInterface;
use Renegade\Infusionsoft\Model\ResourceModel\Token as TokenResource;

/**
 * Class TokenRepository
 *
 * @package Renegade\Infusionsoft\Model
 */
class TokenRepository implements TokenRepositoryInterface
{
    /**
     * Token registry
     *
     * @var TokenInterface[]
     */
    protected $registry = [];

    /**
     * Token resource
     *
     * @var TokenResource
     */
    private $tokenResource;

    /**
     * Token factory
     *
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * TokenRepository constructor.
     *
     * @param TokenResource $tokenResource
     * @param TokenFactory  $tokenFactory
     */
    public function __construct(
        TokenResource $tokenResource,
        TokenFactory $tokenFactory
    ) {
        $this->tokenResource = $tokenResource;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * Create or update a token
     *
     * @param TokenInterface $token
     *
     * @return TokenInterface
     */
    public function save(TokenInterface $token)
    {
        $this->tokenResource->save($token);

        $this->registry[$token->getId()] = $token;

        return $this->registry[$token->getId()];
    }

    /**
     * Retrieve by id
     *
     * @param int $id
     *
     * @throws NoSuchEntityException
     *
     * @return TokenInterface
     */
    public function getById($id)
    {
        if (!isset($this->registry[$id])) {
            /** @var Token $token */
            $token = $this->tokenFactory->create();

            $this->tokenResource->load($token, $id);

            if (!$token->getId()) {
                throw new NoSuchEntityException(__('Token with id "%1" does not exist.', $id));
            }
        }

        return $this->registry[$id];
    }

    /**
     * Retrieve by client id
     *
     * @param string $clientId
     *
     * @throws NoSuchEntityException
     *
     * @return TokenInterface
     */
    public function getByClientId($clientId)
    {
        foreach ($this->registry as $key => $token) {
            if ($clientId == $token->getClientId()) {
                return $token;
            }
        }

        /** @var Token $token */
        $token = $this->tokenFactory->create();

        $this->tokenResource->load($token, $clientId, 'client_id');

        if (!$token->getId()) {
            throw new NoSuchEntityException(__('Token with client_id "%1" does not exist.', $clientId));
        }

        $this->registry[$token->getId()] = $token;

        return $this->registry[$token->getId()];
    }

    /**
     * Delete token
     *
     * @param TokenInterface $token
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function delete(TokenInterface $token)
    {
        try {
            $this->tokenResource->delete($token);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the token: %1', $exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Delete by client id
     *
     * @param string $clientId
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function deleteByClientId($clientId)
    {
        return $this->delete($this->getByClientId($clientId));
    }
}
