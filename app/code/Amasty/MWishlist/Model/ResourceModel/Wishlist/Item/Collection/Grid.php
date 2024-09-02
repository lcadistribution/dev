<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Model\Source\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class Grid extends Collection
{
    public const WISHLIST_ITEM_TABLE = 'wishlist_item';
    private const WISHLIST_TABLE = 'wishlist';

    private const TOTAL_LIST_ALIAS = 'total_list';

    private const COUNTER_COLUMNS = [
        self::TOTAL_LIST_ALIAS => null,
        'total_wish' => Type::WISH,
        'total_requisition' => Type::REQUISITION
    ];

    private const ADDED_COLUMN = 'added_at';

    private const ATTRIBUTES = [
        'entity_id',
        'thumbnail',
        'name'
    ];

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->joinAttributes();
        $this->joinCounters();
        $this->joinAddedAt();

        $this->limitExtraProducts();
    }

    private function joinAttributes()
    {
        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('thumbnail');
    }

    private function joinAddedAt()
    {
        $select = $this->getConnection()->select()->from(
            ['item' => $this->getTable(self::WISHLIST_ITEM_TABLE)],
            ['product_id', 'added_at' => new Zend_Db_Expr('MAX(added_at)')]
        )->group('product_id');

        $this->getSelect()->join(
            [self::ADDED_COLUMN => $select],
            sprintf('%s.product_id = entity_id', self::ADDED_COLUMN),
            self::ADDED_COLUMN
        );
    }

    private function joinCounters()
    {
        foreach (self::COUNTER_COLUMNS as $columnName => $type) {
            $this->getSelect()->joinLeft(
                [$columnName => $this->getWishlistCount($columnName, $type)],
                sprintf('entity_id = %s.product_id', $columnName),
                [
                    $columnName => $this->getConnection()->getIfNullSql(
                        sprintf('%1$s.%1$s', $columnName),
                        0
                    )
                ]
            );
        }
    }

    /**
     * @param string $alias
     * @param int|null $type
     * @return Select
     */
    private function getWishlistCount(string $alias, ?int $type = null): Select
    {
        $select = $this->getConnection()->select()->from(
            ['item' => $this->getTable(self::WISHLIST_ITEM_TABLE)],
            ['product_id', $alias => new Zend_Db_Expr('COUNT(*)')]
        )->join(
            ['list' => $this->getTable(self::WISHLIST_TABLE)],
            sprintf('item.%1$s = list.%1$s', WishlistInterface::WISHLIST_ID),
            []
        )->group('product_id');

        if ($type !== null) {
            $select->where(WishlistInterface::TYPE . ' = ?', $type)
                ->group(WishlistInterface::TYPE);
        }

        return $select;
    }

    /**
     *  Remove from collection products, which not added for one wishlist at least.
     */
    private function limitExtraProducts()
    {
        $this->getSelect()->where(
            sprintf('%1$s.%1$s', self::TOTAL_LIST_ALIAS) . ' > 0'
        );
    }

    /**
     * Rewrite for order by custom columns not as attribute.
     *
     * @return  $this
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $field => $direction) {
                if (in_array($field, self::ATTRIBUTES)) {
                    $this->addAttributeToSort($field, $direction);
                } else {
                    $this->_select->order(new \Zend_Db_Expr($field . ' ' . $direction));
                }
            }
            $this->addAttributeToSort('entity_id', Select::SQL_ASC);
            $this->_isOrdersRendered = true;
        }

        return $this;
    }

    /**
     * Rewrite for filter by custom columns not as attribute.
     *
     * @param string $field
     * @param null|string $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, self::ATTRIBUTES)) {
            $this->addAttributeToFilter($field, $condition, 'left');
        } else {
            $resultCondition = $this->_translateCondition($field, $condition);
            $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);
        }

        return $this;
    }

    /**
     * Dont remove left joins!
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        return $this->_getSelectCountSql(null, false);
    }
}
