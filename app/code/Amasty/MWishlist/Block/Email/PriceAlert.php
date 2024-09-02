<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Email;

use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template\Context;

class PriceAlert extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::email/price.phtml';

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    public function __construct(
        ImageBuilder $imageBuilder,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->imageBuilder = $imageBuilder;
    }

    protected function getPriceRender(): BlockInterface
    {
        return $this->_layout->createBlock(
            Render::class,
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    public function getProductPriceHtml(
        Product $product,
        string $priceType = FinalPrice::PRICE_CODE,
        string $renderZone = Render::ZONE_EMAIL,
        array $arguments = []
    ): string {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var Render $priceRender */
        $priceRender = $this->getPriceRender();
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                $priceType,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * @param int $wishlistId
     * @return string
     */
    public function getViewUrl(int $wishlistId): string
    {
        return $this->getUrl(PostHelper::VIEW_WISHLIST_ROUTE, ['wishlist_id' => $wishlistId]);
    }

    public function getImage(Product $product, string $imageId, array $attributes = []): Image
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    public function getImageUrl(Product $product): string
    {
        return $this->getImage($product, 'mwishlist_item_email_image', ['class' => 'photo image'])->getImageUrl();
    }
}
