<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Plugin\DataPost;

use Amasty\MWishlist\Model\ConfigProvider;

class Replacer
{
    public const DATA_POST = 'data-post';
    public const DATA_ADD_POPUP_ATTRIBUTE = 'data-mwishlist-popup="open"';
    public const DATA_POST_AJAX = 'data-mwishlist-ajax';

    public const HREF_ATTR = '@href="#"@';
    public const WISHLIST_REGEX = '@(<a[^>]*)data-post([^>]*towishlist[^>]*)@';
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param string $html
     * @param string $pattern
     */
    public function dataPostReplace(&$html, $pattern = '@' . self::DATA_POST . '@')
    {
        if ($this->configProvider->isEnabled()) {
            $html = preg_replace(
                $pattern,
                '$1' . static::DATA_ADD_POPUP_ATTRIBUTE . ' ' . static::DATA_POST_AJAX . '$2',
                $html
            );
//        $html = preg_replace(static::HREF_ATTR, '', $html);
        }
    }
}
