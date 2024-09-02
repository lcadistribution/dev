<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerpriceCategoryRepositoryInterface
{

    /**
     * Save CustomerpriceCategory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
    );

    /**
     * Retrieve CustomerpriceCategory
     * @param string $customerpricecategoryId
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customerpricecategoryId);

    /**
     * Retrieve CustomerpriceCategory matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceCategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerpriceCategory
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategory
    );

    /**
     * Delete CustomerpriceCategory by ID
     * @param string $customerpricecategoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerpricecategoryId);
}

