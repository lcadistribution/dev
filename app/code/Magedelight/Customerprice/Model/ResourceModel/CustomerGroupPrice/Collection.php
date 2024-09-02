<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\Customerprice\Model\ResourceModel\CustomerGroupPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'customergroupprice_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Magedelight\Customerprice\Model\CustomerGroupPrice::class,
            \Magedelight\Customerprice\Model\ResourceModel\CustomerGroupPrice::class
        );
    }
}

