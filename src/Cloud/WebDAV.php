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
        $this->_loadSettings();

        $this->_cloudServiceFactory = $cloudServiceFactory;
        $this->_pdfInvoiceModel = $pdfInvoiceModel;

        $this->client = new \Sabre\DAV\Client(array(
            'baseUri'  => $this->base_url,
            'userName' => $this->username,
            'password' => $this->password
        ));
    }

    /**
     * Uploads invoice into Google Drive
     *
     * @return GoogleDrive object
     */
    public function uploadInvoice(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        try {
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();
            $file_name = 'invoice-' . $invoice->getIncrementId() . '.pdf';

            $res = $this->client->request('PUT', $this->base_url . $file_name, $pdfContent);
            if ($res["statusCode"] != 201) {
                $this->logger->critical($res["body"]);
                throw \Exception($res["body"]);
            }
            $invoice->addComment('Added to ' . $this->base_url, false, false);
            $this->setUploadedStatus($invoice->getEntityId());
        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
        }
        return $this;
    }

    private function _loadSettings() {
        $this->base_url = trim($this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        // make sure path ends with /
        $this->base_url = rtrim($this->base_url, '/') . '/';

        $this->username = trim($this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_username',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        $this->password = $this->scopeConfig->getValue(
            'cloud_invoice/webdav/webdav_password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
