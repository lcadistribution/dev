<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Calculation;

use Magedelight\Customerprice\Helper\Data as Helper;
use Magedelight\Customerprice\Model\Calculation\Calculator\GlobalDiscountCalculator;

class CalculatorFactory
{
    /**
     * @var Helper
     */
    protected $helper;
    
    /**
     *
     * @var GlobalDiscountCalculator
     */
    protected $globalDiscountCalculator;

    /**
     * CalculationFactory constructor.
     *
     * @param Helper $helper
     * @param GlobalDiscountCalculator $globalDiscountCalculator
     */
    public function __construct(
        Helper $helper,
        GlobalDiscountCalculator $globalDiscountCalculator
    ) {
        $this->helper = $helper;
        $this->globalDiscountCalculator = $globalDiscountCalculator;
    }

    /**
     * @return Calculator\CalculatorInterface
     */
    public function get()
    {
        return $this->globalDiscountCalculator;
    }
}
