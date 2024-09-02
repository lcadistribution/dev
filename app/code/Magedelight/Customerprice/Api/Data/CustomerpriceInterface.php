<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceInterface
{

    const QTY = 'qty';
    const PRICE = 'price';
    const NEW_PRICE = 'new_price';
    const EXPIRY_DATE = 'expiry_date';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_NAME = 'customer_name';
    const PRODUCT_NAME = 'product_name';
    const CUSTOMERPRICE_ID = 'customerprice_id';
    const LOG_PRICE = 'log_price';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_ID = 'customer_id';
    const WEBSITE_ID = 'website_id';
    const CREATED_AT = 'created_at';

    /**
     * Get customerprice_id
     * @return string|null
     */
    public function getCustomerpriceId();

    /**
     * Set customerprice_id
     * @param string $customerpriceId
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setCustomerpriceId($customerpriceId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
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
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
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
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setProductId($productId);

    /**
     * Get product_name
     * @return string|null
     */
    public function getProductName();

    /**
     * Set product_name
     * @param string $productName
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setProductName($productName);

    /**
     * Get price
     * @return string|null
     */
    public function getPrice();

    /**
     * Set price
     * @param string $price
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setPrice($price);

    /**
     * Get log_price
     * @return string|null
     */
    public function getLogPrice();

    /**
     * Set log_price
     * @param string $logPrice
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setLogPrice($logPrice);

    /**
     * Get new_price
     * @return string|null
     */
    public function getNewPrice();

    /**
     * Set new_price
     * @param string $newPrice
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setNewPrice($newPrice);

    /**
     * Get qty
     * @return string|null
     */
    public function getQty();

    /**
     * Set qty
     * @param string $qty
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setQty($qty);

    /**
     * Get website_id
     * @return string|null
     */
    public function getWebsiteId();

    /**
     * Set website_id
     * @param string $websiteId
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get expiry_date
     * @return string|null
     */
    public function getExpiryDate();

    /**
     * Set expiry_date
     * @param string $expiryDate
     * @return \Magedelight\Customerprice\Customerprice\Api\Data\CustomerpriceInterface
     */
    public function setExpiryDate($expiryDate);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setCreatedAt($createdAt);
}

