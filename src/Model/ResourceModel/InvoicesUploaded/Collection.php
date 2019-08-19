<?php
namespace Hypernova\CloudInvoice\Model\ResourceModel\InvoicesUploaded;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'sales_invoice_cloudinvoice_invoices_uploaded';
    protected $_eventObject = 'invoices_uploaded_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hypernova\CloudInvoice\Model\InvoicesUploaded', 'Hypernova\CloudInvoice\Model\ResourceModel\InvoicesUploaded');
    }

}