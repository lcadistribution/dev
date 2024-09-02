<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceDiscountSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomerpriceDiscount list.
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

