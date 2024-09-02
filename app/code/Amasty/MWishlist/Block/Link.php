<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block;

use Amasty\MWishlist\Model\ConfigProvider;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Block\Link as NativeLink;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class Link extends NativeLink
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::link.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Context $context,
        WishlistHelper $wishlistHelper,
        array $data = []
    ) {
        parent::__construct($context, $wishlistHelper, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        $result = null;

        if ($this->configProvider->isEnabled()) {
            $result = __('My Wish Lists');
        } else {
            $result = parent::getLabel();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl(PostHelper::LIST_WISHLIST_ROUTE);
    }
}
