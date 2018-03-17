<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Token
 *
 * @package Renegade\Infusionsoft\Model\ResourceModel
 */
class Token extends AbstractDb
{
    /**
     * Initialize the resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('infusionsoft_token', 'id');
    }
}
