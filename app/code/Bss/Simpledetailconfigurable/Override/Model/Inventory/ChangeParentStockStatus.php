<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Override\Model\Inventory;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class ChangeParentStockStatus extends \Magento\ConfigurableProduct\Model\Inventory\ChangeParentStockStatus
{
    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $criteriaInterfaceFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $configSDCP;

    /**
     * @param Configurable $configurableType
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        Configurable                                      $configurableType,
        StockItemCriteriaInterfaceFactory                 $criteriaInterfaceFactory,
        StockItemRepositoryInterface                      $stockItemRepository,
        StockConfigurationInterface                       $stockConfiguration,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $configSDCP
    ) {
        parent::__construct($configurableType, $criteriaInterfaceFactory, $stockItemRepository, $stockConfiguration);
        $this->configurableType = $configurableType;
        $this->criteriaInterfaceFactory = $criteriaInterfaceFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->configSDCP = $configSDCP;
    }

    /**
     * Update stock status of configurable products based on children products stock status
     *
     * @param array $childrenIds
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(array $childrenIds): void
    {
        if ($this->configSDCP->isModuleEnable()) {
            $parentIds = $this->configurableType->getParentIdsByChild($childrenIds);
            foreach (array_unique($parentIds) as $productId) {
                $this->processStockForParent((int)$productId);
            }
        } else {
            parent::execute($childrenIds);
        }
    }

    /**
     * Process stock for parent
     *
     * @param int $productId
     * @return void
     */
    private function processStockForParent(int $productId): void
    {
        $criteria = $this->criteriaInterfaceFactory->create();
        $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());

        $criteria->setProductsFilter($productId);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();
        if (empty($allItems)) {
            return;
        }
        $parentStockItem = array_shift($allItems);

        $childrenIds = $this->configurableType->getChildrenIds($productId, false);
        $criteria->setProductsFilter($childrenIds);
        $stockItemCollection = $this->stockItemRepository->getList($criteria);
        $allItems = $stockItemCollection->getItems();

        $childrenIsInStock = false;

        foreach ($allItems as $childItem) {
            if ($childItem->getIsInStock() === true) {
                $childrenIsInStock = true;
                break;
            }
        }

        /* Always update stock parent product */
        $parentStockItem->setIsInStock($childrenIsInStock);
        $parentStockItem->setStockStatusChangedAuto(1);
        $parentStockItem->setStockStatusChangedAutomaticallyFlag(true);
        $this->stockItemRepository->save($parentStockItem);
    }
}
