<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\DataPost\Block\Product;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Catalog\Block\Product\Compare\ListCompare as ProductListCompare;

class ListCompare extends Replacer
{
    /**
     * @param ProductListCompare $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(ProductListCompare $subject, $result)
    {
        $this->dataPostReplace($result, static::WISHLIST_REGEX);

        return $result;
    }
}
