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

namespace Speedinfo\Opensi\Controller\Index;

use \SoapServer;
use \Exception;

// CSRF management to avoid error "Maximum of redirects reached [10]" on POST requests
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

error_reporting(E_ERROR);
//error_reporting(E_ALL);
ini_set('zlib.output_compression', 0);

/**
 * Constants
 */
define('OSI_SUCCESS_CREATE', '1001');
define('OSI_SUCCESS_UPDATE', '1002');
define('OSI_SUCCESS_DELETE', '1003');
define('OSI_WARNING_DUPLICATE', '2001');
define('OSI_ERROR_DUPLICATE', '3001');
define('OSI_ERROR_NOT_FOUND', '3002');
define('OSI_ERROR_TAX', '3003');
define('OSI_ERROR_TAX_RULES_GROUP', '3004');
define('OSI_ERROR_SHIPMENT', '3005');

define('OSI_CUSTOMER_EXIST', 'The customer already exist on the store.');
define('OSI_INVALID_AUTH', 'Authentification failed, please check your credentials!');
define('OSI_INVALID_SHOP_CONFIGURATION', 'Error, this code website is not recognized. Please check that the store is properly configured!');
define('OSI_INVALID_REFERENCE', 'Error, the reference was not found on the store.');
define('OSI_INVALID_ORDER', 'Error, this order does not exist on the store.');
define('OSI_INVALID_CUSTOMER', 'Error, this customer does not exist on the store.');
define('OSI_DUPLICATE_COMMENT', 'Warning, this comment is already set on the store.');
define('OSI_DUPLICATE_DOCUMENT', 'Warning, this document is already set on the store.');
define('OSI_DUPLICATE_SHIPPING_METHOD', 'Warning, this shipping method is already set on the store.');
define('OSI_INVALID_TAX', 'Warning, an error occurred while creating the tax.');
define('OSI_INVALID_TAX_RULES_GROUP', 'Warning, an error occurred while creating the tax rules group.');
define('OSI_INVALID_SHIPMENT', 'Error, this shipment id already set or the order cannot be shipped!');
define('OSI_SHIPMENT_NOT_FOUND', 'Error, no delivery note found for this shipment.');
define('OSI_SHIPMENT_CREATION_ERROR', 'Error, unable to create the shipment.');
define('OSI_TRACKING_EXIST', 'Error, the tracking number for this shipment already exist.');


class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
	protected $_componentRegistrar;
	protected $_objectManager;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Component\ComponentRegistrar $componentRegistrar
	) {
		$this->_componentRegistrar = $componentRegistrar;

		return parent::__construct($context);
	}

  /**
   * CSRF management to avoid error "Maximum of redirects reached [10]" on POST requests
   */
  public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
  {
    return null;
  }

  public function validateForCsrf(RequestInterface $request): ?bool
  {
    return true;
  }

	/**
	 * Execute
	 */
	public function execute()
	{
		try {

			ini_set('soap.wsdl_cache_enabled', 0);
			ini_set('default_socket_timeout', 180);

			$server = new SoapServer(
				$this->_componentRegistrar->getPath(\Magento\Framework\Component\ComponentRegistrar::MODULE, 'Speedinfo_Opensi').'/Webservices/opensi.wsdl',
				array(
					'soap_version' => SOAP_1_2,
					'encoding' => 'utf-8',
					'classmap' => array('Header' => 'Speedinfo\Opensi\Webservices\Classes\Header')
				)
			);

			$server->setclass('Speedinfo\Opensi\Webservices\Classes\OpensiWS', $server, \Magento\Framework\App\ObjectManager::getInstance());

			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				ob_start();
				$server->handle();
				$response = preg_replace("/[\\x00-\\x12\\x14-\\x1F\\x7F]/", "", ob_get_contents());
				ob_end_clean();

				$this->getResponse()->setHeader('Content-Length', strlen($response));
				$this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');

				$this->_view->loadLayout(false);
				$this->_view->renderLayout();

				$this->getResponse()->setBody($response);
			}

		} catch (Exception $e) {

			header('Content-type: text/xml');

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://www.opensi.fr">';
			echo '<env:Body>';
			echo '<env:Fault>';
			echo '<env:Code>';
			echo '<env:Value>ns1:server</env:Value>';
			echo '</env:Code>';
			echo '<env:Reason>';
			echo '<env:Text>'.$e->getMessage().'</env:Text>';
			echo '</env:Reason>';
			echo '<env:Detail>';
			echo '<ns1:fault/>';
			echo '</env:Detail>';
			echo '</env:Fault>';
			echo '</env:Body>';
			echo '</env:Envelope>';

		}
	}
}
