<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver\DataProvider;

class Customerspecialprice
{
    /**
     * @var \Magedelight\Customerprice\Model\CustomerpriceSpecialpriceFactory $customerSpecialprice
     */
    protected $customerSpecialprice;

    /**
     * @param \Magedelight\Customerprice\Model\CustomerpriceSpecialpriceFactory $customerSpecialprice
     */
    public function __construct(
        \Magedelight\Customerprice\Model\CustomerpriceSpecialpriceFactory $customerSpecialprice
    ) {
        $this->customerSpecialprice  = $customerSpecialprice;
    }
    /**
     * @params int $customerpriceid
     * this function return all the word of the day by id
     **/
    public function getcustomerSpecialPrice()
    {
        try {
            $collection = $this->customerSpecialprice->create()->getCollection();
            $collection->setOrder('customerspecialprice_id', 'ASC');
            $customerSpecialpriceData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $customerSpecialpriceData;
    }
}
