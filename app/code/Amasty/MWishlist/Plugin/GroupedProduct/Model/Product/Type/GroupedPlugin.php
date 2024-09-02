<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\GroupedProduct\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class GroupedPlugin
{
    /**
     * @var Product
     */
    private $product;

    public function beforeGetAssociatedProducts(Grouped $subject, Product $product): array
    {
        $this->product = $product;

        return [$product];
    }

    public function afterGetAssociatedProducts(Grouped $subject, array $products): array
    {
        if (!$this->product->getIsFromWishlist()) {
            return $products;
        }

        foreach ($products as $product) {
            $product->setIsFromWishlist(true);
        }

        return $products;
    }
}
