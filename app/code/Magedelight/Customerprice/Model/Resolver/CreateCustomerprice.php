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
use Magedelight\Customerprice\Model\Resolver\CreateCustomerPriceService;

class CreateCustomerprice implements ResolverInterface
{
    /**
     * @var creatCustomerPriceService
     */
    protected $creatCustomerPriceService;

    /**
     *
     * @param CreateCustomerPriceService $creatCustomerPriceService
     */
    public function __construct(
        CreateCustomerPriceService $creatCustomerPriceService
    ) {
        $this->creatCustomerPriceService = $creatCustomerPriceService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customerpriceData = $this->creatCustomerPriceService->execute($args['input']);

        return ['customer_price' => [
            'customerprice_id' => $customerpriceData->getCustomerpriceId(),
            'customer_id' => $customerpriceData->getCustomerId(),
            'customer_name' => $customerpriceData->getCustomerName(),
            'customer_email' => $customerpriceData->getCustomerEmail(),
            'product_id' => $customerpriceData->getProductId(),
            'product_name' => $customerpriceData->getProductName(),
            'price' => $customerpriceData->getPrice(),
            'log_price' => $customerpriceData->getLogPrice(),
            'new_price' => $customerpriceData->getNewPrice(),
            'qty' => $customerpriceData->getQty(),
            'expiry_date' => $customerpriceData->getExpiryDate(),
            'website_id' => $customerpriceData->getWebsiteId()]
        ];
    }
}
