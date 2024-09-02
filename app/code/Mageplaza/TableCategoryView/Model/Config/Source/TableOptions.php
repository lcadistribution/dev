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
class TableOptions extends AbstractSource
{
    const IMAGE  = '1';
    const DESC   = '2';
    const REVIEW = '3';
    const STOCK  = '4';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            '0'          => __('-- Please Select --'),
            self::IMAGE  => __('Product Image'),
            self::DESC   => __('Product Short Description'),
            self::REVIEW => __('Product Review Rating'),
            self::STOCK  => __('Product Stock Status')
        ];
    }
}
