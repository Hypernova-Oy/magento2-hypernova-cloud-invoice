<?php

namespace Hypernova\CloudInvoice\Observer;

use Magento\Framework\Event\ObserverInterface;

class SavePdfInvoice implements ObserverInterface
{
    private $dir;
    private $pdfInvoiceModel;
    private $scopeConfig;
    private $localeInterface;

    private $invoiceLocale;

    private $googleDrive = 0;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\TranslateInterface $localeInterface
    ) {
        $this->dir = $dir;
        $this->pdfInvoiceModel = $pdfInvoiceModel;
        $this->scopeConfig = $scopeConfig;
        $this->localeInterface = $localeInterface;

        if ($this->scopeConfig->getValue(
            'cloud_invoice/google_drive/enable_google_drive',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1) {
            $this->googleDrive = 1;
        }

        $this->currentLocale = $this->localeInterface->getLocale();
        $this->invoiceLocale = $this->currentLocale;
        if ($locale = $this->scopeConfig->getValue(
            'cloud_invoice/general/invoice_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->invoiceLocale = $locale;
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->localeInterface->setLocale($this->invoiceLocale);
            $this->localeInterface->loadData();

            $invoice = $observer->getEvent()->getInvoice();

            if ($this->googleDrive) {
                $drive = new \Hypernova\CloudInvoice\Cloud\GoogleDrive(
                    $this->dir,
                    $this->pdfInvoiceModel,
                    $this->scopeConfig
                );
                $drive->uploadInvoice($invoice);
                $invoice->addComment('Added to Google Drive', false, false);
            }

            $this->localeInterface->setLocale($this->currentLocale);
            $this->localeInterface->loadData();
        } catch (Exception $e) {
            $this->localeInterface->setLocale($this->currentLocale);
            $this->localeInterface->loadData();
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
        }

        return $this;
    }
}
