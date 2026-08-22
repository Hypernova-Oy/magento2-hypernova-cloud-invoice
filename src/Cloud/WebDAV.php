<?php

namespace Hypernova\CloudInvoice\Cloud;

class WebDAV extends AbstractCloudService
{
    protected $logger;
    private $scopeConfig;
    private $client;
    private $base_url;
    private $username;
    private $password;
    protected $_cloudServiceFactory;

    private $_pdfInvoiceModel;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Hypernova\CloudInvoice\Model\CloudServiceFactory $cloudServiceFactory,
        \Hypernova\CloudInvoice\Model\InvoicesUploadedFactory $invoicesUploaded
    ) {
        parent::__construct($cloudServiceFactory, $invoicesUploaded);
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        if ($this->scopeConfig->getValue(
                    'cloud_invoice/webdav/enable_webdav',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
        ) {
            $this->_loadSettings();
            try {
                $this->client = new \Sabre\DAV\Client(array(
                    'baseUri'  => $this->base_url,
                    'userName' => $this->username,
                    'password' => $this->password
                ));
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }

        $this->_cloudServiceFactory = $cloudServiceFactory;
        $this->_pdfInvoiceModel = $pdfInvoiceModel;
    }

    /**
     * Return true if directory structure is created or already exists
     *
     * @return bool
     */
    private function _createDirectoryStructure($invoice) {
        // Check existence of CloudInvoices folder
        try {
            $this->client->propfind($this->base_url . 'CloudInvoices/', array (
                '{DAV:}resourcetype'
            ));
        } catch (\Throwable $e) {
            if ($e->getMessage() == 'Not Found') {
                $res = $this->client->request('MKCOL', $this->base_url . 'CloudInvoices');
                if ($res["statusCode"] != 201) {
                    $this->logger->critical($res["body"]);
                    throw new \Exception($res["body"]);
                }
            } else {
                throw $e;
            }
        }

        // Check existence of annual directory
        $year = date('Y', strtotime($invoice->getCreatedAt()));
        try {
            $this->client->propfind($this->base_url . "CloudInvoices/$year/", array (
                '{DAV:}resourcetype'
            ));
        } catch (\Throwable $e) {
            if ($e->getMessage() == 'Not Found') {
                $res = $this->client->request('MKCOL', $this->base_url . "CloudInvoices/$year");
                if ($res["statusCode"] != 201) {
                    $this->logger->critical($res["body"]);
                    throw new \Exception($res["body"]);
                }
            } else {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Uploads invoice into WebDAV
     *
     * @return WebDAV object
     */
    public function uploadInvoice(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        try {
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();


            $year = date('Y', strtotime($invoice->getCreatedAt()));
            $file_name = 'invoice-' . $invoice->getIncrementId() . '.pdf';

            $found = false;
            try {
                $this->client->propfind($this->base_url . "CloudInvoices/$year/$file_name", array (
                    '{DAV:}resourcetype'
                ));
                $found = true;
            } catch (\Throwable $e) {}

            if ($found) {
                return $this;
            }

            $this->_createDirectoryStructure($invoice);
            $res = $this->client->request('PUT', $this->base_url . "CloudInvoices/$year/$file_name", $pdfContent);
            if ($res["statusCode"] != 201) {
                $this->logger->critical($res["body"]);
                throw new \Exception($res["body"]);
            }
            $invoice->addComment('Added to ' . $this->base_url . "CloudInvoices/$year/$file_name", false, false);
            $this->setUploadedStatus($invoice->getEntityId());
        } catch (\Throwable $e) {
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
        }
        return $this;
    }

    private function _loadSettings() {
        $this->base_url = $this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($this->base_url) {
            $this->base_url = trim($this->base_url);
            // make sure path ends with /
            $this->base_url = rtrim($this->base_url, '/') . '/';
        }

        $this->username = $this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_username',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($this->username) {
            $this->username = trim($this->username);
        }

        $this->password = $this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
