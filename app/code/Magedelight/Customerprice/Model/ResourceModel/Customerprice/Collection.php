<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model\ResourceModel\Customerprice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'customerprice_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Magedelight\Customerprice\Model\Customerprice::class,
            \Magedelight\Customerprice\Model\ResourceModel\Customerprice::class
        );
    }

    /**
     * Add filter for a specific date range and include records with empty dates
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function addDateRangeFilter($fromDate, $toDate)
    {
        $this->getSelect()
            ->where('expiry_date >= ?', $fromDate)
            ->where('expiry_date <= ?', $toDate)
            ->orWhere('expiry_date IS NULL');

        return $this;
    }
}

