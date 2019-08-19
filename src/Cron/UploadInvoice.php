<?php

namespace Hypernova\CloudInvoice\Cron;

class UploadInvoice
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Uploader
     */
    private $_uploader;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Hypernova\CloudInvoice\Model\Uploader $uploader
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->_uploader = $uploader;
    }

    /**
     * Process completed orders with no invoice, if cron is enabled
     */
    public function execute()
    {
        if ($this->scopeConfig->getValue(
                'cloud_invoice/cron/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) != 1) {
            return;
        }

        $this->logger->info('Starting uploading invoices.');
        $this->_uploader->uploadAllPending();
        $this->logger->info('Auto invoice procedure completed.');
    }
}