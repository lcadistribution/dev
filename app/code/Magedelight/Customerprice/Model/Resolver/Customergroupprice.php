<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */


declare(strict_types=1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\CustomerGroupPriceRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Customergroupprice implements ResolverInterface
{   
    /**
     * @var \Magedelight\Customerprice\Model\Resolver\DataProvider\Customergrouprice
     */
    private $customerGrouppriceDataProvider;
   
    /**
     *
     * @param \Magedelight\Customerprice\Model\Resolver\DataProvider\Customergrouprice $customerGrouppriceDataProvider
     */
    public function __construct(
        \Magedelight\Customerprice\Model\Resolver\DataProvider\Customergroupprice $customerGrouppriceDataProvider
    ) {
        $this->customerGrouppriceDataProvider = $customerGrouppriceDataProvider;
    }
 
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerGrouppriceData = $this->customerGrouppriceDataProvider->getCustomerGroupPrice();
        return $customerGrouppriceData;
    }
}
