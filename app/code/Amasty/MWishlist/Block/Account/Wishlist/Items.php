<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Magento\Wishlist\Block\Customer\Wishlist\Items as NativeItems;

class Items extends NativeItems
{
    public const DROPDOWN_OPTIONS = 'dropdown-options';

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->filterColumns(parent::getColumns());
    }

    /**
     * Remove from general array columns which render separately
     * @param array $columns
     * @return array
     */
    public function filterColumns(array $columns): array
    {
        $dropdownColumns = $this->getGroupChildNames(static::DROPDOWN_OPTIONS);

        foreach ($columns as $key => $column) {
            if (in_array($column->getNameInLayout(), $dropdownColumns)) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }
}
