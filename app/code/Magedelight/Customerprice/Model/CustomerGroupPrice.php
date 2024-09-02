<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerGroupPrice extends AbstractModel implements CustomerGroupPriceInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Customerprice\Model\ResourceModel\CustomerGroupPrice::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomergrouppriceId()
    {
        return $this->getData(self::CUSTOMERGROUPPRICE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomergrouppriceId($customergrouppriceId)
    {
        return $this->setData(self::CUSTOMERGROUPPRICE_ID, $customergrouppriceId);
    }

    /**
     * @inheritDoc
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getPriceType()
    {
        return $this->getData(self::PRICE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::PRICE_TYPE, $priceType);
    }
}

