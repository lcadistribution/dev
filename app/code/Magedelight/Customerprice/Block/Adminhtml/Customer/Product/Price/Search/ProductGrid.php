<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Product\Price\Search;

/**
 * Adminhtml product grid block.
 *
 */
class ProductGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * Session quote.
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Catalog config.
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Product factory.
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory   $productFactory
     * @param \Magento\Catalog\Model\Config           $catalogConfig
     * @param \Magento\Backend\Model\Session\Quote    $sessionQuote
     * @param \Magento\Framework\Module\Manager       $moduleManager
     * @param \Magento\Catalog\Model\Product\Type     $type
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_sessionQuote = $sessionQuote;
        $this->_type = $type;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerprice_product_search_grid');

        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Retrieve quote store object.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_sessionQuote->getStore();
    }


    /**
     * Add column filter to collection.
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare collection to be displayed in the grid.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $attributes = $this->_catalogConfig->getProductAttributes();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productFactory->create()->getCollection();
        $collection->setStore($this->getStore())
                ->addAttributeToSelect($attributes)
                ->addAttributeToSelect('sku')
                ->addStoreFilter()
                ->addAttributeToFilter('type_id', ['simple', 'downloadable', 'virtual']);

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id',
            'header_css_class' => 'col-select',
            'column_css_class' => 'col-select',
                ]
        );
        $this->addColumn(
            'entity_id',
            [
            'header' => __('ID'),
            'sortable' => true,
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'index' => 'entity_id',
                ]
        );
        $this->addColumn(
            'name',
            [
            'header' => __('Product Name'),

            'index' => 'name',
                ]
        );
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
            'header' => __('Price'),
            'column_css_class' => 'price',
            'type' => 'currency',
            'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
            'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
            'index' => 'price',
            'renderer' => \Magedelight\Customerprice\Block\Adminhtml\Customer\Product\Price\Search\Renderer\Price::class,
                ]
        );

        $this->addColumn(
            'type',
            [
            'header' => __('Type'),
            'index' => 'type_id',
            'type' => 'options',
            'options' => $this->_type->getOptionArray(),
                ]
        );

        $this->addColumn(
            'qty',
            [
            'header' => __('Quantity'),
            'type' => 'number',
            'index' => 'qty',
                ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'md_customerprice/*/loadblock',
            ['block' => 'customer_product_grid', '_current' => true, 'collapse' => null]
        );
    }

    /**
     * Get selected products.
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', []);

        return $products;
    }

    /**
     * Add custom options to product collection.
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addOptionsToResult();

        return parent::_afterLoadCollection();
    }
}
