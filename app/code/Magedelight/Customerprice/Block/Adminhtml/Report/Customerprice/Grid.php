<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Report\Customerprice;

use Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    /**
     * @var $productFactory
     */
    protected $productFactory;

    /**
     * @var \Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory $customerpriceCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
            $this->collectionFactory = $collectionFactory;
            $this->_coreRegistry = $coreRegistry;
            parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getData('filter_data');
        $count = 0;
        foreach ($filterData as $data) {
            $count=count($data);
           
        }
        if ($count>0) {
            
            $collection =  $this->collectionFactory->create()->addFieldToSelect('*');

            /* applied date range filter */
            if ($filterData['from'] != null && $filterData['to'] != null) {
                $startDate = date("Y-m-d", strtotime($filterData['from'])); // start date
                $endDate = date("Y-m-d", strtotime($filterData['to'])); // end date
                $collection->addDateRangeFilter($startDate,$endDate);
            }

            $this->setCollection($collection);
        }
               
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
            $this->addColumn(
                'customer_name',
                [
                    'header' => __('Customer Name'),
                    'index' => 'customer_name',
                    'filter'=>false
                ]
            );

            $this->addColumn(
                'customer_email',
                [
                    'header' => __('Customer Email'),
                    'index' => 'customer_email',
                    'filter'=>false
                ]
            );

            $this->addColumn(
                'product_name',
                [
                    'header' => __('Product Name'),
                    'index' => 'product_name',
                    'filter'=>false
                ]
            );
            
            $this->addColumn(
                'product_id',
                [
                    'header' => __('Product ID'),
                    'index' => 'product_id',
                    'filter'=>false
                ]
            );

            $this->addColumn(
                'qty',
                [
                    'header' => __('Product Qty'),
                    'index' => 'qty',
                    'filter'=>false
                ]
            );

            $this->addColumn(
                'new_price',
                [
                    'header' => __('Special Price'),
                    'index' => 'new_price',
                    'filter'=>false
                ]
            );

            $this->addColumn(
                'expiry_date',
                [
                    'header' => __('Price End By Date'),
                    'index' => 'expiry_date',
                    'renderer' => 'Magedelight\Customerprice\Block\Adminhtml\Report\Customerprice\Renderer\Expirydate',
                    'filter'=>false
                ]
            );

        $this->addExportType('*/*/exportReportCsv', __('CSV'));
        $this->addExportType('*/*/exportReportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getSearchButtonHtml()
    {
        return '';
    }

    public function getResetFilterButtonHtml()
    {
        return '';
    }

}
