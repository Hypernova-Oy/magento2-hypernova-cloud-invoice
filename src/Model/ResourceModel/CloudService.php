<?php
namespace Hypernova\CloudInvoice\Model\ResourceModel;


class CloudService extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('sales_invoice_cloudinvoice_cloud_service', 'entity_id');
    }

}