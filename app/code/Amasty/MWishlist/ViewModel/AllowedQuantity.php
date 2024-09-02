<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\ViewModel;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AllowedQuantity implements ArgumentInterface
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ItemInterface
     */
    private $item;

    public function __construct(StockRegistry $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param ItemInterface $item
     * @return self
     */
    public function setItem(ItemInterface $item): self
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return array
     */
    public function getMinMaxQty(): array
    {
        $product = $this->getItem()->getProduct();
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $params = [];

        $params['minAllowed'] = (float)$stockItem->getMinSaleQty();
        if ($stockItem->getMaxSaleQty()) {
            $params['maxAllowed'] = (float)$stockItem->getMaxSaleQty();
        } else {
            $params['maxAllowed'] = (float)StockDataFilter::MAX_QTY_VALUE;
        }

        return $params;
    }
}
