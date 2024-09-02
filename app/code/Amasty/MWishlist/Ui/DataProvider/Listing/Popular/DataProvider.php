<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Multiple Wishlist for Magento 2
 */

namespace Amasty\MWishlist\Ui\DataProvider\Listing\Popular;

use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection\Grid as Collection;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Item\Collection\GridFactory as CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
        }

        return $this->collection;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $this->getCollection()->getSize();
        $arrItems['items'] = [];

        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($this->getCollection() as $item) {
            $arrItems['items'][] = $item->toArray();
        }

        return $arrItems;
    }
}
