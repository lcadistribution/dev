<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\CustomerpriceCategory\Grid;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory;


class CustomerCategoryPriceGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var array
     */
    private $websites;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data 
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerpricecategory_id');
        $this->setDefaultSort('customerpricecategory_id', 'desc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_collectionFactory->create()->addFieldToFilter('customer_id',$customerId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {   

        $this->addColumn(
            'customerpricecategory_id',
            ['header' => __('ID'), 'index' => 'customerpricecategory_id', 'type' => 'number', 'width' => '100px']
        );


        $this->addColumn(
            'category_id',
            ['header' => __('Category ID'), 'index' => 'category_id', 'type' => 'number', 'width' => '100px']
        );

        $this->addColumn(
            'category_name',
            [
                'header' => __('Category Name'),
                'index' => 'category_name',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'discount',
            [
                'header' => __('Discount in %'),
                'index' => 'discount',
                'type' => 'number',
                /*'renderer' => 'Magedelight\Customerprice\Block\Adminhtml\Customer\Price\Grid\Renderer\Price'*/
            ]
        );

        $this->addColumn(
            'expiry_date',
            [
                'header' => __('Valid till'),
                'index' => 'expiry_date',
                /*'type' => 'date',*/
            ]
        );

        $this->addColumn(
        'delete_action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Delete'),
                    'url' => ['base' => '*/*/categorydelete'],
                    'field' => 'id',
                ],
            ],
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action',
        ]);

        $this->addColumn(
        'edit_action', [
            'header' => __('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Edit'),
                ],
            ],
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action',
        ]);
        
        return parent::_prepareColumns();
    }

    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /*public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    }*/

    
    /*public function getGridUrl()
    {
      return $this->getUrl('md_customerprice/index/customer', array('_current'=>true));
    }*/

}