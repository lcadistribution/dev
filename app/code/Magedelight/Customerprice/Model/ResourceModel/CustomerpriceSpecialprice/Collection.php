<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'customerspecialprice_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Magedelight\Customerprice\Model\CustomerpriceSpecialprice::class,
            \Magedelight\Customerprice\Model\ResourceModel\CustomerpriceSpecialprice::class
        );
    }
}

