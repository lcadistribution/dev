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
 * @package     Mageplaza_ConfigureGridView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ConfigureGridView\Model\Config\Source;

use Mageplaza\ConfigureGridView\Model\Config\AbstractSource;

/**
 * Class Events
 * @package Mageplaza\ConfigureGridView\Model\Config\Source
 */
class Active extends AbstractSource
{
    const ACTIVE   = '1';
    const INACTIVE = '0';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::ACTIVE   => __('Active'),
            self::INACTIVE => __('Inactive')
        ];
    }
}
