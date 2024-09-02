<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\DataPost\Block\Category;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist as CategoryWishlist;

class Wishlist extends Replacer
{
    /**
     * @param CategoryWishlist $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(CategoryWishlist $subject, $result)
    {
        $this->dataPostReplace($result);

        return $result;
    }
}
