<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterfaceFactory;
use Magedelight\Customerprice\Api\CustomerpriceDiscountRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateCustomerDiscountService
{
    
     /**
      * @var DataObjectHelper
      */
    private $dataObjectHelper;

    /**
     * @var CustomerdiscountRepositoryInterface
     */
    private $customerdiscountRepository;

    /**
     * @var customerdiscountInterfaceFactory
     */
    private $customerdiscountInterfaceFactory;

    /**
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerdiscountRepositoryInterface $customerdiscountRepository
     * @param CustomerdiscountInterfaceFactory $customerdiscountInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerpriceDiscountRepositoryInterface $customerdiscountRepository,
        CustomerpriceDiscountInterfaceFactory $customerdiscountInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerdiscountRepository = $customerdiscountRepository;
        $this->customerdiscountInterfaceFactory = $customerdiscountInterfaceFactory;
    }

    /**
     * Creates new store
     * @param array $data
     * @return CustomerpriceInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerpriceDiscountInterface
    {
        try {
            $customerdiscount = $this->createCustomerDiscount($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $customerdiscount;
    }

    /**
     * Creates  customer discount
     *
     * @param array $data
     * @return CustomerpriceDiscountInterface
     * @throws LocalizedException
     */
    private function createCustomerDiscount(array $data): CustomerpriceDiscountInterface
    {
        /** @var StorelocatorInterface $storeDataObject */
        $customerdiscountDataObject = $this->customerdiscountInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerdiscountDataObject,
            $data,
            CustomerpriceInterface::class
        );

        $this->customerdiscountRepository->save($customerdiscountDataObject);

        return $customerdiscountDataObject;
    }
}
