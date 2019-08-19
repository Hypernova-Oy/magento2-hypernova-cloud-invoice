<?php
namespace Hypernova\CloudInvoice\Model;

class CloudService extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sales_invoice_cloudinvoice_cloud_service';

    protected $_cacheTag = 'sales_invoice_cloudinvoice_cloud_service';

    protected $_eventPrefix = 'sales_invoice_cloudinvoice_cloud_service';

    protected function _construct()
    {
        $this->_init('Hypernova\CloudInvoice\Model\ResourceModel\CloudService');
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