<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\Product;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;

class Image
{
    public const IMAGE_ID = 'mwishlist_product_grid_image';

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(ImageHelper $imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getUrl(Product $product): string
    {
        return $this->imageHelper->init($product, static::IMAGE_ID)->getUrl();
    }
}
