<?php

namespace Hypernova\CloudInvoice\Cloud;

abstract class AbstractCloudService implements CloudInterface
{
    /**
     * @var \Hypernova\CloudInvoice\Model\CloudServiceFactory
     */
    protected $_cloudServiceFactory;

    /**
     * @var \Hypernova\CloudInvoice\Model\InvoicesUploadedFactory
     */
    protected $_invoicesUploadedFactory;

    protected $className;

    public function __construct(
        \Hypernova\CloudInvoice\Model\CloudServiceFactory $cloudServiceFactory,
        \Hypernova\CloudInvoice\Model\InvoicesUploadedFactory $invoicesUploadedFactory
    )
    {
        $this->_cloudServiceFactory = $cloudServiceFactory;
        $this->_invoicesUploadedFactory = $invoicesUploadedFactory;
        $this->className = (new \ReflectionClass($this))->getShortName();
    }

    public function getCloudServiceId() {
        $cloudservice_model = $this->_cloudServiceFactory->create();
        $cloudservice_collection = $cloudservice_model->getCollection()
            ->addFieldToFilter('cloud_service_name', $this->className);

        if ($cloudservice_collection->getSize()) {
            $data = $cloudservice_collection->getFirstItem();
            return $data->getData('entity_id');
        }

        $cloudservice_model->setData('cloud_service_name', $this->className);
        $cloudservice_model->save();

        return $cloudservice_model->getData('entity_id');
    }

    public function setUploadedStatus($invoice_id) {
        $cloud_service_id = $this->getCloudServiceId();
        $uploaded = $this->_invoicesUploadedFactory->create();
        $uploaded->setData('cloud_service_id', $cloud_service_id);
        $uploaded->setData('invoice_id', $invoice_id);
        $uploaded->save();

        return $uploaded;
    }

    public abstract function uploadInvoice(\Magento\Sales\Model\Order\Invoice $pdf);
}