<?php

namespace Hypernova\CloudInvoice\Model;


class Uploader
{

    private $scopeConfig;
    private $localeInterface;
    private $currentLocale;
    private $invoiceLocale;

    private $_cloudServiceFactory;

    private $googleDrive;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\TranslateInterface $localeInterface,
        \Hypernova\CloudInvoice\Model\CloudServiceFactory $cloudServiceFactory,
        \Hypernova\CloudInvoice\Cloud\GoogleDrive $googleDrive
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeInterface = $localeInterface;
        $this->_cloudServiceFactory = $cloudServiceFactory;
        $this->googleDrive = $googleDrive;

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
                ) == 1) {
                $this->googleDrive->uploadInvoice($invoice);
            }

        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
        }

        $this->localeInterface->setLocale($this->currentLocale);
        $this->localeInterface->loadData();

        return $this;
    }
}
