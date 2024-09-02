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

class ValidatePostCode extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionColFactory;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ValidatorInterface
     */
    protected $postCodeValidator;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Directory\Model\RegionFactory $regionColFactory
     * @param \Magento\Directory\Model\Country\Postcode\ValidatorInterface $postCodeValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\RegionFactory $regionColFactory,
        \Magento\Directory\Model\Country\Postcode\ValidatorInterface $postCodeValidator
    ) {
        $this->regionColFactory = $regionColFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->postCodeValidator = $postCodeValidator;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return array
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
        $countryId = $this->getRequest()->getParam("country");
        $postalCode = $this->getRequest()->getParam("postcode");
        $result = $this->resultJsonFactory->create();
        try {
            $success = (bool) $this->postCodeValidator->validate($postalCode, $countryId);
            return $result->setData(["success" => $success]);
        } catch (\Throwable $th) {
            return $result->setData(["success" => false]);
        }
    }
}
