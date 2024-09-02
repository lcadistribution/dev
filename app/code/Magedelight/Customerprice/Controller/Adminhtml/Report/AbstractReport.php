<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Report;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Filter\FilterInput;

abstract class AbstractReport extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var BackendHelper
     */
    private $backendHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param BackendHelper|null $backendHelperData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        BackendHelper $backendHelperData = null
    ) {
        parent::__construct($context);
        $this->_dateFilter = $dateFilter;
        $this->backendHelper = $backendHelperData ?: $this->_objectManager->get(BackendHelper::class);
        $this->_fileFactory = $fileFactory;
    }

    /**
     * Admin session model
     *
     * @var null|\Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession = null;

    /**
     * Retrieve admin session model
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _getSession()
    {
        if ($this->_adminSession === null) {
            $this->_adminSession = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        }
        return $this->_adminSession;
    }

    /**
     * Report action init operations
     *
     * @param array|\Magento\Framework\DataObject $blocks
     * @return $this
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = [$blocks];
        }

        $params = $this->initFilterData();

        foreach ($blocks as $block) {
            if ($block) {
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    /**
     * Init filter data
     *
     * @return \Magento\Framework\DataObject
     */
    private function initFilterData(): \Magento\Framework\DataObject
    {
        $requestData = $this->backendHelper->prepareFilterString(
            $this->getRequest()->getParam('filter', ''),
        );

        $filterRules = ['from' => $this->_dateFilter, 'to' => $this->_dateFilter];
        $inputFilter = new \Zend_Filter_Input($filterRules, [], $requestData);

        $requestData = $inputFilter->getUnescaped();
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $requestData['group'] = $this->getRequest()->getParam('group');
        $requestData['website'] = $this->getRequest()->getParam('website');

        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        return $params;
    }
}
