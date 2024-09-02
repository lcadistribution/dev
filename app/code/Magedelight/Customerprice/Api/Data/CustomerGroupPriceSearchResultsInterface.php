<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerGroupPriceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomerGroupPrice list.
     * @return \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface[]
     */
    public function getItems();

    /**
     * Set group_id list.
     * @param \Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

