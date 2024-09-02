<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model;

use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerpriceCategory extends AbstractModel implements CustomerpriceCategoryInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Customerprice\Model\ResourceModel\CustomerpriceCategory::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerpricecategoryId()
    {
        return $this->getData(self::CUSTOMERPRICECATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerpricecategoryId($customerpricecategoryId)
    {
        return $this->setData(self::CUSTOMERPRICECATEGORY_ID, $customerpricecategoryId);
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
    public function getCustomerName()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryName()
    {
        return $this->getData(self::CATEGORY_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryName($categoryName)
    {
        return $this->setData(self::CATEGORY_NAME, $categoryName);
    }

    /**
     * @inheritDoc
     */
    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
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
}

