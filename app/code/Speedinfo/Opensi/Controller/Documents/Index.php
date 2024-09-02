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

namespace Speedinfo\Opensi\Controller\Documents;

class Index extends \Magento\Framework\App\Action\Action
{
  protected $_request;
  protected $_documentsFactory;
  protected $_resultForwardFactory;

	/**
	 * Constructor
	 */
	public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\App\Request\Http $request,
    \Speedinfo\Opensi\Model\DocumentsFactory $documentsFactory,
    \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
  ) {
		parent::__construct($context);

    $this->_request = $request;
    $this->_documentsFactory = $documentsFactory;
    $this->_resultForwardFactory = $resultForwardFactory;
	}

	/**
	 * Execute
	 */
	public function execute()
	{
		$documentId = $this->_request->getParam('id');
    $documentKey = $this->getRequest()->getParam('key');
    $documentCollection = $this->getDocumentFromIdKey($documentId, $documentKey);

		if (empty($documentCollection->getData())) {

			/**
			 * Redirect to 404
			 */
       $resultForward = $this->_resultForwardFactory->create();
       $resultForward->forward('noroute');

       return $resultForward;

		} else {

			/**
			 * Return document
			 */
			foreach ($documentCollection as $document)
			{
        return $this->downloadDocument($document->getDocumentNumber(), $document->getDocumentKey());
			}

		}
	}


  /**
	 * Retrieve document informations
	 *
	 * @param $documentId
	 * @param $documentKey
	 */
	private function getDocumentFromIdKey($documentId, $documentKey)
	{
    $resultPage = $this->_documentsFactory->create();
    $collection = $resultPage->getCollection();
    $collection
			->getSelect()
			->where('document_id = "'.$documentId.'" AND MD5(document_key) = "'.$documentKey.'"');

    return $collection;
	}


  /**
	 * Download document
	 *
	 * @param $documentNumber
	 * @param $documentKey
	 */
	private function downloadDocument($documentNumber, $documentKey)
	{
		if(is_callable('curl_init')) {

			/**
			 * Download invoice via CURL
			 */
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $documentKey);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
			$output = curl_exec($c);

			if($output === false) {

				header('HTTP/1.1 500 Internal Server Error');

			} else {

				header("Content-type: application/pdf");
				header("Content-Length: ".strlen($output));
				header("Content-Disposition: attachment; filename=".$documentNumber.".pdf");
				echo $output;
				exit();

			}

			curl_close($c);

		} else {

			/**
			 * Download invoice via READFILE
			 */
			ob_start();
			$pdf_size = readfile($documentKey);
			$pdf_file = ob_get_contents();
			ob_end_clean();

			if($pdf_size != 0) {

				header('Content-type: application/pdf');
				header('Content-Disposition: attachement; filename='.$documentNumber.'.pdf');
				echo $pdf_file;
				exit();

			} else {

				header('HTTP/1.1 500 Internal Server Error');

			}
		}
	}
}
