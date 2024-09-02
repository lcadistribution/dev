<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Config\Backend;

/**
 * Backend model for customer price  CSV importing.
 *
 */
class Import extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceFactory
     */
    protected $_customerPriceFactory;

    /**
     * @param \Magento\Framework\Model\Context                           $context
     * @param \Magento\Framework\Registry                                $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface         $config
     * @param \Magento\Framework\App\Cache\TypeListInterface             $cacheTypeList
     * @param \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceFactory $customerPriceFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource    $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb              $resourceCollection
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceFactory $customerPriceFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_customerPriceFactory = $customerPriceFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Magedelight\Customerprice\Model\Customerprice $customerPrice */
        $customerPrice = $this->_customerPriceFactory->create();
        $customerPrice->uploadAndImport($this);

        return parent::afterSave();
    }
}
