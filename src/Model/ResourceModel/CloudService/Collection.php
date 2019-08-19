<?php
namespace Hypernova\CloudInvoice\Model\ResourceModel\CloudService;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'sales_invoice_cloudinvoice_cloud_service_collection';
    protected $_eventObject = 'cloud_service_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hypernova\CloudInvoice\Model\CloudService', 'Hypernova\CloudInvoice\Model\ResourceModel\CloudService');
    }

}