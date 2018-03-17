<?php
/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Controller\Adminhtml\Infusionsoft;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Renegade\Infusionsoft\Service\InfusionsoftService;

class AuthorizationCallback extends Action
{
    /**
     * Context object
     *
     * @var Context
     */
    private $context;

    /**
     * Infusionsoft service
     *
     * @var InfusionsoftService
     */
    private $infusionsoftService;

    /**
     * AuthorizationCallback constructor.
     *
     * @param Context             $context
     * @param InfusionsoftService $infusionsoftService
     */
    public function __construct(
        Context $context,
        InfusionsoftService $infusionsoftService
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->infusionsoftService = $infusionsoftService;
    }

    /**
     * Execute controller action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->infusionsoftService->processAuthorizationResponse();

            $this->messageManager
                ->addSuccessMessage(__('Successfully authenticated with the Infusionsoft API'));
        } catch (\Exception $e) {
            $this->messageManager
                ->addExceptionMessage($e, __('An error occurred while attempting to authorize the Infusionsoft API'));
        }

        $redirectUrl = $this->_backendUrl->getUrl(
            'adminhtml/system_config/edit',
            ['section' => 'renegade_infusionsoft']
        );

        $this->_redirect($redirectUrl);
    }
}
