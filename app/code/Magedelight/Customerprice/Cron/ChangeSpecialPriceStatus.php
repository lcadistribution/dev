<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Cron;

use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice\CollectionFactory;
use Magedelight\Customerprice\Helper\Data;

class ChangeSpecialPriceStatus
{
    
    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var CollectionFactory */
    private $customerPrice;

    /** @var Data */
    private $customerPriceHelper;

   

    /**
     * @param CollectionFactory $collectionFactory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice
     * @param Data $customerPriceHelper
     */
    public function __construct(CollectionFactory $collectionFactory, \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerPrice,Data $customerPriceHelper)
    {
        $this->collectionFactory = $collectionFactory;
        $this->customerPrice = $customerPrice;
        $this->customerPriceHelper = $customerPriceHelper;
    }

     /**
      * update specialprice status
      *
      * @return RedirectFactory
      */
    public function execute()
    {
      if ($this->customerPriceHelper->isEnabled()) {
        $customerpriceCollection = $this->collectionFactory->create();
        $enddate = date("Y-m-d 00:00:00");
        foreach ($customerpriceCollection as $item) {
            if (strtotime($item->getExpiryDate()) > strtotime('0000-00-00 00:00:00')) {
                if (strtotime($enddate) > strtotime($item->getExpiryDate())) {
                    $customerpriceId = $item->getCustomerpriceId();
                    $customerpriceModel = $this->customerPrice->load($customerpriceId);
                    $customerpriceModel->delete();
                    $item->setApprove(0);
                    $item->setCustomerpriceId("");
                    $item->save();
                }
            }
        }
      }
    }
}
