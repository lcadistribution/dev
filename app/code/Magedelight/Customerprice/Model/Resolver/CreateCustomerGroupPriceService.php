<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterfaceFactory;
use Magedelight\Customerprice\Api\CustomerGroupPriceRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateCustomerGroupPriceService
{
    
     /**
      * @var DataObjectHelper
      */
    private $dataObjectHelper;

    /**
     * @var CustomerGroupPriceRepositoryInterface
     */
    private $customerGroupPriceRepository;

    /**
     * @var customerGroupPriceInterfaceFactory
     */
    private $customerGroupPriceInterfaceFactory;

    /**
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerGroupPriceRepositoryInterface $customerGroupPriceRepository
     * @param CustomerGroupPriceInterfaceFactory $customerGroupPriceInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerGroupPriceRepositoryInterface $customerGroupPriceRepository,
        CustomerGroupPriceInterfaceFactory $customerGroupPriceInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerGroupPriceRepository = $customerGroupPriceRepository;
        $this->customerGroupPriceInterfaceFactory = $customerGroupPriceInterfaceFactory;
    }

    /**
     * Creates new store
     * @param array $data
     * @return CustomerGroupPriceInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerGroupPriceInterface
    {
        try {
            $customerGroupPrice = $this->createCustomerGroupPrice($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $customerGroupPrice;
    }

    /**
     * Creates store
     *
     * @param array $data
     * @return CustomerGroupPriceInterface
     * @throws LocalizedException
     */
    private function createCustomerGroupPrice(array $data): CustomerGroupPriceInterface
    {
        /** @var StorelocatorInterface $storeDataObject */
        $customerGroupPriceDataObject = $this->customerGroupPriceInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerGroupPriceDataObject,
            $data,
            CustomerGroupPriceInterface::class
        );

        $this->customerGroupPriceRepository->save($customerGroupPriceDataObject);

        return $customerGroupPriceDataObject;
    }
}
