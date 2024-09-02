<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types = 1);

namespace Magedelight\Customerprice\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Customerprice\Model\Resolver\CreateCustomerGroupPriceService;

class CreateCustomerGroupPrice implements ResolverInterface
{
    /**
     * @var creatCustomerGroupPriceService
     */
    protected $creatCustomerGroupPriceService;

    /**
     *
     * @param CreateCustomerGroupPriceService $creatCustomerGroupPriceService
     */
    public function __construct(
        CreateCustomerGroupPriceService $creatCustomerGroupPriceService
    ) {
        $this->creatCustomerGroupPriceService = $creatCustomerGroupPriceService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customerpriceData = $this->creatCustomerGroupPriceService->execute($args['input']);

        return ['customer_groupprice' => [
            'customergroupprice_id' => $customerpriceData->getCustomergrouppriceId(),
            'group_id' => $customerpriceData->getGroupId(),
            'value' => $customerpriceData->getValue(),
            'price_type' => $customerpriceData->getPriceType()]
        ];
    }
}
