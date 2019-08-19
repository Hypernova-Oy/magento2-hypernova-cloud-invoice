<?php

namespace Hypernova\CloudInvoice\Cloud;

interface CloudInterface
{
    public function uploadInvoice(\Magento\Sales\Model\Order\Invoice $pdf);
}