<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\Xsearch\Block\Search;

use Amasty\MWishlist\Plugin\DataPost\Replacer;

class Product extends Replacer
{
    public function afterToHtml($subject, string $result) : string
    {
        $this->dataPostReplace($result);

        return $result;
    }
}
