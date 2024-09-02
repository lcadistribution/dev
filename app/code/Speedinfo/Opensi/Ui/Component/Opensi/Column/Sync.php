<?php
/**
 * 2003-2017 OpenSi Connect
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Speedinfo
 * @package     Speedinfo_Opensi
 * @copyright   Copyright (c) 2017 Speedinfo SARL (http://www.speedinfo.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Speedinfo\Opensi\Ui\Component\Opensi\Column;

use \Magento\Ui\Component\Listing\Columns\Column;

class Sync extends Column
{
  protected $_orderRepository;
  protected $_searchCriteria;

  public function __construct(
    \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
    \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
    \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
    array $components = [],
    array $data = []
  ) {
    parent::__construct($context, $uiComponentFactory, $components, $data);

    $this->_orderRepository = $orderRepository;
    $this->_searchCriteria  = $criteria;
  }

  public function prepareDataSource(array $dataSource)
  {
    if (isset($dataSource['data']['items']))
    {
      foreach ($dataSource['data']['items'] as & $item)
      {
        $order  = $this->_orderRepository->get($item["entity_id"]);
        $isSync = $order->getData("opensi_sync");

        switch ($isSync)
        {
          case 1;
            $opensi_sync = '<center><span class="icon-valid"></span></center>';
            break;
          default:
            $opensi_sync = '';
            break;
        }

        // $this->getData('name') returns the name of the column
        $item[$this->getData('name')] = $opensi_sync;
      }
    }

    return $dataSource;
  }
}
