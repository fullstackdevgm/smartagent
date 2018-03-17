<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Api\Data;

/**
 * Interface TokenInterface
 *
 * @package Renegade\Infusionsoft\Api\Data
 */
interface TokenInterface
{
    /**
     * Get token id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set token id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId();

    /**
     * Set client id
     *
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId($clientId);

    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set token
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token);

    /**
     * Get expires at
     *
     * @return string
     */
    public function getExpiresAt();

    /**
     * Set expires at
     *
     * @param \DateTime $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(\DateTime $expiresAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt);
}
