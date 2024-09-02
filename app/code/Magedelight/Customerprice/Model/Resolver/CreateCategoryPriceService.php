<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterfaceFactory;
use Magedelight\Customerprice\Api\CustomerpriceCategoryRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateCategoryPriceService
{
    
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CategorypriceRepositoryInterface
     */
    private $categorypriceRepository;

    /**
     * @var CustomerpriceCategoryInterfaceFactory
     */
    private $CustomerpriceCategoryInterfaceFactory;

    /**
     *
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerpriceCategoryRepositoryInterface $customerpriceRepository
     * @param CustomerpriceInterfaceFactory $CustomerpriceCategoryInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CustomerpriceCategoryRepositoryInterface $categorypriceRepository,
        CustomerpriceCategoryInterfaceFactory $CustomerpriceCategoryInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->categorypriceRepository = $categorypriceRepository;
        $this->CustomerpriceCategoryInterfaceFactory = $CustomerpriceCategoryInterfaceFactory;
    }

    /**
     * Creates new store
     * @param array $data
     * @return CustomerpriceInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): CustomerpriceCategoryInterface
    {
        try {
            $categoryprice = $this->createCategoryPrice($data);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $categoryprice;
    }

    /**
     * Creates store
     *
     * @param array $data
     * @return CustomerpriceCategoryInterface
     * @throws LocalizedException
     */
    private function createCategoryPrice(array $data): CustomerpriceCategoryInterface
    {
        /** @var CustomerpriceCategoryInterface $categorypriceDataObject */
        $categorypriceDataObject = $this->CustomerpriceCategoryInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $categorypriceDataObject,
            $data,
            CustomerpriceCategoryInterface::class
        );
        
        $this->categorypriceRepository->save($categorypriceDataObject);

        return $categorypriceDataObject;
    }
}
