<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Model\Config\Source;

use Mageplaza\TableCategoryView\Model\Config\AbstractSource;

/**
 * Class Events
 * @package Mageplaza\TableCategoryView\Model\Config\Source
 */
class AddButton extends AbstractSource
{
    const EACH = '0';
    const ALL  = '1';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::EACH => __('Add Each Product To Cart'),
            self::ALL  => __('Add All To Cart')
        ];
    }
}
