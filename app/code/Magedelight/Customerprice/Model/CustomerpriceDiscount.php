<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerpriceDiscount extends AbstractModel implements CustomerpriceDiscountInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Customerprice\Model\ResourceModel\CustomerpriceDiscount::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerpricediscountId()
    {
        return $this->getData(self::CUSTOMERPRICEDISCOUNT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerpricediscountId($customerpricediscountId)
    {
        return $this->setData(self::CUSTOMERPRICEDISCOUNT_ID, $customerpricediscountId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
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
}

