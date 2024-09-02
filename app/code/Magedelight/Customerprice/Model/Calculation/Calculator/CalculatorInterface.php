<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Calculation\Calculator;

interface CalculatorInterface
{
    /**
     * Calculate
     *
     * @param decimal $price
     * @param $product object
     * @return float
     */
    public function calculate($price, $product);
}
