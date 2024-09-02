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

namespace Mageplaza\TableCategoryView\Plugin\CatalogBlock\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar as CatalogToolbar;
use Magento\Framework\App\RequestInterface;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class Toolbar
 * @package Mageplaza\TableCategoryView\Plugin\CatalogBlock\Product\ProductList
 */
class Toolbar
{
    const TABLE = 'table';

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request
    ) {
        $this->_helperData = $helperData;
        $this->_request    = $request;
    }

    /**
     * @param CatalogToolbar $subject
     * @param $result
     *
     * @return array
     */
    public function afterGetModes(CatalogToolbar $subject, $result)
    {
        if ($this->_helperData->isEnabled()
            && (in_array(
                $this->_request->getFullActionName(),
                ['catalog_category_view', 'catalogsearch_result_index','ambrand_index_index'],
                true
            )
            )
        ) {
            $result[self::TABLE] = __('Table');
        }

        return $result;
    }

    /**
     * @param CatalogToolbar $toolbar
     * @param $result
     *
     * @return mixed
     */
    public function afterGetCurrentMode(CatalogToolbar $toolbar, $result)
    {
        if ($this->_helperData->isEnabled()
            && $this->_request->getParam('product_list_mode') === self::TABLE
            && (in_array(
                $this->_request->getFullActionName(),
                ['catalog_category_view', 'catalogsearch_result_index','ambrand_index_index'],
                true
            )
            )
        ) {
            $result = self::TABLE;
        }

        return $result;
    }
}
