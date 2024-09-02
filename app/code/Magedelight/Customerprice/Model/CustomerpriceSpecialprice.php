<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerpriceSpecialprice extends AbstractModel implements CustomerpriceSpecialpriceInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerpricespecialpriceId()
    {
        return $this->getData(self::customerspecialprice_id);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerpricespecialpriceId($customerpricespecialpriceId)
    {
        return $this->setData(self::customerspecialprice_id, $customerpricespecialpriceId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function getActualPrice()
    {
        return $this->getData(self::ACTUAL_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setActualPrice($actualPrice)
    {
        return $this->setData(self::ACTUAL_PRICE, $actualPrice);
    }

    /**
     * @inheritDoc
     */
    public function getSpecialPrice()
    {
        return $this->getData(self::SPECIAL_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setSpecialPrice($specialPrice)
    {
        return $this->setData(self::SPECIAL_PRICE, $specialPrice);
    }

    /**
     * @inheritDoc
     */
    public function getPid()
    {
        return $this->getData(self::PID);
    }

    /**
     * @inheritDoc
     */
    public function setPid($pid)
    {
        return $this->setData(self::PID, $pid);
    }

    /**
     * @inheritDoc
     */
    public function getPname()
    {
        return $this->getData(self::PNAME);
    }

    /**
     * @inheritDoc
     */
    public function setPname($pname)
    {
        return $this->setData(self::PNAME, $pname);
    }

    /**
     * @inheritDoc
     */
    public function getQuantity()
    {
        return $this->getData(self::PNAME);
    }

    /**
     * @inheritDoc
     */
    public function setQuantity($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getApprove()
    {
        return $this->getData(self::APPROVE);
    }

    /**
     * @inheritDoc
     */
    public function setApprove($approve)
    {
        return $this->setData(self::APPROVE, $approve);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerpriceId()
    {
        return $this->getData(self::CUSTOMERPRICE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerpriceId($customerpriceId)
    {
        return $this->setData(self::CUSTOMERPRICE_ID, $customerpriceId);
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDate()
    {
        return $this->getData(self::EXPIRY_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDate($expiryDate)
    {
        return $this->setData(self::EXPIRY_DATE, $expiryDate);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}

