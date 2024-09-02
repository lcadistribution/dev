<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AttrBaseSplitcart\Controller\Cartover;

class CalculateTax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->taxCalculation = $taxCalculation;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return array
     */
    public function execute()
    {
        $countryId = $this->getRequest()->getParam("country");
        $regionId = $this->getRequest()->getParam("region");
        $total = $this->getRequest()->getParam("total");
        $postalCode = $this->getRequest()->getParam("postcode");
        $taxClassId = $this->getRequest()->getParam("taxclassid");
        $result = $this->resultJsonFactory->create();
            
        // Tax Calculation
        $defaultCustomerTaxClassId = $this->scopeConfig->getValue('tax/classes/default_customer_tax_class');

        $request = new \Magento\Framework\DataObject(
            [
                'country_id' => $countryId,
                'region_id' => $regionId,
                'postcode' => $postalCode,
                'customer_class_id' => $defaultCustomerTaxClassId,
                'product_class_id' => $taxClassId
            ]
        );

        // Calculate tax
        $taxInfo = $this->taxCalculation->getResource()->getRateInfo($request);

        $taxPercent = 0;

        // Classify different taxes
        if (count($taxInfo['process']) > 0) {
            $taxDetails = [];
            $i = 0;
            foreach ($taxInfo['process'][0]['rates'] as $key => $rate) {
                $taxPercent += $rate['percent'];
            }
        }

        $finalTaxTotal = (($taxPercent * $total) / 100);

        try {
            $success = true;
            return $result->setData(["success" => round($finalTaxTotal, 2)]);
        } catch (\Throwable $th) {
            return $result->setData(["success" => 0.00]);
        }
    }
}
