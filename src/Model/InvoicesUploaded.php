<?php
namespace Hypernova\CloudInvoice\Model;

class InvoicesUploaded extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sales_invoice_cloudinvoice_invoices_uploaded';

    protected $_cacheTag = 'sales_invoice_cloudinvoice_invoices_uploaded';

    protected $_eventPrefix = 'sales_invoice_cloudinvoice_invoices_uploaded';

    protected function _construct()
    {
        $this->_init('Hypernova\CloudInvoice\Model\ResourceModel\InvoicesUploaded');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}