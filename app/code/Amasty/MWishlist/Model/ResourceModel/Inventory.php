<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Model\ResourceModel;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager as ModuleManager;

class Inventory
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var array|null
     */
    private $stockNames;

    /**
     * @var array
     */
    private $qty = [];

    /**
     * @var array
     */
    private $sourceCodes = [];

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ResourceConnection $resource,
        StockRegistryInterface $stockRegistry,
        ModuleManager $moduleManager
    ) {
        $this->resource = $resource;
        $this->stockRegistry = $stockRegistry;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $sku
     * @throws NoSuchEntityException
     */
    public function getStockData(string $sku)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $isManageStock = $stockItem->getManageStock();

        $stockInfo = [];

        foreach ($this->getStockIdsBySku($sku) as $stockId) {
            $stockId = (int) $stockId;
            $stockInfo[] = [
                'stock_name' => $this->getStockName($stockId),
                'qty' => $isManageStock ? $this->getQty($sku, $stockId) : null,
                'manage_stock' => $isManageStock
            ];
        }

        return $stockInfo;
    }

    /**
     * @param $productSku
     * @param $stockId
     *
     * @return float|int
     *
     * @throws NoSuchEntityException
     */
    public function getQty($productSku, $stockId)
    {
        if ($this->isMsiEnabled()) {
            $qty = $this->getMsiQty($productSku, $stockId);
        } else {
            $qty = $this->stockRegistry->getStockItemBySku($productSku)->getQty();
        }

        return $qty;
    }

    /**
     * For MSI. Need to get negative qty.
     * Emulate \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     *
     * @param string $productSku
     * @param string $stockId
     *
     * @return float|int
     *
     * @throws NoSuchEntityException
     */
    public function getMsiQty($productSku, $stockId)
    {
        if (!isset($this->qty[$stockId][$productSku])) {
            $this->qty[$stockId][$productSku] = $this->getItemQty($productSku, $stockId)
                + $this->getReservationQty($productSku, $stockId);
        }

        return $this->qty[$stockId][$productSku];
    }

    /**
     * @param string $productSku
     * @param string $stockId
     *
     * @return float|int
     */
    private function getItemQty($productSku, $stockId)
    {
        $select = $this->resource->getConnection()->select()
            ->from($this->resource->getTableName('inventory_source_item'), ['SUM(quantity)'])
            ->where('source_code IN (?)', $this->getSourceCodes($stockId))
            ->where('sku = ?', $productSku)
            ->group('sku');

        return $this->resource->getConnection()->fetchOne($select);
    }

    /**
     * For MSI.
     *
     * @param string $stockId
     *
     * @return array
     */
    public function getSourceCodes($stockId)
    {
        if (!isset($this->sourceCodes[$stockId])) {
            $select = $this->resource->getConnection()->select()
                ->from($this->resource->getTableName('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $stockId);

            $this->sourceCodes[$stockId] = $this->resource->getConnection()->fetchCol($select);
        }

        return $this->sourceCodes[$stockId];
    }

    /**
     * For MSI.
     *
     * @param string $sku
     * @param int $stockId
     *
     * @return int|string
     */
    private function getReservationQty($sku, $stockId)
    {
        $select = $this->resource->getConnection()->select()
            ->from($this->resource->getTableName('inventory_reservation'), ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku)
            ->where('stock_id = ?', $stockId)
            ->limit(1);

        $reservationQty = $this->resource->getConnection()->fetchOne($select);
        if ($reservationQty === false) {
            $reservationQty = 0;
        }

        return $reservationQty;
    }

    /**
     * @param string $sku
     * @return array
     */
    private function getStockIdsBySku(string $sku): array
    {
        if ($this->isMsiEnabled()) {
            $select = $this->resource->getConnection()->select()->from(
                ['source_item' => $this->resource->getTableName('inventory_source_item')],
                []
            )->join(
                ['link' => $this->resource->getTableName('inventory_source_stock_link')],
                'source_item.source_code = link.source_code',
                'stock_id'
            )->where('sku = ?', $sku);

            $stockIds = array_unique($this->resource->getConnection()->fetchCol($select));
        } else {
            $stockIds = [1];
        }

        return $stockIds;
    }

    /**
     * @return array
     */
    private function getStockNames(): array
    {
        if ($this->stockNames === null) {
            $select = $this->resource->getConnection()->select()->from(
                $this->resource->getTableName('inventory_stock'),
                ['stock_id', 'name']
            );
            $this->stockNames = $this->resource->getConnection()->fetchPairs($select);
        }

        return $this->stockNames;
    }

    /**
     * @param int $stockId
     * @return string
     */
    private function getStockName(int $stockId): string
    {
        if ($this->isMsiEnabled()) {
            $stockName = $this->getStockNames()[$stockId] ?? '';
        } else {
            $stockName = __('Default Stock')->render();
        }

        return $stockName;
    }

    /**
     * @return bool
     */
    private function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
