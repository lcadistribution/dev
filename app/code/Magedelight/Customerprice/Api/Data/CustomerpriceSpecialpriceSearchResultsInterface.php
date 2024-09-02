<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Api\Data;

interface CustomerpriceSpecialpriceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomerpriceSpecialprice list.
     * @return \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceSpecialpriceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

