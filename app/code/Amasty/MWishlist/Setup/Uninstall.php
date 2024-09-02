<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Setup;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Module\Manager;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public const AMASTY_COLUMNS_TO_DROP = [
        WishlistInterface::TYPE
    ];

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;

    public function __construct(
        Manager $moduleManager,
        CollectionFactory $wishlistCollectionFactory
    ) {
        $this->moduleManager = $moduleManager;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $wishlistTable = $setup->getTable('wishlist');
        $isMagentoMultipleEnabled = $this->moduleManager->isEnabled('Magento_MultipleWishlist');
        $connection = $setup->getConnection();
        if (!$isMagentoMultipleEnabled) {
            $this->saveWishlistData($connection, $setup, $wishlistTable);
            $connection->dropColumn($wishlistTable, WishlistInterface::NAME);
            $this->revertIndex($setup, $wishlistTable);
        }
        $this->deleteColumns($connection, $wishlistTable);
    }

    private function saveWishlistData(
        AdapterInterface $connection,
        SchemaSetupInterface $setup,
        string $wishlistTable
    ): void {
        $wishlistItemsTable = $setup->getTable('wishlist_item');

        $defaultWishlists = $this->getDefaultWishlists($connection, $wishlistTable);
        $this->updateWishlistItems($defaultWishlists, $connection, $wishlistTable, $wishlistItemsTable);

        $connection->delete($wishlistTable, ['wishlist_id NOT IN (?)' => array_keys($defaultWishlists)]);
    }

    private function getDefaultWishlists(AdapterInterface $connection, string $wishlistTable): array
    {
        $select = $connection->select()
            ->from($wishlistTable, ['default_wishlist_id' => 'MIN(wishlist_id)', 'customer_id'])
            ->group('customer_id');

        return $connection->fetchPairs($select);
    }

    private function updateWishlistItems(
        array $defaultWishlists,
        AdapterInterface $connection,
        string $wishlistTable,
        string $wishlistItemsTable
    ): void {
        foreach ($defaultWishlists as $defaultWishlist => $customerId) {
            $select = $connection->select()
                ->joinLeft(
                    ['wishlist' => $wishlistTable],
                    'items.wishlist_id = wishlist.wishlist_id',
                    []
                )
                ->reset(Select::COLUMNS)
                ->columns(['wishlist_id' => new \Zend_Db_Expr($defaultWishlist)])
                ->where('customer_id = ?', $customerId);
            $connection->query($connection->updateFromSelect($select, ['items' => $wishlistItemsTable]));
        }
    }

    private function deleteColumns(
        AdapterInterface $connection,
        string $wishlistTable
    ): void {
        foreach (self::AMASTY_COLUMNS_TO_DROP as $column) {
            $connection->dropColumn($wishlistTable, $column);
        }
    }

    private function revertIndex(
        SchemaSetupInterface $setup,
        string $wishlistTable
    ): void {
        $connection = $setup->getConnection();
        foreach ($setup->getConnection()->getForeignKeys($wishlistTable) as $foreignKeyName => $foreignKey) {
            if ($foreignKey['COLUMN_NAME'] === 'customer_id') {
                $setup->getConnection()->dropForeignKey($wishlistTable, $foreignKeyName);
            }
        }

        $connection->dropIndex($wishlistTable, $setup->getIdxName($wishlistTable, ['customer_id']));
        $connection->addIndex(
            $wishlistTable,
            $setup->getIdxName($wishlistTable, ['customer_id']),
            ['customer_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $customerTable = $setup->getTable('customer_entity');
        $setup->getConnection()->addForeignKey(
            $setup->getFkName($wishlistTable, 'customer_id', $customerTable, 'entity_id'),
            $wishlistTable,
            'customer_id',
            $customerTable,
            'entity_id'
        );
    }
}
