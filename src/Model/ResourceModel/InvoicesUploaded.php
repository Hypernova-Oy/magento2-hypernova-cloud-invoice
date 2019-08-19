<?php
namespace Hypernova\CloudInvoice\Model\ResourceModel;


class InvoicesUploaded extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('sales_invoice_cloudinvoice_invoices_uploaded', 'entity_id');
    }

}