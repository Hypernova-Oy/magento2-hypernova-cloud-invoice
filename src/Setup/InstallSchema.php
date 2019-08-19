<?php

namespace Hypernova\CloudInvoice\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $cloudservice = 'sales_invoice_cloudinvoice_cloud_service';
        $invoices_uploaded = 'sales_invoice_cloudinvoice_invoices_uploaded';

        if (!$setup->tableExists($cloudservice)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable($cloudservice)
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'cloud_service_name',
                Table::TYPE_TEXT,
                255,
                [],
                'CloudService'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created date'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated date'
            )->addIndex(
                $setup->getIdxName($cloudservice,
                    ['cloud_service_name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['cloud_service_name'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );
            $setup->getConnection()->createTable($table);
        }

        if (!$setup->tableExists($invoices_uploaded)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable($invoices_uploaded)
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'InvoiceId'
            )->addColumn(
                'invoice_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'InvoiceId'
            )->addColumn(
                'cloud_service_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'CloudServiceId'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created date'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Updated date'
            )->addIndex(
                $setup->getIdxName($invoices_uploaded,
                    ['invoice_id', 'cloud_service_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['invoice_id', 'cloud_service_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );
            $setup->getConnection()->createTable($table);
        }

        $setup->getConnection()
        ->addForeignKey(
            $setup->getFkName($invoices_uploaded, 'invoice_id', 'sales_invoice', 'entity_id'),
            $invoices_uploaded,
            'invoice_id',
            $setup->getTable('sales_invoice'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName($invoices_uploaded, 'cloud_service_id', $cloudservice, 'entity_id'),
            $invoices_uploaded,
            'cloud_service_id',
            $setup->getTable($cloudservice),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->endSetup();
    }
}