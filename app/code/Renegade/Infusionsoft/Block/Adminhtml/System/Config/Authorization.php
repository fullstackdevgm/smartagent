<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Renegade\Infusionsoft\Service\InfusionsoftService;

/**
 * Class Authorization
 *
 * @package Renegade\Infusionsoft\Block\Adminhtml\System\Config
 */
class Authorization extends Field
{
    /**
     * InfusionsoftService object
     *
     * @var InfusionsoftService
     */
    private $infusionsoftService;

    /**
     * Authorization constructor.
     *
     * @param InfusionsoftService $infusionsoftService
     * @param Context             $context
     * @param array               $data
     */
    public function __construct(
        InfusionsoftService $infusionsoftService,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->infusionsoftService = $infusionsoftService;
    }

    /**
     * Render button
     *
     * @param  AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()
                ->unsCanUseWebsiteValue()
                ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->infusionsoftService->hasValidToken()) {
            return sprintf(
                '<b>%s</b> (<a href="%s">%s</a>)',
                __('Authorized'),
                $this->_urlBuilder->getUrl('renegade/infusionsoft/deauthorize'),
                __('De-authorize')
            );
        } else {
            return sprintf(
                '<a href="%s" target="_self">%s</a>',
                $this->infusionsoftService->getAuthorizationUrl(),
                __('Click here to authorize')
            );
        }
    }
}
