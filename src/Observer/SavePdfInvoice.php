<?php

namespace Hypernova\CloudInvoice\Observer;

use Magento\Framework\Event\ObserverInterface;

class SavePdfInvoice implements ObserverInterface
{
    private $dir;
    private $pdfInvoiceModel;
    private $scopeConfig;

    private $googleDrive = 0;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dir = $dir;
        $this->pdfInvoiceModel = $pdfInvoiceModel;
        $this->scopeConfig = $scopeConfig;

        if ($this->scopeConfig->getValue(
            'cloud_invoice/google_drive/enable_google_drive',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1) {
            $this->googleDrive = 1;
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $invoice = $observer->getEvent()->getInvoice();

            if ($this->googleDrive) {
                $drive = new \Hypernova\CloudInvoice\Cloud\GoogleDrive(
                    $this->dir,
                    $this->pdfInvoiceModel,
                    $this->scopeConfig
                );
                $drive->uploadInvoice($invoice);
                $invoice->addComment('Added to Google Drive', false, true);
            }

        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, true);
            $invoice->save();
        }
        return $this;
    }
}