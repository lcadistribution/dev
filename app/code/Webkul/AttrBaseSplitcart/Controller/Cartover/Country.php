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

class Country extends \Magento\Framework\App\Action\Action
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
     * Constructor function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Directory\Model\RegionFactory $regionColFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\RegionFactory $regionColFactory
    ) {
        $this->regionColFactory = $regionColFactory;
        $this->resultJsonFactory = $resultJsonFactory;
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

        $result = $this->resultJsonFactory->create();
        $regions = $this->regionColFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                "country_id",
                $this->getRequest()->getParam("country")
            );
        $html = "";

        if (count($regions) > 0) {
            $html .=
                '<option selected="selected" value="">Please select a region, state or province.</option>';
            foreach ($regions as $state) {
                $html .=
                    '<option regionid='. $state->getId(). ' value="' .
                    $state->getCode() .
                    '">' .
                    $state->getName() .
                    ".</option>";
            }
        }
        return $result->setData(["success" => true, "value" => $html]);
    }
}
