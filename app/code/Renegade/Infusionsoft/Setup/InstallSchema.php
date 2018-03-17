<?php

namespace Renegade\Infusionsoft\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $table = $installer
            ->getConnection()
            ->newTable(
                $installer->getTable('infusionsoft_token')
            )
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Id'
            )
            ->addColumn(
                'client_id',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Client Id'
            )
            ->addColumn(
                'token',
                Table::TYPE_TEXT,
                512,
                ['nullable' => false],
                'Token'
            )
            ->addColumn(
                'expires_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Expires at'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => true,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE,
                ],
                'Updated at'
            )
            ->setComment(
                'Infusionsoft token storage'
            );

        $installer
            ->getConnection()
            ->createTable($table);

        $installer->endSetup();
    }
}
