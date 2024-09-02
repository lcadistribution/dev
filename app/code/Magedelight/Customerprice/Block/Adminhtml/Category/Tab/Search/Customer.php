<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

/**
 * Customer grid
 *
 */
namespace Magedelight\Customerprice\Block\Adminhtml\Category\Tab\Search;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Customer extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory
     */
    protected $_categorypriceFactory;

    /**
     * @var  \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory $categorypriceFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory $categorypriceFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_categorypriceFactory = $categorypriceFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_customer');
        $this->setDefaultSort('customer_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedCustomer();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }
        $collection = $this->_customerFactory->create()->getCollection();
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addAttributeToFilter('store_id', $storeId);
        }
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedCustomer();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn(
                'in_category',
                [
                    'type' => 'checkbox',
                    'name' => 'in_category',
                    'values' => $this->_getSelectedCustomer(),
                    'index' => 'entity_id',
                    'renderer'=> \Magedelight\Customerprice\Block\Adminhtml\Category\Tab\Renderer\Renderer::class,
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('firstname', ['header' => __('Name'), 'index' => 'firstname']);
        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('md_customerprice/category/CustomerGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedCustomer()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $vProducts = $this->_categorypriceFactory->create()
                                ->addFieldToFilter('category_id', $this->getCategory()->getId())
                                ->addFieldToSelect('customer_id');
            $products = [];
            foreach ($vProducts as $pdct) {
                $products[]  = $pdct->getCustomerId();
            }
        }
        return $products;
    }
}
