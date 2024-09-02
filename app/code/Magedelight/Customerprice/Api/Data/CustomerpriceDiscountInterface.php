<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceDiscountInterface
{

    const CUSTOMERPRICEDISCOUNT_ID = 'customerpricediscount_id';
    const VALUE = 'value';
    const CUSTOMER_ID = 'customer_id';

    /**
     * Get customerpricediscount_id
     * @return string|null
     */
    public function getCustomerpricediscountId();

    /**
     * Set customerpricediscount_id
     * @param string $customerpricediscountId
     * @return \Magedelight\Customerprice\CustomerpriceDiscount\Api\Data\CustomerpriceDiscountInterface
     */
    public function setCustomerpricediscountId($customerpricediscountId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Magedelight\Customerprice\CustomerpriceDiscount\Api\Data\CustomerpriceDiscountInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get value
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     * @param string $value
     * @return \Magedelight\Customerprice\CustomerpriceDiscount\Api\Data\CustomerpriceDiscountInterface
     */
    public function setValue($value);
}

