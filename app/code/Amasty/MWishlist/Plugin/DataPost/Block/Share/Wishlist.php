<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\DataPost\Block\Share;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Share\Wishlist as SharedBlock;

class Wishlist extends Replacer
{
    /**
     * @param SharedBlock $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(SharedBlock $subject, $result)
    {
        $this->dataPostReplace($result, static::WISHLIST_REGEX);

        return $result;
    }
}
