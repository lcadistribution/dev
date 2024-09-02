<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerpriceRepositoryInterface
{

    /**
     * Save Customerprice
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
    );

    /**
     * Retrieve Customerprice
     * @param string $customerpriceId
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customerpriceId);

    /**
     * Retrieve Customerprice matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Customerprice
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Customerprice\Api\Data\CustomerpriceInterface $customerprice
    );

    /**
     * Delete Customerprice by ID
     * @param string $customerpriceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerpriceId);

     /**
     * get lowest Customerprice
     * @param string $productId
     * @param string $customerId
     * @param string $websiteId
     * @return string on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPrice($productId, $customerId, $websiteId);
}

