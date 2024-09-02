<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerpriceSpecialpriceRepositoryInterface
{

    /**
     * Save CustomerpriceSpecialprice
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
    );

    /**
     * Retrieve CustomerpriceSpecialprice
     * @param string $customerpricespecialpriceId
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customerpricespecialpriceId);

    /**
     * Retrieve CustomerpriceSpecialprice matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerpriceSpecialprice
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface $customerpriceSpecialprice
    );

    /**
     * Delete CustomerpriceSpecialprice by ID
     * @param string $customerpricespecialpriceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerpricespecialpriceId);
}

