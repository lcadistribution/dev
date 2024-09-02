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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class OutOfStockEnd
 * @package Mageplaza\LayeredNavigationUltimate\Model\Config\Source
 */
class OutOfStockEnd implements ArrayInterface
{
    const DISABLED      = 0;
    const BASE_ON_QTY   = 1;
    const BASE_ON_LABEL = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::BASE_ON_QTY,
                'label' => __('Base on qty (<1)')
            ],
            [
                'value' => self::DISABLED,
                'label' => __('No')
            ],
            [
                'value' => self::BASE_ON_LABEL,
                'label' => __('Base on Stock Label')
            ]
        ];
    }
}
