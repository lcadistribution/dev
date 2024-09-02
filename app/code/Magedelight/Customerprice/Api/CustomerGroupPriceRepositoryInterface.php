<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerGroupPriceRepositoryInterface
{

    /**
     * Save CustomerGroupPrice
     * @param \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
     * @return \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
    );

    /**
     * Retrieve CustomerGroupPrice
     * @param string $customergrouppriceId
     * @return \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customergrouppriceId);

    /**
     * Retrieve CustomerGroupPrice matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Customerprice\Api\Data\CustomerGroupPriceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerGroupPrice
     * @param \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice
    );

    /**
     * Delete CustomerGroupPrice by ID
     * @param string $customergrouppriceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customergrouppriceId);
}

