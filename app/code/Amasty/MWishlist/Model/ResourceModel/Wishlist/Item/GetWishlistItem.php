<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Wishlist\Item;

use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection\Grid;
use Magento\Framework\App\ResourceConnection;

class GetWishlistItem
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function execute()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName(Grid::WISHLIST_ITEM_TABLE))
            ->limit(1);

        return $connection->fetchRow($select);
    }
}
