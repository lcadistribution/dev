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

namespace Mageplaza\TableCategoryView\Plugin\CatalogHelper;

use Magento\Catalog\Helper\Product\ProductList as CatalogProductList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\TableCategoryView\Helper\Data;

/**
 * Class Toolbar
 * @package Mageplaza\TableCategoryView\Plugin\CatalogHelper
 */
class ProductList
{
    const TABLE = 'table';

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ProductList constructor.
     *
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $helperData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param CatalogProductList $productList
     * @param $result
     * @param array $option
     *
     * @return mixed
     */
    public function afterGetDefaultViewMode(
        CatalogProductList $productList,
        $result,
        $option = []
    )
    {
        if (array_key_exists(self::TABLE, $option)
            && $this->_helperData->isEnabled() && $this->_helperData->isDefault()) {
            $result = self::TABLE;
        }

        return $result;
    }

    /**
     * @param CatalogProductList $productList
     * @param callable $proceed
     * @param $mode
     *
     * @return array|false|string
     */
    public function aroundGetAvailableLimit(
        CatalogProductList $productList,
        callable $proceed,
        $mode
    )
    {
        if ($mode === self::TABLE
            && $this->_helperData->isEnabled()) {
            $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
            $perPageValues = (string)$this->scopeConfig->getValue(
                $perPageConfigKey,
                ScopeInterface::SCOPE_STORE
            );
            $perPageValues = explode(',', $perPageValues);
            $perPageValues = array_combine($perPageValues, $perPageValues);
            if ($this->scopeConfig->isSetFlag(
                'catalog/frontend/list_allow_all',
                ScopeInterface::SCOPE_STORE
            )) {
                return ($perPageValues + ['all' => __('All')]);
            } else {
                return $perPageValues;
            }
        }

        return $proceed($mode);
    }

    /**
     * @param CatalogProductList $productList
     * @param callable $proceed
     * @param $mode
     *
     * @return mixed
     */
    public function aroundGetDefaultLimitPerPageValue(
        CatalogProductList $productList,
        callable $proceed,
        $mode
    )
    {
        if ($mode === self::TABLE && $this->_helperData->isEnabled()) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/table_per_page',
                ScopeInterface::SCOPE_STORE
            );
        } else {
            return $proceed($mode);
        }
    }
}
