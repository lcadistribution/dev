<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Catalog;


class Customerprice extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productmodel
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productmodel,
        array $data = []
    ) {
        $this->productmodel = $productmodel;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve field suffix.
     *
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'customerprice';
    }

    /**
     * Retrieve current store id.
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getRequest()->getParam('store');

        return (int)($storeId);
    }

    /**
     * Tab settings.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Price Per Customer');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Price Per Customer');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $id = $this->getRequest()->getParam('id');
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $this->productmodel->create()->load($id);
        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'downloadable'
            || $product->getTypeId() == 'virtual') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('md_customerprice/product/index', ['_current' => true]);
    }
}
