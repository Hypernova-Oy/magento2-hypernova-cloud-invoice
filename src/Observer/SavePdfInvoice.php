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

    private $uploader;

    private $googleDrive = 0;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\TranslateInterface $localeInterface,
        \Hypernova\CloudInvoice\Model\Uploader $uploader
    ) {
        $this->dir = $dir;
        $this->pdfInvoiceModel = $pdfInvoiceModel;
        $this->scopeConfig = $scopeConfig;
        $this->localeInterface = $localeInterface;
        $this->uploader = $uploader;

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
        $invoice = $observer->getEvent()->getInvoice();

        try {
            $this->uploader->upload($invoice);
        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, false);
            $invoice->save();
        }

        return $this;
    }
}
