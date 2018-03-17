<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Renegade\Infusionsoft\Model\ResourceModel\Token
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize the collection resource
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(
            'Renegade\Infusionsoft\Model\Token',
            'Renegade\Infusionsoft\Model\ResourceModel\Token'
        );
    }
}
