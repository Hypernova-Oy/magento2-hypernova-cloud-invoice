# magento2-hypernova-cloud-invoice
Store Magento2 invoices into your favorite cloud storage platform.

## Requirements

#### Google Drive
* Enable Google API for your project https://console.developers.google.com/apis/
* Create service account keys https://console.developers.google.com/apis/credentials

## Installation
* Via composer

Add this Git repository to composer.json
```
    "repositories": {
        "magento2-hypernova-cloud-invoice": {
            "type": "vcs",
            "url": "https://github.com/Hypernova-Oy/magento2-hypernova-cloud-invoice"
        }
    }
```

Execute 

```composer require hypernova/magento2-cloud-invoice:dev-master```

## Configuration
1. Go to Stores -> Configuration -> Sales -> Cloud Invoice
2. Enable desired cloud platforms

* For Google Drive, upload your service account credentials that you are able
to download right after creating a service account in Google Developer Console.
Filename is typically something like My_Project-f324uf89f3r8yfwe.json.
Also, specify Google Accounts that you want to share the invoices with.

## Usage
Create an invoice normally via your order screen. As you create the invoice,
it will be automatically uploaded to Google Drive and shared with the
Google Accounts you specified in the configuration step.

## Crontab
This module can run via crontab. Go to Stores -> Configuration -> Sales -> 
Cloud Invoice -> Cron settings to activate it.