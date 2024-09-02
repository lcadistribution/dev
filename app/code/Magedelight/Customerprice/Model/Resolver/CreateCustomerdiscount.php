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
use Magedelight\Customerprice\Model\Resolver\CreateCustomerDiscountService;

class CreateCustomerdiscount implements ResolverInterface
{
     
    /**
     * @var createCustomerDiscount
     */
    protected $createCustomerDiscountService;

    /**
     *
     * @param createCustomerDiscount $createCustomerDiscount
     */
    public function __construct(
        CreateCustomerDiscountService $createCustomerDiscountService
    ) {
        $this->createCustomerDiscountService = $createCustomerDiscountService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customerdiscountData = $this->createCustomerDiscountService->execute($args['input']);

        return ['customer_discount' => [
            'customerpricediscount_id' => $customerdiscountData->getCustomerpricediscountId(),
            'customer_id' => $customerdiscountData->getCustomerId(),
            'value' => $customerdiscountData->getValue()]
        ];
    }
}
