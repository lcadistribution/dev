<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Customerprice\Api\CustomerpriceDiscountRepositoryInterface;
use Magedelight\Customerprice\Api\Data\CustomerpriceDiscountInterface;

class DeleteCustomerdiscount implements ResolverInterface
{
    /**
     * @var CustomerpriceDiscountRepositoryInterface
     */
    private $customerdiscountRepository;
    
    /**
     * @var customerdiscount
     */
    private $customerdiscount;

    
     /**
      *
      * @param CustomerpriceDiscountRepositoryInterface $customerdiscountRepository
      * @param CustomerpriceDiscountInterface $customerdiscount
      */
    public function __construct(
        CustomerpriceDiscountRepositoryInterface $customerdiscountRepository,
        CustomerpriceDiscountInterface $customerdiscount
    ) {
        $this->customerdiscountRepository = $customerdiscountRepository;
        $this->customerdiscount = $customerdiscount;
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
        if (!isset($args['customerpricediscount_id'])) {
            throw new GraphQlInputException(__('Specify the "customerpricediscount_id" value.'));
        }

        $id = $this->customerdiscount->load($args['customerpricediscount_id']);
        ;
        if (empty($id->getCustomerpricediscountId())) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a customer discount with id : %1', $args['customerpricediscount_id'])
            );
        }

        return ['result' => $this->customerdiscountRepository->deleteById($args['customerpricediscount_id'])];
    }
}
