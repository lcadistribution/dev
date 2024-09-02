<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\View\Layout\Argument\UpdaterInterface;

class Updater implements UpdaterInterface
{
    /**
     * Add filtration by customer id
     *
     * @param AbstractDb $argument
     * @return AbstractDb
     */
    public function update($argument)
    {
        $connection = $argument->getConnection();

        $argument->getSelect()->columns(
            ['wishlist_name' => $connection->getIfNullSql('wishlist.name', $connection->quote(__('Wish List')))]
        );

        $argument->addFilterToMap(
            'wishlist_name',
            $connection->getIfNullSql('wishlist.name', $connection->quote(__('Wish List')))
        );

        $argument->getSelect()->columns(
            ['wishlist_type' => 'wishlist.type']
        );

        $argument->addFilterToMap('wishlist_type', 'wishlist.type');

        return $argument;
    }
}
