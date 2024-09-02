<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver\DataProvider;

class Customerprice
{
    /**
     * @var \Magedelight\Customerprice\Model\CustomerpriceFactory $customerprice
     */
    protected $customerprice;

    /**
     * @param \Magedelight\Customerprice\Model\CustomerpriceFactory $customerprice
     */
    public function __construct(
        \Magedelight\Customerprice\Model\CustomerpriceFactory $customerprice
    ) {
        $this->customerprice  = $customerprice;
    }
    /**
     * @params int $customerpriceid
     * this function return all the word of the day by id
     **/
    public function getcustomerPrice()
    {
        try {
            $collection = $this->customerprice->create()->getCollection();
            $collection->setOrder('customerprice_id', 'ASC');
            $customerData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $customerData;
    }
}
