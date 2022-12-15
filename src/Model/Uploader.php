<?php

namespace Hypernova\CloudInvoice\Model;


use Magento\Framework\Exception\LocalizedException;

class Uploader
{

    private $scopeConfig;
    private $localeInterface;
    private $currentLocale;
    private $invoiceLocale;

    protected $_invoiceFactory;

    protected $_cloudServiceFactory;
    protected $_invoicesUploadedFactory;

    private $googleDrive;
    private $webDav;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\TranslateInterface $localeInterface,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Hypernova\CloudInvoice\Model\CloudServiceFactory $cloudServiceFactory,
        \Hypernova\CloudInvoice\Model\InvoicesUploadedFactory $invoicesUploadedFactory,
        \Hypernova\CloudInvoice\Cloud\GoogleDrive $googleDrive,
        \Hypernova\CloudInvoice\Cloud\WebDAV $webDav
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeInterface = $localeInterface;
        $this->_invoiceFactory = $invoiceFactory;
        $this->_cloudServiceFactory = $cloudServiceFactory;
        $this->_invoicesUploadedFactory = $invoicesUploadedFactory;

        $this->googleDrive = $googleDrive;
        $this->webDav = $webDav;

        $this->currentLocale = $this->localeInterface->getLocale();
        $this->invoiceLocale = $this->currentLocale;
        if ($locale = $this->scopeConfig->getValue(
            'cloud_invoice/general/invoice_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->invoiceLocale = $locale;
        }
    }

    public function upload(\Magento\Sales\Model\Order\Invoice $invoice)
    {

        $this->localeInterface->setLocale($this->invoiceLocale);
        $this->localeInterface->loadData();

        try {

            // Google Drive
            if ($this->scopeConfig->getValue(
                    'cloud_invoice/google_drive/enable_google_drive',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) == 1 && !$this->isUploaded($invoice->getEntityId(), $this->googleDrive->getCloudServiceId())) {
                $this->googleDrive->uploadInvoice($invoice);
            }

            // WebDAV
            if ($this->scopeConfig->getValue(
                    'cloud_invoice/webdav/enable_webdav',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) == 1 && !$this->isUploaded($invoice->getEntityId(), $this->webDav->getCloudServiceId())) {
                $this->webDav->uploadInvoice($invoice);
            }

        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
            throw new LocalizedException($e->getMessage());
        }

        $this->localeInterface->setLocale($this->currentLocale);
        $this->localeInterface->loadData();

        return $this;
    }

    public function uploadAllPending() {
        $uploaded_invoices = $this->_invoicesUploadedFactory->create()->getCollection();
        $invoices = $this->_invoiceFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', array('nin' => $uploaded_invoices->getColumnValues('invoice_id')));

        foreach ($invoices as $invoice) {
            $this->upload($invoice);
        }

        return $this;
    }

    public function isUploaded($invoice_id, $cloud_service_id) {
        $collection = $this->_invoicesUploadedFactory->create()->getCollection()
            ->addFieldToFilter('invoice_id', $invoice_id)
            ->addFieldToFilter('cloud_service_id', $cloud_service_id);

        return $collection->getSize()>0;
    }
}
