<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceSpecialpriceInterface
{
    const QTY = 'quantity';
    const PID = 'pid';
    const EXPIRY_DATE = 'expiry_date';
    const NAME = 'name';
    const APPROVE = 'approve';
    const EMAIL = 'email';
    const SPECIAL_PRICE = 'special_price';
    const CREATED_AT = 'created_at';
    const CUSTOMERPRICE_ID = 'customerprice_id';
    const PNAME = 'pname';
    const customerspecialprice_id = 'customerspecialprice_id';
    const ACTUAL_PRICE = 'actual_price';

    /**
     * Get customerspecialprice_id
     * @return string|null
     */
    public function getCustomerpricespecialpriceId();

    /**
     * Set customerspecialprice_id
     * @param string $customerpricespecialpriceId
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setCustomerpricespecialpriceId($customerpricespecialpriceId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setName($name);

    /**
     * Get email
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     * @param string $email
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setEmail($email);

    /**
     * Get actual_price
     * @return string|null
     */
    public function getActualPrice();

    /**
     * Set actual_price
     * @param string $actualPrice
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setActualPrice($actualPrice);

    /**
     * Get special_price
     * @return string|null
     */
    public function getSpecialPrice();

    /**
     * Set special_price
     * @param string $specialPrice
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Get pid
     * @return string|null
     */
    public function getPid();

    /**
     * Set pid
     * @param string $pid
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setPid($pid);

    /**
     * Get pname
     * @return string|null
     */
    public function getPname();

    /**
     * Set pname
     * @param string $pname
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setPname($pname);

    /**
     * Get qty
     * @return string|null
     */
    public function getQuantity();

    /**
     * Set qty
     * @param string $qty
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setQuantity($qty);

    /**
     * Get approve
     * @return string|null
     */
    public function getApprove();

    /**
     * Set approve
     * @param string $approve
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setApprove($approve);

    /**
     * Get customerprice_id
     * @return string|null
     */
    public function getCustomerpriceId();

    /**
     * Set customerprice_id
     * @param string $customerpriceId
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
     */
    public function setCustomerpriceId($customerpriceId);

    /**
     * Get expiry_date
     * @return string|null
     */
    public function getExpiryDate();

    /**
     * Set expiry_date
     * @param string $expiryDate
     * @return \Magedelight\Customerprice\CustomerpriceSpecialprice\Api\Data\CustomerpriceSpecialpriceInterface
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

