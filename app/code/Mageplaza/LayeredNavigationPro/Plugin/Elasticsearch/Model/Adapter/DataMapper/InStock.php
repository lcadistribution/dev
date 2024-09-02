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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;

/**
 * Class InStock
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class InStock implements DataMapperInterface
{
    const FIELD_NAME = 'mp_in_stock';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var CustomerGroupCollectionFactory
     */
    protected $customerGroupCollection;

    protected $onSaleProductIds = [];
    /**
     * @var Configurable
     */
    protected $configurable;
    /**
     * @var Stock
     */
    protected $stockHelper;

    /**
     * OnSale constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Configurable $configurable
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Configurable $configurable,
        Stock $stockHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager             = $storeManager;
        $this->scopeConfig              = $scopeConfig;
        $this->configurable             = $configurable;
        $this->stockHelper              = $stockHelper;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     *
     * @return int[]
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $products = $this->productCollectionFactory->create()->addAttributeToFilter('entity_id', $entityId)->load();
        $this->stockHelper->addInStockFilterToCollection($products);
        $value = $products->getSize();
        return [self::FIELD_NAME => (int) $value];
    }

    /**
     * @inheritDoc
     */
    public function isAllowed()
    {
        return true;
    }
}
