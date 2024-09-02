<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver\DataProvider;

class Customergroupprice
{
    /**
     * @var \Magedelight\Customerprice\Model\CustomerGroupPriceFactory $customerGroupPrice
     */
    protected $customerGroupPrice;

    /**
     * @param \Magedelight\Customerprice\Model\CustomerGroupPriceFactory $customerGroupPrice
     */
    public function __construct(
        \Magedelight\Customerprice\Model\CustomerGroupPriceFactory $customerGroupPrice
    ) {
        $this->customerGroupPrice  = $customerGroupPrice;
    }
    /**
     * @params int $customerpriceid
     * this function return all the word of the day by id
     **/
    public function getCustomerGroupPrice()
    {
        try {
            $collection = $this->customerGroupPrice->create()->getCollection();
            $collection->setOrder('customergroupprice_id', 'ASC');
            $customerGroupData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $customerGroupData;
    }
}
