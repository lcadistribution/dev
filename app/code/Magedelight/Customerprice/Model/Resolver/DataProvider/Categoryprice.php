<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver\DataProvider;

class Categoryprice
{
    /**
     * @var \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory
     */
    protected $categoryprice;

    /**
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory $categoryprice
     */
    public function __construct(
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory $categoryprice
    ) {
        $this->categoryprice  = $categoryprice;
    }
    /**
     * @params int $categorypriceId
     * this function return all the word of the day by id
     **/
    public function getcategoryPrice()
    {
        try {
            $collection = $this->categoryprice->create()->getCollection();
            $collection->setOrder('customerpricecategory_id', 'ASC');
            $categoryData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $categoryData;
    }
}
