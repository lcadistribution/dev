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
use Magedelight\Customerprice\Model\Resolver\CreateCustomerSpecialPriceService;

class CreateCustomerSpecialPrice implements ResolverInterface
{
    /**
     * @var creatCustomerSpecialPriceService
     */
    protected $creatCustomerSpecialPriceService;

    /**
     *
     * @param CreateCustomerPriceService $creatCustomerSpecialPriceService
     */
    public function __construct(
        CreateCustomerSpecialPriceService $creatCustomerSpecialPriceService
    ) {
        $this->creatCustomerSpecialPriceService = $creatCustomerSpecialPriceService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customerpriceData = $this->creatCustomerSpecialPriceService->execute($args['input']);

        return ['customer_specialprice' => [
            'customerspecialprice_id' => $customerpriceData->getCustomerspecialpriceId(),
            'name' => $customerpriceData->getName(),
            'customer_email' => $customerpriceData->getEmail(),
            'actual_price' => $customerpriceData->getActualPrice(),
            'quantity' => $customerpriceData->getQuantity(),
            'special_price' => $customerpriceData->getSpecialPrice(),
            'pname' => $customerpriceData->getPname(),
            'pid' => $customerpriceData->getPid(),
            'approve' => $customerpriceData->getApprove(),
            'customerprice_id' => $customerpriceData->getCustomerpriceId(),
            'expiry_date' => $customerpriceData->getExpiryDate(),
            'created_at' => $customerpriceData->getCreatedAt()
            ]
        ];
    }
}
