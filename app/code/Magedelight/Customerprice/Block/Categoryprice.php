<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block;

class Categoryprice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magedelight\Customerprice\Model\CategorypriceFactory
     */
    protected $categoryprice;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Catalog\Block\Product\Context             $context
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Catalog\Model\ProductFactory              $productFactory
     * @param \Magento\Framework\Url\Helper\Data                 $urlHelper
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory $categoryprice,
        \Magedelight\Customerprice\Helper\Data $helper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->categoryFactory = $categoryFactory;
        $this->categoryprice = $categoryprice;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getCategories()
    {
        $customerId = $this->customerSession->getId();
        $collections = $this->categoryprice->create()->getCollection()
                ->addFieldToSelect('*')->addFieldToFilter('customer_id', ['eq' => $customerId]);
        return $collections;
    }
    public function getCategory($catId)
    {
        $customerId = $this->customerSession->getId();
        $category = $this->categoryFactory->create()->load($catId);
        return $category;
    }

    public function getmoduleStatus()
    {
        if ($this->helper->isEnabled()) {
            return true;
        }
        return false;
    }
}
