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

    /**
     * Uploads invoice into Google Drive
     *
     * @return GoogleDrive object
     */
    public function uploadInvoice($invoice)
    {
        try {
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();

            $directory_id = $this->_createDirectoryStructure($invoice);

            $file = new \Google_Service_Drive_DriveFile();
            $file->setName('invoice-' . $invoice->getIncrementId() . '.pdf');
            $file->setParents(array($directory_id));
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

    /**
     * Return directory id into which invoice shall be uploaded
     *
     * @return integer
     */
    private function _createDirectoryStructure($invoice) {
        // Check existence of CloudInvoices folder
        $cloudinvoice_folder = $this->drive->files->listFiles(array(
            'q' => 'name="CloudInvoices" and mimeType="application/vnd.google-apps.folder"',
            'fields' => 'nextPageToken, files(id, name, mimeType, parents)'
        ))->getFiles();

        if (count($cloudinvoice_folder) == 0) {
            // Create CloudInvoices folder
            $file = new \Google_Service_Drive_DriveFile();
            $file->setName('CloudInvoices');
            $file->setMimeType('application/vnd.google-apps.folder');
            $cloudinvoice_folder = $this->drive->files->create($file, array(
                'fields' => 'id'
            ))->getId();
        } else {
            foreach ($cloudinvoice_folder as $f) {
                $cloudinvoice_folder = $f->getId(); break;
            }
        }

        // Check existence of annual directory
        $year = date('Y', strtotime($invoice->getCreatedAt()));
        $folder = $this->drive->files->listFiles(array(
            'q' => 'name="'.$year.'" and "'.$cloudinvoice_folder.'" in parents and mimeType="application/vnd.google-apps.folder"',
            'fields' => 'nextPageToken, files(id, name, mimeType, parents)'
        ))->getFiles();

        if (count($folder) == 0) {
            // Create a directory for this year
            $file = new \Google_Service_Drive_DriveFile();
            $file->setName($year);
            $file->setParents(array($cloudinvoice_folder));
            $file->setMimeType('application/vnd.google-apps.folder');
            $folder = $this->drive->files->create($file, array(
                'fields' => 'id'
            ))->getId();
        } else {
            foreach ($folder as $f) {
                $folder = $f->getId();
            }
        }

        // Add administrator permissions to CloudInvoices folder
        $google_accounts = explode(',', $this->accounts);
        foreach ($google_accounts as $account) {
            $permission = new \Google_Service_Drive_Permission();
            $permission->setRole('writer');
            $permission->setEmailAddress($account);
            $permission->setType('user');
            try {
                $this->drive->permissions->create($cloudinvoice_folder, $permission);
                $this->drive->permissions->create($folder, $permission);
            } catch (Exception $e) {
                $invoice->addComment($e->getMessage(), false, true);
                $invoice->save();
            }
        }

        return $folder;
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