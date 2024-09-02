<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Model\Calculation;

use Magedelight\Customerprice\Model\Calculation\CalculatorFactory;
use Magedelight\Customerprice\Model\Calculation\Calculator\CalculatorInterface;
use Magedelight\Customerprice\Helper\Data as Helper;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class CalculationService acts as wrapper around actual CalculatorInterface so logic valid for all calculations like
 * min order amount is only done once.
 *
 * @package Magedelight\Customerprice\Model\Calculation
 */
class CalculationService implements CalculatorInterface
{
    /**
     * @var CalculatorFactory
     */
    protected $factory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * CalculationService constructor.
     * @param CalculatorFactory $factory
     * @param Helper $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CalculatorFactory $factory,
        Helper $helper,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($price, $product)
    {
        try {
            $calculatorFactory = $this->factory->get();
            return $calculatorFactory->calculate($price, $product);
        } catch (ConfigurationMismatchException $e) {
            $this->logger->error($e);
            return 0.00;
        }
    }
}
