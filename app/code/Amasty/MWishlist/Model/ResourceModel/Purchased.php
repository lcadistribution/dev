<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel;

class Purchased extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const QUOTE_ITEM_ID = 'quote_item_id';

    public const TABLE_NAME = 'amasty_wishlist_most_purchased';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'product_id');
    }

    public function saveItems(array $items): void
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $items);
    }
}
