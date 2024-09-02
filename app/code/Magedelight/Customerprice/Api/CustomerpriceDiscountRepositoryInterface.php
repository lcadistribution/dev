<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerpriceDiscountRepositoryInterface
{

    /**
     * Save CustomerpriceDiscount
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount
    );

    /**
     * Retrieve CustomerpriceDiscount
     * @param string $customerpricediscountId
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customerpricediscountId);

    /**
     * Retrieve CustomerpriceDiscount matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerpriceDiscount
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface $customerpriceDiscount
    );

    /**
     * Delete CustomerpriceDiscount by ID
     * @param string $customerpricediscountId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerpricediscountId);
}

