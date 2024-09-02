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

use Magento\Catalog\Model\ProductTypes\Config;
use Mageplaza\TableCategoryView\Model\Config\AbstractSource;

/**
 * Class Events
 * @package Mageplaza\TableCategoryView\Model\Config\Source
 */
class PopupOption extends AbstractSource
{
    /**
     * @var Config
     */
    protected $_typeConfig;

    /**
     * PopupOption constructor.
     *
     * @param Config $typeConfig
     */
    public function __construct(
        Config $typeConfig
    ) {
        $this->_typeConfig = $typeConfig;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $types = ['' => __('-- Please Select --')];

        foreach ($this->_typeConfig->getAll() as $type => $value) {
            $types[$type] = $value['label'];
        }

        return $types;
    }
}
