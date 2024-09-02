<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */


declare(strict_types=1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magedelight\Customerprice\Api\CustomerpriceRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Customerprice implements ResolverInterface
{   
    /**
     * @var \Magedelight\Customerprice\Model\Resolver\DataProvider\Customerprice
     */
    private $customerpriceDataProvider;
   
    /**
     *
     * @param \Magedelight\Customerprice\Model\Resolver\DataProvider\Customerprice $customerpriceDataProvider
     */
    public function __construct(
        \Magedelight\Customerprice\Model\Resolver\DataProvider\Customerprice $customerpriceDataProvider
    ) {
        $this->customerpriceDataProvider = $customerpriceDataProvider;
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
        $customerpriceData = $this->customerpriceDataProvider->getcustomerPrice();
        return $customerpriceData;
    }
}
