<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerGroupPriceInterface
{

    const PRICE_TYPE = 'price_type';
    const CUSTOMERGROUPPRICE_ID = 'customergroupprice_id';
    const GROUP_ID = 'group_id';
    const VALUE = 'value';

    /**
     * Get customergroupprice_id
     * @return string|null
     */
    public function getCustomergrouppriceId();

    /**
     * Set customergroupprice_id
     * @param string $customergrouppriceId
     * @return \Magedelight\Customerprice\CustomerGroupPrice\Api\Data\CustomerGroupPriceInterface
     */
    public function setCustomergrouppriceId($customergrouppriceId);

    /**
     * Get group_id
     * @return string|null
     */
    public function getGroupId();

    /**
     * Set group_id
     * @param string $groupId
     * @return \Magedelight\Customerprice\CustomerGroupPrice\Api\Data\CustomerGroupPriceInterface
     */
    public function setGroupId($groupId);

    /**
     * Get value
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     * @param string $value
     * @return \Magedelight\Customerprice\CustomerGroupPrice\Api\Data\CustomerGroupPriceInterface
     */
    public function setValue($value);

    /**
     * Get price_type
     * @return string|null
     */
    public function getPriceType();

    /**
     * Set price_type
     * @param string $priceType
     * @return \Magedelight\Customerprice\CustomerGroupPrice\Api\Data\CustomerGroupPriceInterface
     */
    public function setPriceType($priceType);
}

