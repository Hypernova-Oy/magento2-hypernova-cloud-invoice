# magento2-hypernova-cloud-invoice
Store Magento2 invoices into your favorite cloud storage platform.

Supports
* Google Drive
* WebDAV (Nextcloud, ownCloud)

## Requirements

#### Google Drive
* Enable Google API for your project https://console.developers.google.com/apis/
* Create service account keys https://console.developers.google.com/apis/credentials

#### Nextcloud
* Create an app password

## Installation

```composer require hypernova/magento2-cloud-invoice```

## Configuration
1. Go to Stores -> Configuration -> Sales -> Cloud Invoice
2. Enable desired cloud platforms

* For Google Drive, upload your service account credentials that you are able
to download right after creating a service account in Google Developer Console.
Filename is typically something like My_Project-f324uf89f3r8yfwe.json.
Also, specify Google Accounts that you want to share the invoices with.

* For Nextcloud/Owncloud, enter WebDAV URL as follows

```https://NEXTCLOUD.INSTANCE.COM/remote.php/dav/files/YOUR_USERNAME```

Replace `NEXTCLOUD.INSTANCE.COM` with your Nextcloud/Owncloud hostname, and
`YOUR_USERNAME` with the account you are uploading to.

## Usage
Create an invoice normally via your order screen. As you create the invoice,
it will be automatically uploaded to the selected cloud storage.

## Crontab (recommended)
This module can run via crontab. Go to Stores -> Configuration -> Sales -> 
Cloud Invoice -> Cron settings to activate it.

Running this module via crontab prevents upload from blocking the normal
workflow of your order/invoice process.
