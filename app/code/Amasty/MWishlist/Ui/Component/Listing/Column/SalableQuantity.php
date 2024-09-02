<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Ui\Component\Listing\Column;

use Amasty\MWishlist\Model\Inventory\IsQtyProductType;
use Amasty\MWishlist\Model\ResourceModel\Inventory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SalableQuantity extends Column
{
    /**
     * @var IsQtyProductType
     */
    private $isQtyProductType;

    /**
     * @var Inventory
     */
    private $inventory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IsQtyProductType $isQtyProductType,
        Inventory $inventory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isQtyProductType = $isQtyProductType;
        $this->inventory = $inventory;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                $row['salable_quantity'] = $this->isQtyProductType->execute($row['type_id']) === true
                    ? $this->inventory->getStockData($row['sku'])
                    : [];
            }
        }
        unset($row);

        return $dataSource;
    }
}
