<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Cron;

use Magedelight\Customerprice\Model\ResourceModel\Customerprice\CollectionFactory;
use Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory\CollectionFactory as CategoryCollectionFactory;
use Magedelight\Customerprice\Helper\Data;

class ChangeCustomerPriceStatus
{
    
    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var CategoryCollectionFactory */
    private $categoryCollectionFactory;

    /** @var Data */
    private $customerPriceHelper;

   

    /**
     * @param CollectionFactory $collectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Data $customerPriceHelper
     */
    public function __construct(CollectionFactory $collectionFactory,
      CategoryCollectionFactory $categoryCollectionFactory,
      Data $customerPriceHelper)
    {
        $this->collectionFactory = $collectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
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
          if($item->getExpiryDate()){
            if (strtotime($item->getExpiryDate()) > strtotime('0000-00-00 00:00:00')) {
              if (strtotime($enddate) > strtotime($item->getExpiryDate())) {
                $item->delete();
              }
            }
          }
        }

        $categoryPriceCollection = $this->categoryCollectionFactory->create();
        foreach ($categoryPriceCollection as $item) {
          if($item->getExpiryDate()){
            if (strtotime($item->getExpiryDate()) > strtotime('0000-00-00 00:00:00')) {
              if (strtotime($enddate) > strtotime($item->getExpiryDate())) {
                $item->delete();
              }
            }
          }
        }
      }
    }
}
