<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\Data\CustomerpriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceInterfaceFactory;
use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateCustomerPriceService
{
    
     /**
      * @var DataObjectHelper
      */
    private $dataObjectHelper;

    /**
     * @var CustomerpriceRepositoryInterface
     */
    private $customerpriceRepository;

    /**
     * @var customerpriceInterfaceFactory
     */
    private $customerpriceInterfaceFactory;

    /**
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerpriceRepositoryInterface $customerpriceRepository
     * @param CustomerpriceInterfaceFactory $customerpriceInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerpriceRepositoryInterface $customerpriceRepository,
        CustomerpriceInterfaceFactory $customerpriceInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerpriceRepository = $customerpriceRepository;
        $this->customerpriceInterfaceFactory = $customerpriceInterfaceFactory;
    }

    /**
     * Creates new store
     * @param array $data
     * @return CustomerpriceInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerpriceInterface
    {
        try {
            $customerprice = $this->createCustomerPrice($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $customerprice;
    }

    /**
     * Creates store
     *
     * @param array $data
     * @return CustomerpriceInterface
     * @throws LocalizedException
     */
    private function createCustomerPrice(array $data): CustomerpriceInterface
    {
        /** @var StorelocatorInterface $storeDataObject */
        $customerpriceDataObject = $this->customerpriceInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerpriceDataObject,
            $data,
            CustomerpriceInterface::class
        );

        $this->customerpriceRepository->save($customerpriceDataObject);

        return $customerpriceDataObject;
    }
}
