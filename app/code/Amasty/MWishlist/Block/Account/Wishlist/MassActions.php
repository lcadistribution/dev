<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\Api\WishlistProviderInterface;
use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\Element\Template\Context;

class MassActions extends AbstractPostBlock
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/mass_actions.phtml';

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(
        WishlistProviderInterface $wishlistProvider,
        UrlHelper $urlHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wishlistProvider = $wishlistProvider;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @return string
     */
    public function getAddToCartData()
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::IN_CART_ITEMS_ROUTE), [
            'wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId(),
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl(
                $this->getUrl(
                    PostHelper::VIEW_WISHLIST_ROUTE,
                    ['wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId()]
                )
            )
        ]);
    }

    /**
     * @return string
     */
    public function getMoveData(): string
    {
        return $this->getPostHelper()->getPostData(
            $this->getUrl(PostHelper::MOVE_ITEMS_ROUTE),
            [
                'wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId(),
                UpdateAction::BLOCK_PARAM => 'customer.wishlist',
                UpdateAction::COMPONENT_PARAM => 'itemsQty'
            ]
        );
    }

    /**
     * @return string
     */
    public function getCopyData(): string
    {
        return $this->getPostHelper()->getPostData(
            $this->getUrl(PostHelper::COPY_ITEMS_ROUTE),
            ['wishlist_id' => $this->wishlistProvider->getWishlist()->getWishlistId()]
        );
    }
}
