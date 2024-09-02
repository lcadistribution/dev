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

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * Class Events
 * @package Mageplaza\ConfigureGridView\Model\Config\Source
 */
class Product extends Boolean
{
    const ACTIVE   = '1';
    const INACTIVE = '0';

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Active'), 'value' => self::ACTIVE],
                ['label' => __('Inactive'), 'value' => self::INACTIVE],
            ];
        }

        return $this->_options;
    }
}
