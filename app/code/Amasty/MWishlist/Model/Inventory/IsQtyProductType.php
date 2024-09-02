<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Inventory;

use Magento\CatalogInventory\Api\StockConfigurationInterface;

class IsQtyProductType
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param string $productType
     * @return bool
     */
    public function execute(string $productType): bool
    {
        return in_array($productType, array_keys(array_filter($this->stockConfiguration->getIsQtyTypeIds())), true);
    }
}
