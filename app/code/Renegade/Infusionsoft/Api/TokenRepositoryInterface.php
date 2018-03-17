<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Renegade\Infusionsoft\Api\Data\TokenInterface;

/**
 * Class TokenRepositoryInterface
 *
 * @package Renegade\Infusionsoft\Api
 */
interface TokenRepositoryInterface
{
    /**
     * Create or update a token
     *
     * @param TokenInterface $token
     *
     * @return TokenInterface
     */
    public function save(TokenInterface $token);

    /**
     * Retrieve by id
     *
     * @param int $id
     *
     * @throws NoSuchEntityException
     *
     * @return TokenInterface
     */
    public function getById($id);

    /**
     * Retrieve by client id
     *
     * @param string $clientId
     *
     * @throws NoSuchEntityException
     *
     * @return TokenInterface
     */
    public function getByClientId($clientId);

    /**
     * Delete token
     *
     * @param TokenInterface $token
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function delete(TokenInterface $token);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function deleteById($id);

    /**
     * Delete by client id
     *
     * @param string $clientId
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function deleteByClientId($clientId);
}
