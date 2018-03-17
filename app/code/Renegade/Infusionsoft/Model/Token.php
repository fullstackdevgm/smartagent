<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Renegade\Infusionsoft\Api\Data\TokenInterface;

/**
 * Class Token
 *
 * @package Renegade\Infusionsoft\Model
 */
class Token extends AbstractModel implements IdentityInterface, TokenInterface
{
    const CACHE_TAG = 'infusionsoft_token';

    /**
     * Initialize the resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Renegade\Infusionsoft\Model\ResourceModel\Token');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getData('client_id');
    }

    /**
     * Set client id
     *
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->setData('client_id', $clientId);

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getData('token');
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->setData('token', $token);

        return $this;
    }

    /**
     * Set expires at
     *
     * @param \DateTime $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->setData('expires_at', $expiresAt->format('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * Get expires at
     *
     * @return string
     */
    public function getExpiresAt()
    {
        return $this->getData('expires_at');
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * Set updated at
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->setData('updated_at', $updatedAt->format('Y-m-d H:i:s'));

        return $this;
    }
}
