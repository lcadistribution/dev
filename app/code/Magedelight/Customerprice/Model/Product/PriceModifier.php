<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;

class PriceModifier implements PriceModifierInterface
{
    /**
     * @var CalculatorInterface
     */
    protected $calculatorInterface;

    /**
     *
     * @param CalculatorInterface $calculatorInterface
     */
    public function __construct(CalculatorInterface $calculatorInterface)
    {
        $this->calculatorInterface = $calculatorInterface;
    }

    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     */
    public function modifyPrice($price, Product $product)
    {
        if ($price !== null) {
            $resultPrice = $this->calculatorInterface->calculate($price, $product);
            if ($resultPrice !== null) {
                $price = $resultPrice;
            }
        }
        return $price;
    }
}
