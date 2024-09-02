<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CategorySaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManagerInterface;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory
     */
    protected $customerpriceCategory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory 
     */
    protected $customerFactory;

    /**
     * @param \Magento\Framework\App\Request\Http       $request
     * @param \Magedelight\Customerprice\Helper\Data    $helper
     * @param \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory $customerpriceCategory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Customerprice\Helper\Data $helper,
        \Magedelight\Customerprice\Model\CustomerpriceCategoryFactory $customerpriceCategory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManagerInterface
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->customerpriceCategory = $customerpriceCategory;
        $this->customerFactory = $customerFactory;
        $this->messageManagerInterface = $messageManagerInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return bool
     * @codingStandardsIgnoreStart
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $options = $this->request->getPostValue();
        if ($this->helper->isEnabled()) {
            if (isset($options['customeroption'])) {
                foreach ($options['customeroption'] as $key => $_options) {
                    foreach ($_options as $k => $value) {
                        if ($key == 'value') {
                            $priceCustomerCategory = $this->customerpriceCategory->create();
                            $customer = $this->customerFactory->create()->load($value['pid']);
                            if (is_int($k)) {
                                $priceCustomerCategory->setId($k);
                            }

                            $date = $value['date'];
                            $timestamp = strtotime($date);
                            if (is_int($timestamp) || is_null($timestamp)) {
                                $date = date('Y-m-d', $timestamp);
                            } else {
                                $date = NULL;
                            }

                            $priceCustomerCategory->setCustomerId($value['pid'])
                                    ->setCustomerName(trim($customer->getName()))
                                    ->setCustomerEmail(trim($customer->getEmail()))
                                    ->setCategoryId($options['category_id'])
                                    ->setCategoryName(trim($options['name']))
                                    ->setExpiryDate($date)
                                    ->setDiscount($value['discount']);
                            try{
                                $priceCustomerCategory->save();
                            } catch (LocalizedException $e) {
                                $this->messageManagerInterface->addErrorMessage($e->getMessage());
                            }
                        }
                    }
                }
            }
        }else {
            if (isset($options['customeroption'])) {
                unset($options['customeroption']);
                throw new LocalizedException(__('Magedelight Price Per Customer extension is disabled'));
            }
        }
    }
}
