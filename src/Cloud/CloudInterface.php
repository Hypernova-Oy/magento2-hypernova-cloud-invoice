<?php

namespace Hypernova\CloudInvoice\Cloud;

interface CloudInterface
{
    public function uploadInvoice($pdf);
}