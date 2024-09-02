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
use Magedelight\Customerprice\Model\Resolver\CreateCategoryPriceService;

class CreateCategoryprice implements ResolverInterface
{
    
    /**
     * @var creatCategoryPriceService
     */
    protected $creatCategoryPriceService;

    /**
     *
     * @param CreateCategoryPriceService $creatCategoryPriceService
     */
    public function __construct(
        CreateCategoryPriceService $creatCategoryPriceService
    ) {
        $this->creatCategoryPriceService = $creatCategoryPriceService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customerpriceData = $this->creatCategoryPriceService->execute($args['input']);

        if (!is_numeric($customerpriceData->getDiscount())) {
            throw new GraphQlInputException(__('Please enter value of status from 1 or 2. 1 is for Enabled and 2 is for Disabled.'));
        }


        return ['category_price' => [
            'customerpricecategory_id' => $customerpriceData->getCustomerpricecategoryId(),
            'customer_id' => $customerpriceData->getCustomerId(),
            'customer_name' => $customerpriceData->getCustomerName(),
            'customer_email' => $customerpriceData->getCustomerEmail(),
            'category_id' => $customerpriceData->getCategoryId(),
            'category_name' => $customerpriceData->getCategoryName(),
            'discount' => $customerpriceData->getDiscount(),
            'expiry_date' => $customerpriceData->getExpiryDate()]
        ];
    }
}
