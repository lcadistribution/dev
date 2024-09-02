<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Catalog\Ui\Listing\DataProvider;

use Amasty\MWishlist\Model\ConfigProvider;
use Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider;

class AddMWishlistData
{
    public const IS_MWISHLIST_ENABLED = 'isMWishlistEnabled';
    public const RECENTLY_VIEWED_DATASOURCE = 'recently_viewed_datasource';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function afterGetData(DataProvider $subject, array $result): array
    {
        if ($subject->getName() === self::RECENTLY_VIEWED_DATASOURCE) {
            $result[self::IS_MWISHLIST_ENABLED] = $this->configProvider->isEnabled();
        }

        return $result;
    }
}
