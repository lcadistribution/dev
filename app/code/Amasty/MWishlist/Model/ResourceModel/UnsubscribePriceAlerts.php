<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class UnsubscribePriceAlerts
{
    public const MAIN_TABLE = 'ammwishlist_unsubscribed_price_alerts';

    public const ID = 'id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function getUserIds(): array
    {
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName(self::MAIN_TABLE)
        );

        return $this->resource->getConnection()->fetchCol($select);
    }

    public function unsubscribeUser(int $userId): void
    {
        $this->resource->getConnection()->insert(
            $this->resource->getTableName(self::MAIN_TABLE),
            [self::ID => $userId]
        );
    }
}
