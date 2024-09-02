<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Calculation\Calculator;

use Magedelight\Customerprice\Helper\Data as Helper;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;

abstract class AbstractCalculator implements CalculatorInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * AbstractCalculation constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }
}
