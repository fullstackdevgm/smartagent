<?php

/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Infusionsoft\Cron;

use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;
use Renegade\Infusionsoft\Service\InfusionsoftService;

/**
 * Class InfusionsoftTokenRefresh
 *
 * @package Renegade\Infusionsoft\Cron
 */
class InfusionsoftTokenRefresh
{
    /**
     * InfusionsoftService object
     *
     * @var InfusionsoftService
     */
    private $infusionsoftService;

    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InfusionsoftTokenRefresh constructor.
     *
     * @param InfusionsoftService $infusionsoftService
     * @param LoggerInterface     $logger
     */
    public function __construct(
        InfusionsoftService $infusionsoftService,
        LoggerInterface $logger
    ) {
        $this->infusionsoftService = $infusionsoftService;
        $this->logger = $logger;
    }

    /**
     * Execute Cron Job
     *
     * @throws AlreadyExistsException
     *
     * @return void
     */
    public function execute()
    {
        if ($this->infusionsoftService->isEnabled()) {
            $this->logger->info(__('Refreshing Infusionsoft stored token'));

            try {
                $this->infusionsoftService->refreshToken();

                $this->logger->info('Refreshed Infusionsoft stored token successfully');
            } catch (\Exception $e) {
                $this->logger->critical('An error occurred while refreshing the Infusionsoft token');
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
