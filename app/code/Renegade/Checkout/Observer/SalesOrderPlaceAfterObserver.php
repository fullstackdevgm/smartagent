<?php
/**
 * @copyright   Copyright (c) 2016 SmartAgents
 * @author      Andrew Kolstad <andrew@smartagents.com>
 */

namespace Renegade\Checkout\Observer;

use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    const XML_PATH_RENEGADE_CHECKOUT_ORDER_WEBHOOK_URL = 'renegade_checkout/order_webhook/url';

    /**
     * HTTP Client
     *
     * @var Guzzle6HttpAdapter
     */
    private $httpClient;

    /**
     * Store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Logger object
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SalesOrderPlaceAfterObserver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Guzzle6HttpAdapter   $httpClient
     * @param LoggerInterface      $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Guzzle6HttpAdapter $httpClient,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Execute the observer
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        $webhookUrl = $this->scopeConfig->getValue(self::XML_PATH_RENEGADE_CHECKOUT_ORDER_WEBHOOK_URL);

        try {
            $this->httpClient->post($webhookUrl, [], $order->toArray());
            $this->logger->debug('Order data posted to webhook (%s).', $webhookUrl);
        } catch (\Exception $e) {
            $this->logger->critical(
                __(
                    'Could not post order data to "%s". Exception message: %s',
                    $webhookUrl,
                    $e->getMessage()
                )
            );
        }
    }
}
