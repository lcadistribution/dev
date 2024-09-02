<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Customerprice\Grid;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory;
use \Magento\Store\Model\StoreManagerInterface;

class CustomerpriceGrid extends \Magento\Backend\Block\Widget\Grid\Extended
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
     * @param Registry $coreRegistry
     * @param \Magento\Directory\Helper\Data $_directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data 
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        \Magento\Directory\Helper\Data $_directoryHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->_directoryHelper = $_directoryHelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerprice_id');
        $this->setDefaultSort('customerprice_id', 'desc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addFieldToFilter('customer_id',$this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {   

        $this->addColumn(
            'customerprice_id',
            ['header' => __('ID'), 'index' => 'customerprice_id', 'type' => 'number', 'width' => '100px']
        );

        $this->addColumn(
            'website_id',
            [
                'header' => __('Website'),
                'index' => 'website_id',
                'type' => 'options',
                'options' => $this->getWebsites()
            ]
        );

        $this->addColumn(
            'product_id',
            ['header' => __('Product ID'), 'index' => 'product_id', 'type' => 'number', 'width' => '100px']
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'product_name',
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'number',
                'renderer' => 'Magedelight\Customerprice\Block\Adminhtml\Customer\Customerprice\Grid\Renderer\Price'
            ]
        );

        $this->addColumn(
            'new_price',
            [
                'header' => __('Special Price'),
                'index' => 'new_price',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'qty',
            [
                'header' => __('Qty'),
                'index' => 'qty',
                'type' => 'number',
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
                    'url' => ['base' => '*/*/customerpricedelete'],
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

    private function getWebsites()
    {
        if ($this->websites !== null) {
            return $this->websites;
        }

        $this->websites = [ 0 => 'All Websites '.$this->_directoryHelper->getBaseCurrencyCode()];

        /** @var $website \Magento\Store\Model\Website */
        $allWebsites = $this->_storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $this->websites[$website->getId()] = $website->getName()." ".$website->getBaseCurrencyCode();
        }
        return $this->websites;
    }

    /*public function getGridUrl()
    {
      return $this->getUrl('md_customerprice/index/customer', array('_current'=>true));
    }*/

}