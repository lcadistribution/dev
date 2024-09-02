<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterfaceFactory;
use Magedelight\Customerprice\Api\CustomerpriceSpecialpriceRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateCustomerSpecialPriceService
{
    
     /**
      * @var DataObjectHelper
      */
    private $dataObjectHelper;

    /**
     * @var CustomerpriceSpecialpriceRepositoryInterface
     */
    private $customerSpecialPriceRepository;

    /**
     * @var customerSpecialPriceInterfaceFactory
     */
    private $customerSpecialPriceInterfaceFactory;

    /**
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerpriceSpecialpriceRepositoryInterface $customerSpecialPriceRepository
     * @param CustomerpriceSpecialpriceInterfaceFactory $customerSpecialPriceInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerpriceSpecialpriceRepositoryInterface $customerSpecialPriceRepository,
        CustomerpriceSpecialpriceInterfaceFactory $customerSpecialPriceInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSpecialPriceRepository = $customerSpecialPriceRepository;
        $this->customerSpecialPriceInterfaceFactory = $customerSpecialPriceInterfaceFactory;
    }

    /**
     * Creates new store
     * @param array $data
     * @return CustomerpriceSpecialpriceInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerpriceSpecialpriceInterface
    {
        try {
            $customerprice = $this->createCustomerSpecialPrice($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $customerprice;
    }

    /**
     * Creates store
     *
     * @param array $data
     * @return CustomerpriceSpecialpriceInterface
     * @throws LocalizedException
     */
    private function createCustomerSpecialPrice(array $data): CustomerpriceSpecialpriceInterface
    {

        /** @var StorelocatorInterface $storeDataObject */
        $customerpriceDataObject = $this->customerSpecialPriceInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerpriceDataObject,
            $data,
            CustomerpriceSpecialpriceInterface::class
        );

        $this->customerSpecialPriceRepository->save($customerpriceDataObject);

        return $customerpriceDataObject;
    }
}
