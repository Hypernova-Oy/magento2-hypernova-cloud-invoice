<?php

namespace Hypernova\CloudInvoice\Model\Config\Backend;

use Magento\Framework\Filesystem\DirectoryList;

/**
 * System config file field backend model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @api
 * @since 100.0.2
 */
class CloudInvoiceConfigFile extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {

        $fieldConfig = $this->getFieldConfig();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList')->getPath('var');

        $uploadDir = "$directory/hypernova-cloudinvoice/etc/";
        if (array_key_exists('scope_info', $fieldConfig['upload_dir'])
            && $fieldConfig['upload_dir']['scope_info']
        ) {
            $uploadDir = $this->_appendScopeInfo($uploadDir);
        }

        return $uploadDir;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions()
    {
        return ['json'];
    }
}
