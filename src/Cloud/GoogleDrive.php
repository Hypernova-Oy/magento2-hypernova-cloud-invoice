<?php

namespace Hypernova\CloudInvoice\Cloud;

use Google_Client;
use Google_Service_Drive;

class GoogleDrive implements CloudInterface
{
    const CREDENTIALS_DIRECTORY = '/hypernova-cloudinvoice/etc/';
    private $scopeConfig;
    private $client;
    private $drive;
    private $api_credentials;
    private $accounts;

    private $_pdfInvoiceModel;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_loadSettings();

        $this->_pdfInvoiceModel = $pdfInvoiceModel;

        $this->client = new Google_Client();
        $this->client->setApplicationName('hypernova/magento2-cloud-invoice');
        $this->client->setAuthConfig($dir->getPath('var') . self::CREDENTIALS_DIRECTORY . $this->api_credentials);
        $this->client->setScopes(Google_Service_Drive::DRIVE);

        $this->drive = new Google_Service_Drive($this->client);
    }

    public function uploadInvoice($invoice)
    {
        try {
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();

            $google_accounts = explode(',', $this->accounts);

            // Check existence of CloudInvoices folder
            $folder = $this->drive->files->listFiles(array(
                'q' => 'name="CloudInvoices" and mimeType="application/vnd.google-apps.folder"',
                'fields' => 'nextPageToken, files(id, name, mimeType, parents)'
            ))->getFiles();

            if (count($folder) == 0) {
                // Create CloudInvoices folder
                $file = new \Google_Service_Drive_DriveFile();
                $file->setName('CloudInvoices');
                $file->setMimeType('application/vnd.google-apps.folder');
                $folder = $this->drive->files->create($file, array(
                    'fields' => 'id'
                ))->getId();
            } else {
                foreach ($folder as $f) {
                    $folder = $f->getId(); break;
                }
            }

            // Add administrator permissions to CloudInvoices folder
            foreach ($google_accounts as $account) {
                $permission = new \Google_Service_Drive_Permission();
                $permission->setRole('writer');
                $permission->setEmailAddress($account);
                $permission->setType('user');
                try {
                    $this->drive->permissions->create($folder, $permission);
                } catch (Exception $e) {
                    // todo
                }
            }

            $file = new \Google_Service_Drive_DriveFile();
            $file->setName('invoice-' . $invoice->getIncrementId() . '.pdf');
            $file->setParents(array($folder));
            $file->setMimeType('application/pdf');
            $this->drive->files->create(
                $file,
                array(
                    'data' => $pdfContent,
                    'mimeType' => 'application/pdf',
                    'uploadType' => 'media'
                )
            );
        } catch (Exception $e) {
            $invoice->addComment($e->getMessage(), false, true);
            $invoice->save();
        }
        return $this;
    }

    private function _loadSettings() {
        $this->api_credentials = $this->scopeConfig->getValue(
            'cloud_invoice/google_drive/google_service_credentials',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->accounts = $this->scopeConfig->getValue(
            'cloud_invoice/google_drive/google_share_with_accounts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (strlen($this->accounts) > 0) {
            $this->accounts = trim($this->accounts);
            $this->accounts = str_replace(' ', '', $this->accounts);
            $this->accounts = str_replace('|', ',', $this->accounts);
        }

    }
}