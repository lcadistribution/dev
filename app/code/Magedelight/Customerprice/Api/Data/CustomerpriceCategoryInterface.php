<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceCategoryInterface
{

    const CATEGORY_NAME = 'category_name';
    const EXPIRY_DATE = 'expiry_date';
    const CATEGORY_ID = 'category_id';
    const DISCOUNT = 'discount';
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMERPRICECATEGORY_ID = 'customerpricecategory_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * Get customerpricecategory_id
     * @return string|null
     */
    public function getCustomerpricecategoryId();

    /**
     * Set customerpricecategory_id
     * @param string $customerpricecategoryId
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCustomerpricecategoryId($customerpricecategoryId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get customer_name
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set customer_name
     * @param string $customerName
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get customer_email
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set customer_email
     * @param string $customerEmail
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId();

    /**
     * Set category_id
     * @param string $categoryId
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCategoryId($categoryId);

    /**
     * Get category_name
     * @return string|null
     */
    public function getCategoryName();

    /**
     * Set category_name
     * @param string $categoryName
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setCategoryName($categoryName);

    /**
     * Get discount
     * @return string|null
     */
    public function getDiscount();

    /**
     * Set discount
     * @param string $discount
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setDiscount($discount);

    /**
     * Get expiry_date
     * @return string|null
     */
    public function getExpiryDate();

    /**
     * Set expiry_date
     * @param string $expiryDate
     * @return \Magedelight\Customerprice\CustomerpriceCategory\Api\Data\CustomerpriceCategoryInterface
     */
    public function setExpiryDate($expiryDate);
}

