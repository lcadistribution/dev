<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\View\Element\Template;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Wishlist\Controller\WishlistProviderInterface;

class Search extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/search_wrapper.phtml';

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    public function __construct(
        SearchHelper $searchHelper,
        WishlistProviderInterface $wishlistProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchHelper = $searchHelper;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * @return false|string
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->updateSearchConfig($this->jsLayout);

        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function updateSearchConfig(array $jsLayout): array
    {
        if (isset($jsLayout['components']['search']['config'])) {
            $jsLayout['components']['search']['config']['minChars'] = $this->searchHelper->getMinQueryLength();
            $jsLayout['components']['search']['config']['maxChars'] = $this->searchHelper->getMaxQueryLength();
            $jsLayout['components']['search']['config']['searchUrl'] = $this->getSearchUrl();
            $jsLayout['components']['search']['config']['addUrl'] = $this->getAddUrl();
            $jsLayout['components']['search']['config']['wishlist_id'] = $this->wishlistProvider->getWishlist()
                ->getId();
        }

        return $jsLayout;
    }

    /**
     * @return string
     */
    private function getSearchUrl(): string
    {
        return $this->getUrl(PostHelper::PRODUCT_SEARCH_ROUTE);
    }

    /**
     * @return string
     */
    private function getAddUrl(): string
    {
        return $this->getUrl(PostHelper::ADD_ITEM_ROUTE);
    }
}
