<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Catalog\Price\Search;

/**
 * Adminhtml customer grid block.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Customer factory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Customer Group factory.
     *
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroupFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerGroupFactory = $customerGroupFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerprice_customer_search_grid');

        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
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
            $productIds = $this->_getSelectedCustomers();
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
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_customerFactory->create()->getCollection()
                ->addNameToSelect()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('group_id');

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
            'in_customers',
            [
            'type' => 'checkbox',
            'name' => 'in_customers',
            'values' => $this->_getSelectedCustomers(),
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
            'header' => __('Name'),
            'index' => 'name',
                ]
        );

        $this->addColumn(
            'email',
            [
            'header' => __('Email'),
            'index' => 'email',
                ]
        );

        $groups = $this->_customerGroupFactory->create()->getCollection()
                ->addFieldToFilter('customer_group_id', ['gt' => 0])
                ->load()
                ->toOptionHash();

        $this->addColumn('group', [
            'header' => __('Group'),
            'index' => 'group_id',
            'type' => 'options',
            'options' => $groups,
        ]);

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
            'md_customerprice/product/loadblock',
            ['block' => 'product_customer_grid', '_current' => true, 'collapse' => null]
        );
    }

    /**
     * Get selected products.
     *
     * @return mixed
     */
    protected function _getSelectedCustomers()
    {
        $products = $this->getRequest()->getPost('customers', []);
        return $products;
    }

    /**
     * Add custom options to product collection.
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection();
        return parent::_afterLoadCollection();
    }
}
