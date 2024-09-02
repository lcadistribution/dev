<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Traits;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\Model\Wishlist;

trait ComponentProvider
{
    /**
     * @param WishlistInterface $wishlist
     * @return array
     */
    public function getComponentData(WishlistInterface $wishlist): array
    {
        $componentsForReload = $this->getContext()->getRequest()->getParam(UpdateAction::COMPONENT_PARAM, '');
        $componentsForReload = explode(',', $componentsForReload);

        $componentData = [];

        foreach ($componentsForReload as $component) {
            if ($registryName = $this->getComponentRegistryName($component)) {
                $componentData[$registryName][$component] = $this->retrieveValue($wishlist, $component);
            }
        }

        return $componentData;
    }

    /**
     * @param string $component
     * @return string|null
     */
    private function getComponentRegistryName(string $component): ?string
    {
        switch ($component) {
            case 'itemsQty':
                $registryName = 'ampagetitle';
                break;
            default:
                $registryName = null;
        }

        return $registryName;
    }

    /**
     * @param WishlistInterface|Wishlist $wishlist
     * @param string $component
     * @return mixed|null
     */
    private function retrieveValue(WishlistInterface $wishlist, string $component)
    {
        switch ($component) {
            case 'itemsQty':
                $value = $wishlist->getItemCollection()->count();
                break;
            default:
                $value = null;
        }

        return $value;
    }
}
