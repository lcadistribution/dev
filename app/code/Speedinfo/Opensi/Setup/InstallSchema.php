<?php
/**
 * 2003-2017 OpenSi Connect
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Speedinfo
 * @package     Speedinfo_Opensi
 * @copyright   Copyright (c) 2017 Speedinfo SARL (http://www.speedinfo.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Speedinfo\Opensi\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Model\Order\Status;

class InstallSchema implements InstallSchemaInterface
{
  /**
   * @param SchemaSetupInterface $setup
   * @param ModuleContextInterface $context
   * @throws \Zend_Db_Exception
   */
  public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
  {
    $installer = $setup;
    $installer->startSetup();

    /**
     * CREATE OPENSI COMMENT TABLE
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('opensi_comments');

    // Check if the table already exists
    if ($installer->getConnection()->isTableExists($tableName) != true)
    {
      // Create table `opensi_comments`
      $table = $installer->getConnection()->newTable(
        $installer->getTable('opensi_comments')
      )->addColumn(
        'comment_id',
        Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Comment ID'
      )->addColumn(
        'opensi_comment_id',
        Table::TYPE_INTEGER,
        null,
        ['nullable' => false],
        'OpenSi comment ID'
      )->addColumn(
        'created_at',
        Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
        'Created at'
      )->setComment(
        'OpenSi Comments'
      );

      $installer->getConnection()->createTable($table);
    }


    /**
     * CREATE OPENSI DOCUMENTS TABLE (invoices, credit memos, deliverynotes)
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('opensi_documents');

    // Check if the table already exists
    if ($installer->getConnection()->isTableExists($tableName) != true)
    {
      // Create table `opensi_comments`
      $table = $installer->getConnection()->newTable(
        $installer->getTable('opensi_documents')
      )->addColumn(
        'document_id',
        Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Document ID'
      )->addColumn(
        'increment_id',
        Table::TYPE_TEXT,
        32,
        ['nullable' => false],
        'Increment ID'
      )->addColumn(
        'document_number',
        Table::TYPE_TEXT,
        20,
        ['nullable' => false],
        'Document number'
      )->addColumn(
        'document_type',
        Table::TYPE_TEXT,
        2,
        ['nullable' => false],
        'Document type'
      )->addColumn(
        'document_key',
        Table::TYPE_TEXT,
        null,
        ['nullable' => false],
        'Document key'
      )->addColumn(
        'created_at',
        Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => true, 'default' => null],
        'Created at'
      )->addIndex(
        $installer->getIdxName(
          $tableName,
          ['increment_id', 'document_number', 'document_type'],
          \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['increment_id', 'document_number', 'document_type'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
      )->setComment(
        'OpenSi Documents'
      );

      $installer->getConnection()->createTable($table);
    }


    /**
     * CREATE OPENSI SHIPPING METHODS TABLE
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('opensi_shipping_methods');

    // Check if the table already exists
    if ($installer->getConnection()->isTableExists($tableName) != true)
    {
      // Create table `opensi_comments`
      $table = $installer->getConnection()->newTable(
        $installer->getTable('opensi_shipping_methods')
      )->addColumn(
        'shipping_method_id',
        Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Shipping Method ID'
      )->addColumn(
        'name',
        Table::TYPE_TEXT,
        50,
        ['nullable' => false],
        'Name'
      )->addColumn(
        'created_at',
        Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
        'Created at'
      )->setComment(
        'OpenSi Shipping Methods'
      );

      $installer->getConnection()->createTable($table);
    }


    /**
     * ALTER TABLE SALES ORDER
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('sales_order');

    $columns = [
      'opensi_sync' => [
        'type' => Table::TYPE_INTEGER,
        'length' => '1',
        'nullable' => false,
        'default' => 0,
        'comment' => 'Is the order sync with OpenSi (0 => No, 1 => Yes)',
      ],
      'opensi_sync_at' => [
        'type' => Table::TYPE_TIMESTAMP,
        'nullable' => false,
        'default' => '0000-00-00 00:00:00',
        'comment' => 'Synchronization date',
      ],
      'opensi_after' => [
        'type' => Table::TYPE_INTEGER,
        'length' => '1',
        'nullable' => false,
        'default' => 1,
        'comment' => 'Is the order placed after the install of OpenSi',
      ],
      'opensi_date' => [
        'type' => Table::TYPE_TIMESTAMP,
        'nullable' => false,
        'default' => Table::TIMESTAMP_INIT,
        'comment' => 'OpenSi date',
      ],
    ];

    $connection = $installer->getConnection();

    foreach ($columns as $name => $definition)
    {
      $connection->addColumn($tableName, $name, $definition);
    }


    /**
     * ALTER TABLE SALES ORDER GRID
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('sales_order_grid');

    $columns = [
      'opensi_sync' => [
        'type' => Table::TYPE_INTEGER,
        'length' => '1',
        'nullable' => false,
        'default' => 0,
        'comment' => 'Is the order sync with OpenSi (0 => No, 1 => Yes)',
      ],
      'opensi_sync_at' => [
        'type' => Table::TYPE_TIMESTAMP,
        'nullable' => false,
        'default' => '0000-00-00 00:00:00',
        'comment' => 'Synchronization date',
      ],
      'opensi_after' => [
        'type' => Table::TYPE_INTEGER,
        'length' => '1',
        'nullable' => false,
        'default' => 1,
        'comment' => 'Is the order placed after the install of OpenSi',
      ],
      'opensi_date' => [
        'type' => Table::TYPE_TIMESTAMP,
        'nullable' => false,
        'default' => Table::TIMESTAMP_INIT,
        'comment' => 'OpenSi date',
      ],
    ];

    $connection = $installer->getConnection();

    foreach ($columns as $name => $definition)
    {
      $connection->addColumn($tableName, $name, $definition);
    }


    /**
     * ALTER TABLE SALES SHIPMENT
     * ---------------------------------------------------------------------------
     */
    $tableName = $installer->getTable('sales_shipment');

    $columns = [
      'opensi_delivery_note' => [
        'type' => Table::TYPE_TEXT,
        'length' => '15',
        'nullable' => true,
        'default' => null,
        'after' => 'increment_id',
        'comment' => 'OpenSi Delivery note',
      ],
    ];

    $connection = $installer->getConnection();

    foreach ($columns as $name => $definition)
    {
      $connection->addColumn($tableName, $name, $definition);
    }


    /**
     * END SETUP
     * ---------------------------------------------------------------------------
     */
    $installer->endSetup();
  }
}
