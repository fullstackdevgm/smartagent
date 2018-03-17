<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Renegade\Infusionsoft\Api\TokenRepositoryInterface;
use Renegade\Infusionsoft\Service\InfusionsoftService;

/**
 * Class TokenExpiration
 *
 * @package Renegade\Infusionsoft\Block\Adminhtml\System\Config
 */
class TokenExpiration extends Field
{
    /**
     * InfusionsoftService object
     *
     * @var InfusionsoftService
     */
    private $infusionsoftService;

    /**
     * Token repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * TokenExpiration constructor.
     *
     * @param InfusionsoftService      $infusionsoftService
     * @param TokenRepositoryInterface $tokenRepository
     * @param Context                  $context
     * @param array                    $data
     */
    public function __construct(
        InfusionsoftService $infusionsoftService,
        TokenRepositoryInterface $tokenRepository,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->infusionsoftService = $infusionsoftService;
        $this->tokenRepository = $tokenRepository;
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
            $expirationDateText = '';

            if ($clientId = $this->infusionsoftService->getInfusionsoft()->getClientId()) {
                try {
                    $storedToken = $this->tokenRepository->getByClientId($clientId);

                    $expirationDate = $this->_localeDate->date($storedToken->getExpiresAt());

                    $expirationDateText = sprintf(
                        '%s %s %s',
                        $expirationDate->format('D, M jS, Y'),
                        __(' at '),
                        $expirationDate->format('g:i A')
                    );
                } catch (NoSuchEntityException $e) {
                }
            }

            return $expirationDateText;
        } else {
            return __('N/A');
        }
    }
}
