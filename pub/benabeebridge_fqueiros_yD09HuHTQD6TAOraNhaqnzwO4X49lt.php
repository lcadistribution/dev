<?php

/**
 * copyright Benabee 2022.
 */

namespace {

    ob_start();
    ini_set('display_errors', 1);
    ini_set('track_errors', 1);
    ini_set('html_errors', 1);
    //error_reporting((E_ALL | E_STRICT) & ~E_NOTICE);

    // Permissions
    const disable_grid_editing = false;                     //default value: false. If true, grid editing is disabled in Product Manager for Magento
    const disable_grid_mass_action = false;                 //default value: false. If true, copy and paste to multiple products in Product Manager for Magento
    const disable_grid_configuration = false;               //default value: false. If true, "Manage tabs and columns" menu is disabled in Product Manager for Magento
    const force_simplified_view_in_editors = false;         //default value: false. If true, simplified view mode is forced when editing product in Product Manager for Magento

    // Credentials (DO NOT EDIT)
    $username = 'fqueiros';
    $password = '5a301640a5c16491e1ff6b0fa18f842b691ce45b';
    $key = 'Tdx3T04DGVJlIMAhNNxZXd1zgt7X5Oc0KjgYpiGPme78GM6WMFwVzvM48ZpxZw8wnAIxXuUTn0yYoIYrW7ibrDB87YoLxImEyeKvxmR2rPJKjqdi876zu5lhcsD5PKpmh15thhaNNLMm';
    $encryptionKey = '94eDQ/Jbc6YFilGr7oWX7mGbO28B/ywgVkcOTz2Wwwg=';

    // $username = $_POST['username'];$password = $_POST['password'];$key = $_POST['key'];
    $databaseAPI = 'mysql';  //"pdo", "mysql", "mysqli"
    $write_connection = 0;
    $read_connection = 0;

    $profiling_time_start = microtime(true);

    // check if key has been generated
    if ($key == '') {
        exitOnFatalError('FATAL_ERROR_NO_KEY');
    }

    // Magento 2
    $bootstrapMagento2 = 0;
    try {
        if (file_exists('app/bootstrap.php')) {
            require 'app/bootstrap.php';

            if (class_exists('Magento\Framework\App\Bootstrap')) {
                $bootstrapMagento2 = Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
            }
        }
    } catch (\Exception $e) {
        echo 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
    }

    // Magento 1
    $magento1Router = 0;
    try {
        if (file_exists('app/Mage.php')) {
            require_once 'app/Mage.php';
            Mage::app();

            $magento1Router = new ProductManagerMagento1\Magento1Router();
            $magento1Router->execute($encryptionKey, $username, $password, $key);
        }
    } catch (\Exception $e) {
        echo 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
    }

    // Magento 2 again
    if (!$magento1Router && !$bootstrapMagento2) {
        try {
            if (file_exists('../app/bootstrap.php')) {
                require '../app/bootstrap.php';

                if (class_exists('Magento\Framework\App\Bootstrap')) {
                    $bootstrapMagento2 = Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
                }
            }
        } catch (\Exception $e) {
            echo 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
        }
    }

    if ($bootstrapMagento2) {
        $objectManager = $bootstrapMagento2->getObjectManager();
        $state = $objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManager');
        $productMediaConfig = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $giftMessageConfigProvider = $objectManager->get('\Magento\GiftMessage\Model\GiftMessageConfigProvider');
        $resourceConnection = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $stockHelper = $objectManager->get('\Magento\CatalogInventory\Api\StockConfigurationInterface');
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $backendUrl = $objectManager->get('\Magento\Backend\Model\UrlInterface');
        $productModel = $objectManager->get('Magento\Catalog\Model\Product');
        $categoryModel = $objectManager->get('Magento\Catalog\Model\Category');
        $productRepository = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        $registry = $objectManager->get('\Magento\Framework\Registry');
        $backendHelper = $objectManager->get('\Magento\Backend\Helper\Data');
        $productFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
        $indexerCollectionFactory = $objectManager->get('\Magento\Indexer\Model\Indexer\CollectionFactory');
        $productAttributeCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory');
        $categoryAttributeCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory');
        $customerGroupsCollection = $objectManager->get('\Magento\Customer\Model\ResourceModel\Group\Collection');
        $userModel = $objectManager->create('Magento\User\Model\User');
        $authSession = $objectManager->create('\Magento\Backend\Model\Auth\Session');
        $productCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $urlRewriteGenerator = $objectManager->get('\Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator');
        $urlRewrite = $objectManager->get('\Magento\UrlRewrite\Service\V1\Data\UrlRewrite');
        $urlPersist = $objectManager->get('\Magento\UrlRewrite\Model\UrlPersistInterface');
        $urlFinder = $objectManager->get('\Magento\UrlRewrite\Model\UrlFinderInterface');
        $productUrlPathGenerator = $objectManager->get('Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator');
        $cacheManager = $objectManager->get('\Magento\Framework\App\CacheInterface');
        $searchCriteriaBuilder = $objectManager->get('\Magento\Framework\Api\SearchCriteriaBuilder');

        $sourceItemsBySku = null;
        try {
            $sourceItemsBySku = $objectManager->get('\Magento\InventoryApi\Api\GetSourceItemsBySkuInterface');
        } catch (Throwable $e) {
            // Do nothing
            // echo 'Exception : ',  $e->getMessage(), "\n";
        }

        $sourceItemRepository = null;
        try {
            $sourceItemRepository = $objectManager->get('\Magento\InventoryApi\Api\SourceItemRepositoryInterface');
        } catch (Throwable $e) {
            // Do nothing
            // echo 'Exception : ',  $e->getMessage(), "\n";
        }

        $imageResize = null;
        if (class_exists('Magento\MediaStorage\Service\ImageResize')) {
            $imageResize = $objectManager->get('Magento\MediaStorage\Service\ImageResize');
        }

        $productManagerConfigHelper = new ProductManagerMagento2\ProductManagerConfigHelper(
            $storeManager,
            $productMediaConfig,
            $productMetadata,
            $giftMessageConfigProvider,
            $resourceConnection,
            $stockHelper,
            $backendUrl,
            $productAttributeCollectionFactory,
            $categoryAttributeCollectionFactory,
            $customerGroupsCollection,
            $indexerCollectionFactory
        );

        $productManagerDatabaseConnectionHelper = new ProductManagerMagento2\ProductManagerDatabaseConnectionHelper(
            $storeManager,
            $resourceConnection
        );

        if ($imageResize) {
            $productManagerImageHelper = new ProductManagerMagento2\ProductManagerImageHelper($storeManager, $productMediaConfig, $filesystem, $imageResize);
        } else {
            $productManagerImageHelper = new ProductManagerMagento2\ProductManagerImageHelper($storeManager, $productMediaConfig, $filesystem);
        }

        $productManagerReindexHelper = new ProductManagerMagento2\ProductManagerReindexHelper(
            $storeManager,
            $productModel,
            $productFactory,
            $productRepository,
            $indexerCollectionFactory,
            $productCollectionFactory,
            $urlRewriteGenerator,
            $urlRewrite,
            $urlPersist,
            $urlFinder,
            $productUrlPathGenerator,
            $cacheManager,
            $productMetadata,
            $searchCriteriaBuilder,
            $sourceItemsBySku,
            $sourceItemRepository
        );

        $openInMagentoHelper = new ProductManagerMagento2\OpenInMagentoHelper(
            $storeManager,
            $productModel,
            $categoryModel,
            $registry,
            $backendHelper
        );

        $router = new ProductManagerMagento2\Magento2Router(
            $productManagerConfigHelper,
            $productManagerDatabaseConnectionHelper,
            $productManagerImageHelper,
            $productManagerReindexHelper,
            $openInMagentoHelper,
            $userModel,
            $authSession
        );

        $router->execute($encryptionKey, $username, $password, $key);

        // profiling_time_start
        //header('profiling_time_start: ' . $profiling_time_start);

        // profiling_date_start
        $micro = sprintf("%06d", ($profiling_time_start - floor($profiling_time_start)) * 1000000);
        $d = new DateTime(date('Y-m-d H:i:s.' . $micro, intval(floor($profiling_time_start))));
        header('profiling_date_start: ' . $d->format("Y-m-d H:i:s.u"));

        // profiling_time_end
        //$profiling_time_end = microtime(true);
        //header('profiling_time_end: ' . $profiling_time_end);

        // profiling_date_end
        $profiling_time_end = microtime(true);
        $micro = sprintf("%06d", ($profiling_time_end - floor($profiling_time_end)) * 1000000);
        $d = new DateTime(date('Y-m-d H:i:s.' . $micro, intval(floor($profiling_time_end))));
        header('profiling_date_end: ' . $d->format("Y-m-d H:i:s.u"));

        // profiling_total_time_seconds
        $profiling_total_time = $profiling_time_end - $profiling_time_start;
        header('profiling_total_time_seconds: ' . $profiling_total_time);

        exit();
    }
}

/***************************/
/* UTILITY FUNCTIONS       */
/***************************/

namespace ProductManagerUtil {

    function exitOnFatalError($code, $reason = '')
    {
        $result = new \stdClass();
        //$result->result = null;

        if ($reason == '') {
            $result->error = $code;
        } else {
            $result->error = $code . ', ' . $reason;
        }

        echo json_encode($result);
        exit();
    }

    function writeHeaderAndCookie()
    {
        // change script name to index.php (Mage::getBaseUrl returns a wrong URL in BusinessKing_OutofStockSubscription extension)
        if (isset($_SERVER)) {
            $_SERVER['SCRIPT_FILENAME'] = str_replace($_SERVER['SCRIPT_NAME'], '/index.php', $_SERVER['SCRIPT_FILENAME']);
            $_SERVER['REQUEST_URI'] = '/index.php';
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['PHP_SELF'] = '/index.php';
        }

        //header
        header($_SERVER['SERVER_PROTOCOL'] . ' 202 Accepted');  //200 OK
        header('Cache-Control: no-store, private, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale=0', false);
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Expires: 0', false);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Pragma: no-cache');
        header('Content-type: application/json');
        setcookie('EXTERNAL_NO_CACHE', '1');
        setcookie('nocache', '1');
        setcookie('no-cache', '1');
        setcookie('NO_CACHE', '1');
        setcookie('external_no_cache', '1');
    }

    function decipher($data, $key, $iv)
    {
        if (function_exists('openssl_decrypt')) {
            $plaintext = openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        } else {
            // @codingStandardsIgnoreStart
            $td = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            @mcrypt_generic_init($td, $key, $iv);
            $plaintext = @mdecrypt_generic($td, $data);
            // @codingStandardsIgnoreEnd

            //remove PKCS7 padding
            $last = substr($plaintext, -1);
            $plaintext = substr($plaintext, 0, strlen($plaintext) - ord($last));

            // @codingStandardsIgnoreStart
            @mcrypt_generic_deinit($td);
            @mcrypt_module_close($td);
            // @codingStandardsIgnoreEnd
        }

        return $plaintext;
    }

    function getJSONFromPOSTFields($encryptionKey)
    {
        //check if all the fields are set
        if (!isset($_POST['c']) and !isset($_POST['iv'])) {
            exitOnFatalError('FATAL_ERROR_POST_FIELDS_MISSING');
        } else {
            //encrypted data
            if (function_exists('mcrypt_module_open')) {
                $iv = base64_decode($_POST['iv']);
                $cipherText = base64_decode($_POST['c']);
                $json = decipher($cipherText, base64_decode($encryptionKey), $iv);
                $jsonRpc = json_decode($json);

                if ($jsonRpc == null) {
                    exitOnFatalError('FATAL_ERROR_INVALID_JSON', $json);
                }
            } else {
                $json = $_POST['c'];
                $jsonRpc = json_decode($json);

                if ($jsonRpc == null) {
                    exitOnFatalError('FATAL_ERROR_INVALID_JSON_ENCRYPTION_NOT_SUPPORTED');
                }
            }

            return $jsonRpc;
        }
    }

    function printCompressedDataHeader($dataLength)
    {
        echo 'P';
        echo 'M';
        echo '1';
        echo '0';

        $a = ($dataLength << 24) & 0xFF;
        $b = ($dataLength << 16) & 0xFF;
        $c = ($dataLength << 8) & 0xFF;
        $d = $dataLength & 0xFF;

        echo chr($a);
        echo chr($b);
        echo chr($c);
        echo chr($d);
    }

    function writeJSON($jsonRpcResult)
    {
        if (function_exists('gzcompress')) {
            $data = json_encode($jsonRpcResult);
            printCompressedDataHeader(strlen($data));
            echo gzcompress($data, 6);
        } else {
            echo json_encode($jsonRpcResult);
        }
    }
}

/***************************/
/* MAGENTO 1               */
/***************************/

namespace ProductManagerMagento1 {

    use Mage;
    use ProductManagerUtil;

    class Magento1Router
    {
        const BRIDGE_VERSION_M1 = '1.8.2';

        protected $_productManagerConfigHelper;
        protected $_productManagerDatabaseConnectionHelper;
        protected $_productManagerImageHelper;
        protected $_productManagerReindexHelper;
        protected $_openInMagentoHelper;

        public function __construct()
        {
            $this->_productManagerConfigHelper = new ProductManagerConfigHelper();
            $this->_productManagerDatabaseConnectionHelper = new ProductManagerDatabaseConnectionHelper();
            $this->_productManagerImageHelper = new ProductManagerImageHelper();
            $this->_productManagerReindexHelper = new ProductManagerReindexHelper();
            $this->_openInMagentoHelper = new OpenInMagentoHelper();
        }

        public function execute($encryptionKey, $username, $password, $key)
        {
            if (isset($_GET['editproduct'])) {
                $this->_openInMagentoHelper->openInMagento('editproduct', $_GET['editproduct'], null);
            } elseif (isset($_GET['editcategory'])) {
                $this->_openInMagentoHelper->openInMagento('editcategory', $_GET['editcategory'], null);
            } elseif (isset($_GET['viewproduct'])) {
                $this->_openInMagentoHelper->openInMagento('viewproduct', $_GET['viewproduct'], $_GET['storeid']);
            } elseif (isset($_GET['viewcategory'])) {
                $this->_openInMagentoHelper->openInMagento('viewcategory', $_GET['viewcategory'], $_GET['storeid']);
            } elseif (empty($_POST)) {
                echo "The bridge file is correctly installed.";
            } else {
                $startTime = microtime(true);

                ProductManagerUtil\writeHeaderAndCookie();
                $jsonRpc = ProductManagerUtil\getJSONFromPOSTFields($encryptionKey);

                if ($jsonRpc[0]->key != $key) {
                    ProductManagerUtil\exitOnFatalError('FATAL_ERROR_DIFFERENT_KEYS');
                }

                if ($password == '') {
                    $username = $jsonRpc[0]->username;
                    $encryptedpassword = $jsonRpc[0]->encryptedpassword;

                    $iv = base64_decode($_POST['iv']);

                    $password = ProductManagerUtil\decipher(
                        base64_decode($encryptedpassword),
                        base64_decode($encryptionKey),
                        $iv
                    );

                    $session = Mage::getSingleton('admin/session');
                    $userModel = Mage::getModel('admin/user');

                    if (!$session->isLoggedIn()) {
                        $user = $userModel->loadByUsername($username);

                        if (is_null($user->getId())) {
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME');
                        }

                        $session->setUser($user);

                        try {
                            if (!$userModel->authenticate($username, $password)) {
                                ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                            }
                        } catch (\Exception $e) {
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_AUTHENTIFICATION_EXCEPTION', $e->getMessage());
                        }
                    } else {
                        $user = $userModel->loadByUsername($username);

                        if (!Mage::helper('core')->validateHash($password, $user->getPassword())) {
                            $session->clear();
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                        }
                    }

                    /*$acl = Mage::getResourceModel('admin/acl')->loadAcl();
                    $session->setAcl($acl);

                    if (!$session->isAllowed('admin/catalog')) {
                        ProductManagerUtil\exitOnFatalError('FATAL_ERROR_NOT_ALLOWED_IN_ACL');
                    }*/
                } else {
                    if ($jsonRpc[0]->username != $username or $jsonRpc[0]->password != $password) {
                        ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                    }
                }

                $jsonRpcResult = $this->executeJsonRpc($jsonRpc[1]);

                $jsonRpcResult->executionTime = microtime(true) - $startTime;
                ProductManagerUtil\writeJSON($jsonRpcResult);
            }
        }

        public function executeJsonRpc(&$jsonRpc)
        {
            $startTime = microtime(true);
            $jsonRpcResult = new \stdClass();

            if ($jsonRpc->method == 'batch') {
                $jsonRpcResult->result = array();

                $count = count($jsonRpc->params);

                for ($i = 0; $i < $count; ++$i) {
                    $jsonRpcResult->result[$i] = $this->executeJsonRpc($jsonRpc->params[$i]);
                }
            } elseif ($jsonRpc->method == 'sqlquery') {
                /*if ($databaseAPI == "mysql")
                {
                    mysql_executeSqlQuery($jsonrpcresult, $jsonrpc);
                }
                else if ($databaseAPI == "mysqli")
                {
                   mysqli_executeSqlQuery($jsonrpcresult, $jsonrpc);
                }
                else if ($databaseAPI == "pdo")*/
                $is_read = ($jsonRpc->params[0] == 'r');
                $sql = $jsonRpc->params[1];
                $binds = array();

                for ($i = 2; $i < count($jsonRpc->params); ++$i) {
                    array_push($binds, $jsonRpc->params[$i]);
                }
                $this->_productManagerDatabaseConnectionHelper->pdo_executeSqlQuery($jsonRpcResult, $is_read, $sql, $binds);
            } elseif ($jsonRpc->method == 'databaseconnection') {
                $databaseAPI = $jsonRpc->params[0];

                $this->_productManagerDatabaseConnectionHelper->executeDatabaseConnection($jsonRpcResult, $databaseAPI);
            } elseif ($jsonRpc->method == 'uploadimage') {
                $type = $jsonRpc->params[0];
                $filename = $jsonRpc->params[1];  //htc-touch-diamond.jpg
                $data = $jsonRpc->params[2];
                $lastModificationTime = $jsonRpc->params[3];
                $failIfFileExists = $jsonRpc->params[4];
                $useDispretionPath = true;

                // To keep compatibility with Product Manager version < 2.1.1.65
                if (count($jsonRpc->params) > 5) {
                    $useDispretionPath = $jsonRpc->params[5];
                }

                $this->_productManagerImageHelper->uploadImage(
                    $jsonRpcResult,
                    $type,
                    $filename,
                    $data,
                    $lastModificationTime,
                    $failIfFileExists,
                    $useDispretionPath
                );
            } elseif ($jsonRpc->method == 'deleteimage') {
                $type = $jsonRpc->params[0];
                $filename = $jsonRpc->params[1];   // h/t/htc-touch-diamond.jpg

                $this->_productManagerImageHelper->deleteImage($jsonRpcResult, $type, $filename);
            } elseif ($jsonRpc->method == 'getconfig') {
                $this->_productManagerConfigHelper->getConfig($jsonRpcResult);
            } elseif ($jsonRpc->method == 'getsourcemodels') {
                $store_id = $jsonRpc->params[0];
                $locale_code = $jsonRpc->params[1];

                $this->_productManagerConfigHelper->getSourceModels($jsonRpcResult, $store_id, $locale_code);
            } elseif ($jsonRpc->method == 'reindexproducts') {
                $productIds = $jsonRpc->params;

                $this->_productManagerReindexHelper->reindexProducts($jsonRpcResult, $productIds);
            } elseif ($jsonRpc->method == 'connect') {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->bridgeversion = self::BRIDGE_VERSION_M1;
                $jsonRpcResult->result->platform = 'Magento 1';
                $jsonRpcResult->result->bridgetype = 'Bridge file';
                $jsonRpcResult->result->apiversion = '1';
            }

            $jsonRpcResult->id = $jsonRpc->id;
            $jsonRpcResult->executionTime = microtime(true) - $startTime;

            return $jsonRpcResult;
        }
    }

    class ProductManagerConfigHelper
    {
        public function getConfig(&$jsonRpcResult)
        {
            $storeId = 0;

            $jsonRpcResult->result = new \stdClass();

            $jsonRpcResult->result->magento_version = Mage::getVersion();
            $jsonRpcResult->result->php_version = phpversion();
            $jsonRpcResult->result->max_execution_time = ini_get('max_execution_time');
            $jsonRpcResult->result->max_input_time = ini_get('max_input_time');
            $jsonRpcResult->result->memory_limit = ini_get('memory_limit');
            $jsonRpcResult->result->post_max_size = ini_get('post_max_size');
            $jsonRpcResult->result->upload_max_filesize = ini_get('upload_max_filesize');
            $jsonRpcResult->result->zlib_output_compression = ini_get('zlib.output_compression');

            $jsonRpcResult->result->table_prefix = (string) Mage::getConfig()->getTablePrefix();

            //$jsonRpcResult->result->media_product_base_url     = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/';
            //$jsonRpcResult->result->media_category_base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/category/';

            $jsonRpcResult->result->media_product_base_url = Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl();
            $jsonRpcResult->result->media_product_base_path = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

            $jsonRpcResult->result->media_category_base_url = Mage::getBaseUrl('media') . '/catalog/category';  //TODO stores + secureURL
            $jsonRpcResult->result->media_category_base_path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category';

            $jsonRpcResult->result->locale_code = Mage::app()->getLocale()->getLocaleCode();
            $jsonRpcResult->result->date_format = Mage::app()->getLocale()->getDateFormat('short');
            $jsonRpcResult->result->datetime_format = Mage::app()->getLocale()->getDateFormat('long');

            $jsonRpcResult->result->base_currency = Mage::app()->getBaseCurrencyCode();
            $jsonRpcResult->result->base_currency_symbol = Mage::app()->getLocale()->currency($jsonRpcResult->result->base_currency)->getSymbol();
            $jsonRpcResult->result->base_currency_example = Mage::app()->getLocale()->currency($jsonRpcResult->result->base_currency)->toCurrency(1234567.89);
            $jsonRpcResult->result->base_currencies = Mage::getModel('directory/currency')->getConfigBaseCurrencies();
            $jsonRpcResult->result->default_currencies = Mage::getModel('directory/currency')->getConfigDefaultCurrencies();

            $stores = array_keys(Mage::app()->getStores(true));
            $jsonRpcResult->result->stores = $stores;

            //$jsonRpcResult->result->gift_message_available = $this->getAllStoresConfig(Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, $stores);
            $jsonRpcResult->result->gift_message_available = $this->getAllStoresConfig('sales/gift_options/allow_items', $stores);

            // from Mage_CatalogInventory_Model_Stock_Item
            $XML_PATH_ITEM = 'cataloginventory/item_options/';
            $XML_PATH_MIN_QTY = 'cataloginventory/item_options/min_qty';
            $XML_PATH_MIN_SALE_QTY = 'cataloginventory/item_options/min_sale_qty';
            $XML_PATH_MAX_SALE_QTY = 'cataloginventory/item_options/max_sale_qty';
            $XML_PATH_BACKORDERS = 'cataloginventory/item_options/backorders';
            $XML_PATH_NOTIFY_STOCK_QTY = 'cataloginventory/item_options/notify_stock_qty';
            $XML_PATH_MANAGE_STOCK = 'cataloginventory/item_options/manage_stock';
            $XML_PATH_ENABLE_QTY_INCREMENTS = 'cataloginventory/item_options/enable_qty_increments';
            $XML_PATH_QTY_INCREMENTS = 'cataloginventory/item_options/qty_increments';

            $jsonRpcResult->result->cataloginventory_item_options_manage_stock = Mage::getStoreConfig($XML_PATH_MANAGE_STOCK);
            $jsonRpcResult->result->cataloginventory_item_options_backorders = Mage::getStoreConfig($XML_PATH_BACKORDERS);
            $jsonRpcResult->result->cataloginventory_item_options_max_sale_qty = Mage::getStoreConfig($XML_PATH_MAX_SALE_QTY);
            $jsonRpcResult->result->cataloginventory_item_options_min_qty = Mage::getStoreConfig($XML_PATH_MIN_QTY);
            $jsonRpcResult->result->cataloginventory_item_options_min_sale_qty = Mage::getStoreConfig($XML_PATH_MIN_SALE_QTY);
            $jsonRpcResult->result->cataloginventory_item_options_notify_stock_qty = Mage::getStoreConfig($XML_PATH_NOTIFY_STOCK_QTY);
            $jsonRpcResult->result->cataloginventory_item_options_enable_qty_increments = Mage::getStoreConfig($XML_PATH_ENABLE_QTY_INCREMENTS);
            $jsonRpcResult->result->cataloginventory_item_options_qty_increments = Mage::getStoreConfig($XML_PATH_QTY_INCREMENTS);

            if (method_exists(Mage::helper('core'), 'isModuleEnabled')) {
                $jsonRpcResult->result->Mage_Index = Mage::helper('core')->isModuleEnabled('Mage_Index');
            }

            $jsonRpcResult->result->product_manager_configuration = new \stdClass();
            $jsonRpcResult->result->product_manager_configuration->permissions = new \stdClass();
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_editing = disable_grid_editing;
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_mass_action = disable_grid_mass_action;
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_configuration = disable_grid_configuration;
            $jsonRpcResult->result->product_manager_configuration->permissions->force_simplified_view_in_editors = force_simplified_view_in_editors;
        }

        public function getAllStoresConfig($path, $stores)
        {
            $values = new \stdClass();

            for ($i = 0; $i < count($stores); ++$i) {
                $storeId = $stores[$i];
                $values->$storeId = Mage::getStoreConfig($path, $storeId);
            }

            return $values;
        }

        public function modelGetAllOptions($modelClass)
        {
            $sourceModel = new \stdClass();
            $sourceModel->model_class = $modelClass;

            try {
                $model = Mage::getModel($modelClass);
                if ($model) {
                    $sourceModel->options = $model->getAllOptions();
                }
            } catch (\Throwable $e) {
                $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            }

            return $sourceModel;
        }

        public function modelToOptionArray($modelClass)
        {
            $sourceModel = new \stdClass();
            $sourceModel->model_class = $modelClass;

            try {
                $model = Mage::getModel($modelClass);
                if ($model) {
                    $sourceModel->options = $model->toOptionArray();
                }
            } catch (\Throwable $e) {
                $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            }

            return $sourceModel;
        }

        public function getAttributeSourceModels($entityType, $entityId)
        {
            $models = array();
            $attributes = Mage::getSingleton('eav/config')
                ->getEntityType($entityId)
                ->getAttributeCollection()
                ->addFieldToFilter('source_model', array('notnull' => true))
                ->addSetInfo();

            foreach ($attributes as $attribute) {
                $sourceModel = new \stdClass();

                try {
                    $sourceModel->entity_type = $entityType;
                    $sourceModel->attribute_id = $attribute->getAttributeId();
                    $sourceModel->attribute_code = $attribute->getAttributeCode();
                    $sourceModel->attribute_frontend_label = $attribute->getFrontendLabel();
                    $sourceModel->model_class = $attribute->getSourceModel();

                    $modelClass = Mage::getConfig()->getModelClassName($sourceModel->model_class);

                    $reflectionClass = new \ReflectionClass($modelClass);
                    if ($reflectionClass->isInstantiable()) {
                        $sourceModel->options = $attribute->getSource()->getAllOptions();
                    }
                } catch (\Throwable $e) {
                    $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
                }
                $models[] = $sourceModel;
            }

            return $models;
        }

        public function getSourceModels(&$jsonRpcResult, $store_id, $locale_code)
        {
            Mage::app('admin')->setCurrentStore($store_id);
            Mage::app()->getLocale()->setLocale($locale_code);

            $jsonRpcResult->result = new \stdClass();
            $models = array();

            $models[] = $this->modelGetAllOptions('bundle/product_attribute_source_price_view');
            $models[] = $this->modelGetAllOptions('catalog/category_attribute_source_layout');
            $models[] = $this->modelGetAllOptions('catalog/category_attribute_source_mode');
            $models[] = $this->modelGetAllOptions('catalog/category_attribute_source_page');
            $models[] = $this->modelGetAllOptions('catalog/category_attribute_source_sortby');
            $models[] = $this->modelGetAllOptions('catalog/entity_product_attribute_design_options_container');
            $models[] = $this->modelGetAllOptions('catalog/product_attribute_source_layout');
            $models[] = $this->modelGetAllOptions('catalog/product_status');
            $models[] = $this->modelGetAllOptions('catalog/product_type');
            $models[] = $this->modelGetAllOptions('catalog/product_visibility');
            $models[] = $this->modelToOptionArray('cataloginventory/source_backorders');
            $models[] = $this->modelToOptionArray('cataloginventory/source_stock');
            $models[] = $this->modelGetAllOptions('core/design_source_design');
            $models[] = $this->modelGetAllOptions('eav/entity_attribute_source_boolean');
            $models[] = $this->modelGetAllOptions('giftmessage/entity_attribute_source_boolean_config');
            $models[] = $this->modelGetAllOptions('tax/class_source_product');
            $models[] = $this->modelGetAllOptions('catalog/product_attribute_source_countryofmanufacture');
            $models[] = $this->modelGetAllOptions('catalog/product_attribute_source_msrp_type_enabled');
            $models[] = $this->modelGetAllOptions('catalog/product_attribute_source_msrp_type_price');

            // Mirasvit Advanced SEO Suite
            $models[] = $this->modelGetAllOptions('seo/system_config_source_canonical');
            $models[] = $this->modelGetAllOptions('seo/system_config_source_metarobots');
            $models[] = $this->modelGetAllOptions('seo/system_config_source_category');

            if (defined('Mage_Catalog_Model_Product::ENTITY')) {
                $productAttributesSourceModels = $this->getAttributeSourceModels('product', \Mage_Catalog_Model_Product::ENTITY);
            } else {
                // Magento 1.3
                $productAttributesSourceModels = $this->getAttributeSourceModels('product', 'catalog_product');
            }

            if (defined('Mage_Catalog_Model_Category::ENTITY')) {
                $categoryAttributesSourceModels = $this->getAttributeSourceModels('category', \Mage_Catalog_Model_Category::ENTITY);
            } else {
                // Magento 1.3
                $categoryAttributesSourceModels = $this->getAttributeSourceModels('category', 'catalog_category');
            }

            $jsonRpcResult->result->source_models = array_merge($models, $productAttributesSourceModels, $categoryAttributesSourceModels);

            $collection = Mage::getModel('customer/group')->getCollection();
            $customer_groups = array();
            foreach ($collection as $group) {
                $customer_groups[] = array('value' => $group->getData('customer_group_id'), 'label' => $group->getData('customer_group_code'));
            }

            $jsonRpcResult->result->customer_groups = $customer_groups;
        }
    }

    class ProductManagerDatabaseConnectionHelper
    {
        protected $_databaseAPI;

        public function __construct()
        {
        }

        public function executeDatabaseConnection(&$jsonRpcResult, $databaseAPI)
        {
            $this->_databaseAPI = $databaseAPI;
            $jsonRpcResult->result = $databaseAPI;
        }

        public function pdo_executeSqlQuery(&$jsonRpcResult, $is_read, $sql, $binds)
        {
            // read query

            if ($is_read) {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            } else {
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            }

            $readonly = true;
            if ($readonly) {
                $isSelect = (strpos($sql, 'SELECT') === 0);
                $isShowColumns = (strpos($sql, 'SHOW') === 0);

                if (!$isSelect && !$isShowColumns) {
                    return;
                }

                if (strpos($sql, ';') !== false) {
                    return;
                }
            }

            try {
                if (count($binds) > 0) {
                    $query = $connection->prepare($sql);
                    $query->execute($binds);
                } else {
                    $query = $connection->query($sql);
                }

                if ($query === false) {
                    $jsonRpcResult->error = $query->errorInfo();
                } else {
                    if (strpos($sql, 'SELECT') === 0 || strpos($sql, 'SHOW') === 0) {
                        //starts with SELECT or SHOW

                        /*$a = array();

                        while ($row = $query->fetch(PDO::FETCH_NUM))
                        {
                            $a[] = $row;
                        }

                        $jsonRpcResult->result = new \stdClass();
                        $jsonRpcResult->result->rows = $a;*/

                        $jsonRpcResult->result = new \stdClass();
                        $jsonRpcResult->result->rows = $query->fetchAll(\PDO::FETCH_NUM);
                    } else {
                        $jsonRpcResult->result = $query->rowCount();
                    }
                }
            } catch (\Exception $e) {
                $jsonRpcResult->error = $e->getMessage();
            }
        }
    }

    class ProductManagerImageHelper
    {
        public function __construct()
        {
        }

        public function uploadImage($jsonRpcResult, $type, $filename, $data, $lastModificationTime, $failIfFileExists, $useDispretionPath)
        {
            $nbBytes = 0;
            $error = null;
            $errorCode = null;
            $noDuplicatedFileName = '';
            $remoteFileSize = -1;
            $remoteLastModificationTime = 0;
            $imagesDeletedInCache = 0;

            if ($type == 'product') {
                $base = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            }
            if ($type == 'category') {
                $base = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category';
            }

            $useMageCoreModelFileUploader = class_exists('Mage_Core_Model_File_Uploader', false);

            // case1: $filename="photo1.jpg". $chunks=["photo1.jpg"]
            // case2: $filename="bundle/photo1.jpg". $chunks=["bundle", "photo1.jpg"]
            $chunks = explode('/', $filename);

            $filenameIndex = count($chunks)-1;    // the filename is the last element in the array. case1:$filenameIndex=0, case2: $filenameIndex=1
            $filename = $chunks[$filenameIndex];  // case1 and case2: $filename="photo1.jpg"

            if ($useMageCoreModelFileUploader) {
                $correctFileName = \Mage_Core_Model_File_Uploader::getCorrectFileName($filename);
                $dispretionPath = \Mage_Core_Model_File_Uploader::getDispretionPath($correctFileName);
            } else {
                $correctFileName = \Varien_File_Uploader::getCorrectFileName($filename);
                $dispretionPath = \Varien_File_Uploader::getDispretionPath($correctFileName);
            }

            // case1 and case2: $correctFileName="photo1.jpg" and $dispretionPath="/p/h

            if ($useDispretionPath) {
                // Update array with the correct filename (including dispretion path)
                $chunks[$filenameIndex] = $dispretionPath . DS . $correctFileName; //case1: $chunks=["/p/h/photo1.jpg"]
                $fileNameWithDispretionPath = implode(DS, $chunks); //case1: $fileNameWithDispretionPath="/p/h/photo1.jpg"
                $filePath = $base . $fileNameWithDispretionPath; //case1: $filePath="/www/magento-1.9.4.5/media/catalog/product/p/h/photo1.jpg"
            } else {
                // Update array with the correct filename (no dispretion path)
                $chunks[$filenameIndex] = $correctFileName; //case1: $chunks=["photo1.jpg"]     case2: $chunks=["bundle", "photo1.jpg"]
                $fileNameWithDispretionPath = implode(DS, $chunks); //case1: $fileNameWithDispretionPath="photo1.jpg"  case2: $fileNameWithDispretionPath="bundle/photo1.jpg"
                $filePath = $base . DS . $fileNameWithDispretionPath; //case1: $filePath="/www/magento-1.9.4.5/media/catalog/product/photo1.jpg"   case2: $filePath="/www/magento-1.9.4.5/media/catalog/product/bundle/photo1.jpg"
            }

            $destinationDirectory = dirname($filePath);

            $ioAdapter = new \Varien_Io_File();
            $ioAdapter->setAllowCreateFolders(true);
            $ioAdapter->checkAndCreateFolder($destinationDirectory);

            if (file_exists($filePath)) {
                $remoteLastModificationTime = filemtime($filePath);

                if ($failIfFileExists) {
                    $error = 'ERROR_FILE_AREADY_EXISTS'; //'File '.$filePath.' already exists';

                    if ($useMageCoreModelFileUploader) {
                        $noDuplicatedFileName = \Mage_Core_Model_File_Uploader::getNewFileName($filePath);
                    } else {
                        $noDuplicatedFileName = \Varien_File_Uploader::getNewFileName($filePath);
                    }
                } elseif (!is_writeable($filePath)) {
                    $error = 'ERROR_FILE_NOT_WRITABLE'; //'File '.$filePath.' is not writable';
                }
            } else {
                if (!is_writable($destinationDirectory)) {
                    $error = 'ERROR_DIRECTORY_NOT_WRITABLE'; //'Directory '.$destinationDirectory.' is not writable';
                }
            }

            // FILES
            if ($error == null) {
                $fileUploaded = false;

                if (count($_FILES)) {
                    foreach ($_FILES as $file) {
                        if ($file['name'] == $filename) {
                            $uploaded_size = $file['size'];
                            $uploaded_type = $file['type'];

                            /*if ($uploaded_size > 1024*1024*100)  //in bytes  1024*1024*100 = 100 mo
                            {
                              $error = 'ERROR_FILE_TOO_LARGE'. ;
                            } else*/

                            if ($file['error'] === UPLOAD_ERR_OK) {
                                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                                    $fileUploaded = true;
                                } else {
                                    $error = 'ERROR_MOVE_UPLOADED_FILE';
                                }
                            } else {
                                $error = 'ERROR_UPLOAD_ERR ' . $file['error'];
                            }
                        }
                    }
                } elseif ($data != null) {
                    $nbBytes = @file_put_contents($filePath, base64_decode($data, true));

                    if ($nbBytes === false) {
                        $error = 'ERROR_CANNOT_WRITE_FILE'; //'Cannot write '.$filePath;
                    } else {
                        $fileUploaded = true;
                    }
                }

                if ($fileUploaded) {
                    touch($filePath, $lastModificationTime);
                    $remoteLastModificationTime = filemtime($filePath);
                    //if (!is_null($mode)) chmod($filename, $mode);

                    if ($type == 'product') {
                        //remove image from the cache
                        //$dir=escapeshellcmd($base . DS . 'cache'); $files = shell_exec("find $dir -name '$fileName' -delete");

                        $imagesDeletedInCache = array();

                        $search = $base . DS . 'cache' . DS . '*' . DS . '*' . DS . '*' . DS . '*' . $fileNameWithDispretionPath;  //media\catalog\product\cache\1\small_image\135x\9df78eab33525d08d6e5fb8d27136e95\y\u\yulips.jpg
                        $paths1 = glob($search);

                        $search = $base . DS . 'cache' . DS . '*' . DS . '*' . DS . '*' . $fileNameWithDispretionPath;         //media\catalog\product\cache\1\image\9df78eab33525d08d6e5fb8d27136e95\y\u\yulips.jpg
                        $paths2 = glob($search);

                        $imagesDeletedInCache = array_merge($paths1, $paths2);

                        foreach ($imagesDeletedInCache as $file) {
                            @unlink($file);
                        }
                    }
                }
            }

            $remoteFileSize = filesize($filePath);

            $fileNameWithDispretionPath = str_replace(DS, '/', $fileNameWithDispretionPath);

            if ($error) {
                $jsonRpcResult->error = new \stdClass();
                $jsonRpcResult->error->errorcode = $error;
                $jsonRpcResult->error->remotefilename = $fileNameWithDispretionPath;
                $jsonRpcResult->error->remotefilepath = $filePath;
                $jsonRpcResult->error->remotefilesize = $remoteFileSize;
                $jsonRpcResult->error->remotelastmodificationtime = $remoteLastModificationTime;
                $jsonRpcResult->error->noduplicatedfilename = $noDuplicatedFileName;
                $jsonRpcResult->error->base = $base;
            } else {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->nbbytes = $nbBytes;
                $jsonRpcResult->result->remotefilename = $fileNameWithDispretionPath;
                $jsonRpcResult->result->remotefilepath = $filePath;
                $jsonRpcResult->result->remotefilesize = $remoteFileSize;
                $jsonRpcResult->result->remotelastmodificationtime = $remoteLastModificationTime;
                $jsonRpcResult->result->imagesDeletedInCache = $imagesDeletedInCache;
                $jsonRpcResult->result->base = $base;
            }
        }

        // DELETE IMAGE
        public function deleteImage($jsonRpcResult, $type, $filename)
        {
            $error = null;

            $base = '';
            if ($type == 'product') {
                $base = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            }
            if ($type == 'category') {
                $base = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category';
            }

            $useMageCoreModelFileUploader = class_exists('Mage_Core_Model_File_Uploader', false);

            if ($useMageCoreModelFileUploader) {
                $fileName = \Mage_Core_Model_File_Uploader::getCorrectFileName($filename);
                $dispretionPath = \Mage_Core_Model_File_Uploader::getDispretionPath($fileName);
            } else {
                $fileName = \Varien_File_Uploader::getCorrectFileName($filename);
                $dispretionPath = \Varien_File_Uploader::getDispretionPath($fileName);
            }

            $fileName = $dispretionPath . DS . $fileName;

            $filePath = $base . $fileName;

            if (!file_exists($filePath)) {
                $error = 'ERROR_FILE_DOESNT_EXIST'; //'File '.$filePath." doesn't exist";
            } else {
                if (!unlink($filePath)) {
                    $error = 'ERROR_FILE_CANT_BE_DELETED'; // 'File '.$filePath." cannot be deleted";
                }
            }

            if ($error) {
                $jsonRpcResult->error = new \stdClass();
                $jsonRpcResult->error->errorcode = $error;
                $jsonRpcResult->error->remotefilename = $fileName;
                $jsonRpcResult->error->remotefilepath = $filePath;
            } else {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->remotefilename = $fileName;
                $jsonRpcResult->result->remotefilepath = $filePath;
            }
        }
    }

    class ProductManagerReindexHelper
    {
        public function __construct()
        {
        }

        public function reindexProducts(&$jsonRpcResult, $productIds)
        {
            Mage::app('admin', 'store');
            Mage::app()->setCurrentStore(\Mage_Core_Model_App::ADMIN_STORE_ID);

            $count = count($productIds);

            for ($i = 0; $i < $count; ++$i) {
                //Mage_Adminhtml_Catalog_ProductController saveAction()

                $productId = $productIds[$i];
                $product = Mage::getModel('catalog/product')->load($productId);

                if ($product) {
                    try {
                        if (Mage::helper('core')->isModuleEnabled('Innoexts_StorePricing')) {
                            // Fix deleted tier prices with InnoExts Store view Pricing http://catgento.com/2011/12/17/how-to-set-tier-prices-programmatically-in-magento/   http://stackoverflow.com/questions/10176310/odd-behavior-when-saving-a-product-model-that-has-tier-pricing-from-a-script-in/10178922
                            $tierPrices = array(
                                'website_id' => 0,
                                'cust_group' => 2,
                                'price_qty' => 3000,
                                'price' => 1000,
                            );

                            $product->setTierPrice($tierPrices);
                        }

                        $product->setForceReindexRequired(true);
                        $product->setIsChangedCategories(true);

                        if (!$product->save()) {
                            $jsonRpcResult->error = 'save failed';

                            return;
                        }
                    } catch (\Exception $e) {
                        $jsonRpcResult->result = $e->getMessage() . '   stack trace: ' . $e->getTraceAsString();

                        return;
                    }

                    try {
                        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                        $stock->save();

                        if (method_exists(Mage::getModel('catalogrule/rule'), 'applyAllRulesToProduct')) {  //Magento version >= 1.5.0.0
                            Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($productId);
                        }
                    } catch (\Exception $e) {
                        $jsonRpcResult->error = $e->getMessage() . '   stack Trace: ' . $e->getTraceAsString();

                        return;
                    }
                }
            }

            $jsonRpcResult->result = 'reindex successfull';

            /*
            $product = Mage::getModel('catalog/product')->load('1'); // Product Id

            $event = Mage::getSingleton('index/indexer')->logEvent(
                    $product,
                    $product->getResource()->getType(),
                    Mage_Index_Model_Event::TYPE_SAVE,
                    false
                );
                
            Mage::getSingleton('index/indexer')
                ->getProcessByCode('catalog_url') // Adjust the indexer process code as needed
                ->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)
                ->processEvent($event);
        */
        }
    }

    class OpenInMagentoHelper
    {
        public function __construct()
        {
        }

        // EDIT PRODUCT OR CATEGORY IN MAGENTO
        public function openInMagento($action, $id, $storeId)
        {
            if (!is_numeric($id) || $id < 0 || $id != round($id)) {
                return;
            }

            Mage::getSingleton('core/session', array('name' => 'adminhtml'));
            $session = Mage::getSingleton('admin/session');
            $session->start();

            if ($action == 'editproduct') {
                $key = Mage::getSingleton('adminhtml/url')->getSecretKey('catalog_product', 'edit');
                $path = Mage::getUrl('adminhtml/catalog_product/edit/', array('id' => $id, 'key' => $key, '_current' => false));
                $path = str_replace(basename(__FILE__), 'index.php', $path);
                header('Location: ' . $path);
            }

            if ($action == 'editcategory') {
                $key = Mage::getSingleton('adminhtml/url')->getSecretKey('catalog_category', 'edit');
                $path = Mage::getUrl('adminhtml/catalog_category/edit/', array('id' => $id, 'key' => $key, '_current' => false, 'clear' => 1));
                $path = str_replace(basename(__FILE__), 'index.php', $path);
                header('Location: ' . $path);
            }

            if ($action == 'viewproduct') {
                //$path = Mage::getUrl('catalog/product/view', array('id' => $id));
                //$path = str_replace(basename(__FILE__), "index.php", $path);

                $product = Mage::getModel('catalog/product')->load($id);
                $categories = $product->getCategoryIds();

                if (count($categories) > 0) {
                    $category_id = current($categories);
                    $category = Mage::getModel('catalog/category')->load($category_id);
                    Mage::unregister('current_category');
                    Mage::register('current_category', $category);
                }

                $path = $product->getProductUrl(true);
                $path = str_replace(basename(__FILE__), 'index.php', $path);
                header('Location: ' . $path);
            }

            if ($action == 'viewcategory') {
                //$path = Mage::getUrl('catalog/category/view', array('id' => $id));
                //$path = str_replace(basename(__FILE__), "index.php", $path);

                $category = Mage::getModel('catalog/category')->load($id);
                $path = Mage::getUrl($category->getUrlPath());
                $path = str_replace(basename(__FILE__), 'index.php', $path);
                header('Location: ' . $path);
            }

            exit();
        }
    }
}

/***************************/
/* MAGENTO 2               */
/***************************/

namespace ProductManagerMagento2 {

    use Magento;
    use ProductManagerUtil;

    class Magento2Router
    {
        const BRIDGE_VERSION_M2 = '2.4.2';

        protected $_productManagerConfigHelper;
        protected $_productManagerDatabaseConnectionHelper;
        protected $_productManagerImageHelper;
        protected $_productManagerReindexHelper;
        protected $_openInMagentoHelper;
        protected $_userModel;
        protected $_authSession;

        public function __construct(
            ProductManagerConfigHelper $productManagerConfigHelper,
            ProductManagerDatabaseConnectionHelper $productManagerDatabaseConnectionHelper,
            ProductManagerImageHelper $productManagerImageHelper,
            ProductManagerReindexHelper $productManagerReindexHelper,
            OpenInMagentoHelper $openInMagentoHelper,
            Magento\User\Model\User $userModel,
            Magento\Backend\Model\Auth\Session $authSession
        ) {
            $this->_productManagerConfigHelper = $productManagerConfigHelper;
            $this->_productManagerDatabaseConnectionHelper = $productManagerDatabaseConnectionHelper;
            $this->_productManagerImageHelper = $productManagerImageHelper;
            $this->_productManagerReindexHelper = $productManagerReindexHelper;
            $this->_openInMagentoHelper = $openInMagentoHelper;
            $this->_userModel = $userModel;
            $this->_authSession = $authSession;
        }

        public function execute($encryptionKey, $username, $password, $key)
        {
            if (isset($_GET['editproduct'])) {
                $this->_openInMagentoHelper->openInMagento('editproduct', $_GET['editproduct'], null);
            } elseif (isset($_GET['editcategory'])) {
                $this->_openInMagentoHelper->openInMagento('editcategory', $_GET['editcategory'], null);
            } elseif (isset($_GET['viewproduct'])) {
                $this->_openInMagentoHelper->openInMagento('viewproduct', $_GET['viewproduct'], $_GET['storeid']);
            } elseif (isset($_GET['viewcategory'])) {
                $this->_openInMagentoHelper->openInMagento('viewcategory', $_GET['viewcategory'], $_GET['storeid']);
            } elseif (empty($_POST)) {
                echo "The bridge file is correctly installed.";
            } else {
                $startTime = microtime(true);

                ProductManagerUtil\writeHeaderAndCookie();

                $jsonRpc = ProductManagerUtil\getJSONFromPOSTFields($encryptionKey);

                if ($jsonRpc[0]->key != $key) {
                    ProductManagerUtil\exitOnFatalError('FATAL_ERROR_DIFFERENT_KEYS');
                }

                if ($password == '') {
                    $username = $jsonRpc[0]->username;
                    $encryptedpassword = $jsonRpc[0]->encryptedpassword;

                    $iv = base64_decode($_POST['iv']);

                    $password = ProductManagerUtil\decipher(base64_decode($encryptedpassword), base64_decode($encryptionKey), $iv);

                    /*if (!$this->_authSession->isLoggedIn()) {

                        $user = $this->_userModel->loadByUsername($username);

                        if (is_null($user->getId())) {
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME');
                        }

                        $this->_authSession->setUser($user);

                        try {
                            if (!$this->_userModel->authenticate($username, $password)) {
                                ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                            }

                            $this->_authSession->processLogin();
                        } catch (\Exception $e) {
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_AUTHENTIFICATION_EXCEPTION', $e->getMessage());
                        }
                    } else*/ {
                        $user = $this->_userModel->loadByUsername($username);

                        if (!$this->_userModel->verifyIdentity($password)) {
                            $this->_authSession->processLogout();
                            ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                        }
                    }

                    /*if (!$this->_authSession->isAllowed('Magento_Catalog::products')) {
                        ProductManagerUtil\exitOnFatalError('FATAL_ERROR_NOT_ALLOWED_IN_ACL');
                    }*/
                } else {
                    if ($jsonRpc[0]->username != $username or $jsonRpc[0]->password != $password) {
                        ProductManagerUtil\exitOnFatalError('FATAL_ERROR_WRONG_USERNAME_OR_PASSWORD');
                    }
                }

                $jsonRpcResult = $this->executeJsonRpc($jsonRpc[1]);
                $this->_productManagerDatabaseConnectionHelper->closeConnection();

                $jsonRpcResult->executionTime = microtime(true) - $startTime;
                ProductManagerUtil\writeJSON($jsonRpcResult);
            }
        }

        public function executeJsonRpc(&$jsonRpc)
        {
            $startTime = microtime(true);
            $jsonRpcResult = new \stdClass();

            if ($jsonRpc->method == 'batch') {
                $jsonRpcResult->result = array();

                $count = count($jsonRpc->params);

                for ($i = 0; $i < $count; ++$i) {
                    $jsonRpcResult->result[$i] = $this->executeJsonRpc($jsonRpc->params[$i]);
                }
            } elseif ($jsonRpc->method == 'sqlquery') {
                /*if ($databaseAPI == "mysql")
                {
                    mysql_executeSqlQuery($jsonrpcresult, $jsonrpc);
                }
                else if ($databaseAPI == "mysqli")
                {
                   mysqli_executeSqlQuery($jsonrpcresult, $jsonrpc);
                }
                else if ($databaseAPI == "pdo")*/
                $is_read = ($jsonRpc->params[0] == 'r');
                $sql = $jsonRpc->params[1];
                $binds = array();

                for ($i = 2; $i < count($jsonRpc->params); ++$i) {
                    array_push($binds, $jsonRpc->params[$i]);
                }

                $this->_productManagerDatabaseConnectionHelper->pdo_executeSqlQuery($jsonRpcResult, $is_read, $sql, $binds);
                //
            } elseif ($jsonRpc->method == 'databaseconnection') {
                $databaseAPI = $jsonRpc->params[0];
                $this->_productManagerDatabaseConnectionHelper->executeDatabaseConnection($jsonRpcResult, $databaseAPI);
                //
            } elseif ($jsonRpc->method == 'uploadimage') {
                $type = $jsonRpc->params[0];
                $filename = $jsonRpc->params[1];  //htc-touch-diamond.jpg
                $data = $jsonRpc->params[2];
                $lastModificationTime = $jsonRpc->params[3];
                $failIfFileExists = $jsonRpc->params[4];
                $useDispretionPath = true;

                // To keep compatibility with Product Manager version < 2.1.1.65
                if (count($jsonRpc->params) > 5) {
                    $useDispretionPath = $jsonRpc->params[5];
                }

                $this->_productManagerImageHelper->uploadImage(
                    $jsonRpcResult,
                    $type,
                    $filename,
                    $data,
                    $lastModificationTime,
                    $failIfFileExists,
                    $useDispretionPath
                );
            } elseif ($jsonRpc->method == 'deleteimage') {
                $type = $jsonRpc->params[0];
                $filename = $jsonRpc->params[1];   // h/t/htc-touch-diamond.jpg
                $this->_productManagerImageHelper->deleteImage($jsonRpcResult, $type, $filename);
                //
            } elseif ($jsonRpc->method == 'getconfig') {
                $this->_productManagerConfigHelper->getConfig($jsonRpcResult);
                //
            } elseif ($jsonRpc->method == 'getsourcemodels') {
                $store_id = $jsonRpc->params[0];
                $locale_code = $jsonRpc->params[1];
                $this->_productManagerConfigHelper->getSourceModels($jsonRpcResult, $store_id, $locale_code);
                //
                /*} elseif ($jsonRpc->method == 'reindexproducts') {
                // old version which does loadAndSaveProducts, regenerateUrlRewrite and reindexProductsWithAllIndexers
                $productIds = $jsonRpc->params;
                $this->_productManagerReindexHelper->reindexProducts($jsonRpcResult, $productIds);
                //*/
            } elseif ($jsonRpc->method == 'loadandsaveproducts') {
                $productIds = $jsonRpc->params;
                $this->_productManagerReindexHelper->loadAndSaveProducts($jsonRpcResult, $productIds);
                //
            } elseif ($jsonRpc->method == 'regenerateproductsurlrewrites') {
                $productIds = $jsonRpc->params;
                $this->_productManagerReindexHelper->regenerateProductsUrlRewrites($jsonRpcResult, $productIds);
                //
            } elseif ($jsonRpc->method == 'reindexproductsusingindexers') {
                $productIds = $jsonRpc->params->productIds;
                $reindexerIds = $jsonRpc->params->reindexerIds;
                $this->_productManagerReindexHelper->reindexProductsUsingIndexers($jsonRpcResult, $productIds, $reindexerIds);
                //
            } elseif ($jsonRpc->method == 'cleanproductscache') {
                $productIds = $jsonRpc->params;
                $this->_productManagerReindexHelper->cleanProductsCache($jsonRpcResult, $productIds);
                //
            } elseif ($jsonRpc->method == 'connect') {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->bridgeversion = self::BRIDGE_VERSION_M2;
                $jsonRpcResult->result->platform = 'Magento 2';
                $jsonRpcResult->result->bridgetype = 'Bridge file';
                $jsonRpcResult->result->bridgeapiversion = '2';
            }

            $jsonRpcResult->id = $jsonRpc->id;
            $jsonRpcResult->executionTime = microtime(true) - $startTime;

            return $jsonRpcResult;
        }
    }

    class ProductManagerConfigHelper
    {
        protected $_storeManager;
        protected $_productMediaConfig;
        protected $_productMetadata;
        protected $_giftMessageConfigProvider;
        protected $_resourceConnection;
        protected $_stockConfiguration;
        protected $_backendUrl;
        protected $_productAttributeCollectionFactory;
        protected $_categoryAttributeCollectionFactory;
        protected $_customerGroupsCollection;
        protected $_indexerCollectionFactory;

        public function __construct(
            \Magento\Store\Model\StoreManager $storeManager,
            \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
            \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
            \Magento\GiftMessage\Model\GiftMessageConfigProvider $giftMessageConfigProvider,
            \Magento\Framework\App\ResourceConnection $resourceConnection,
            \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
            \Magento\Backend\Model\UrlInterface $backendUrl,
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
            \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory $categoryAttributeCollectionFactory,
            \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupsCollection,
            \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
        ) {
            $this->_storeManager = $storeManager;
            $this->_productMediaConfig = $productMediaConfig;
            $this->_productMetadata = $productMetadataInterface;
            $this->_giftMessageConfigProvider = $giftMessageConfigProvider;
            $this->_resourceConnection = $resourceConnection;
            $this->_stockConfiguration = $stockConfiguration;
            $this->_backendUrl = $backendUrl;
            $this->_productAttributeCollectionFactory = $productAttributeCollectionFactory;
            $this->_categoryAttributeCollectionFactory = $categoryAttributeCollectionFactory;
            $this->_customerGroupsCollection = $customerGroupsCollection;
            $this->_indexerCollectionFactory = $indexerCollectionFactory;
        }

        public function getConfig(&$jsonRpcResult)
        {
            $jsonRpcResult->result = new \stdClass();

            $jsonRpcResult->result->magento_version = $version = $this->_productMetadata->getVersion();
            $jsonRpcResult->result->php_version = phpversion();
            $jsonRpcResult->result->max_execution_time = ini_get('max_execution_time');
            $jsonRpcResult->result->max_input_time = ini_get('max_input_time');
            $jsonRpcResult->result->memory_limit = ini_get('memory_limit');
            $jsonRpcResult->result->post_max_size = ini_get('post_max_size');
            $jsonRpcResult->result->upload_max_filesize = ini_get('upload_max_filesize');
            $jsonRpcResult->result->zlib_output_compression = ini_get('zlib.output_compression');

            $tableName = $this->_resourceConnection->getTableName('core_config_data');
            $pos = strrpos($tableName, 'core_config_data');
            if ($pos === false) {
                $prefix = '';
            } else {
                $prefix = substr($tableName, 0, $pos);
            }

            $jsonRpcResult->result->table_prefix = $prefix;

            $jsonRpcResult->result->media_product_base_url = $this->_productMediaConfig->getBaseMediaUrl();
            $jsonRpcResult->result->media_product_base_path = $this->_productMediaConfig->getBaseMediaPath();

            $baseMediaURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $baseMediaDir = $this->_storeManager->getStore()->getBaseMediaDir();

            $jsonRpcResult->result->media_category_base_url = $baseMediaURL . 'catalog/category';  //TODO stores + secureURL
            $jsonRpcResult->result->media_category_base_path = $baseMediaDir . '/' . 'catalog' . '/' . 'category';

            $jsonRpcResult->result->installation_in_pub_folder = !file_exists('app/bootstrap.php');

            // Fix for https://github.com/magento/magento2/issues/8868
            if ($jsonRpcResult->result->installation_in_pub_folder) {
                $jsonRpcResult->result->original_media_category_base_url = $jsonRpcResult->result->media_category_base_url;
                $jsonRpcResult->result->original_media_product_base_url = $jsonRpcResult->result->media_product_base_url;

                if (file_exists('media/catalog')) {
                    $jsonRpcResult->result->media_category_base_url = str_replace('/pub/media/catalog/', '/media/catalog/', $jsonRpcResult->result->media_category_base_url);
                    $jsonRpcResult->result->media_product_base_url = str_replace('/pub/media/catalog/', '/media/catalog/', $jsonRpcResult->result->media_product_base_url);
                }
            }

            $jsonRpcResult->result->locale_code = $this->_storeManager->getStore()->getLocaleCode();
            //$jsonrpcresult->result->date_format = Mage::app()->getLocale()->getDateFormat('short');
            //$jsonrpcresult->result->datetime_format = Mage::app()->getLocale()->getDateFormat('long');
            $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency();

            $jsonRpcResult->result->base_currency = $this->_storeManager->getStore()->getBaseCurrencyCode();
            //$jsonrpcresult->result->base_currency_symbol     =  $baseCurrency->getSymbol();
            //$jsonrpcresult->result->base_currency_example     =  $baseCurrency->toCurrency(1234567.89);
            //$jsonrpcresult->result->base_currencies         = Mage::getModel('directory/currency')->getConfigBaseCurrencies();
            //$jsonrpcresult->result->default_currencies         = Mage::getModel('directory/currency')->getConfigDefaultCurrencies();

            $storeCollection = $this->_storeManager->getStores(true);

            $jsonRpcResult->result->stores = array_keys($storeCollection);
            $jsonRpcResult->result->storeCollection = $storeCollection;

            /* $this->_giftMessageConfigProvider

             $giftMessageConfigProvider = $objectManager->get('\Magento\GiftMessage\Model\GiftMessageConfigProvider');
             $itemLevelGiftMessageConfiguration = (bool)$this->scopeConfiguration->getValue(
                 GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
                 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
             );

             $gift_message_available = new \stdClass();

             for ($i = 0; $i < count($storeCollection); $i ++)
             {
                 $storeid = $storeCollection[$i];
                 $gift_message_available->$storeid = Mage::getStoreConfig(Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS, $storeid);
             }
             $jsonrpcresult->result->gift_message_available  = $gift_message_available;*/

            $jsonRpcResult->result->cataloginventory_item_options_manage_stock = $this->_stockConfiguration->getManageStock();
            $jsonRpcResult->result->cataloginventory_item_options_backorders = $this->_stockConfiguration->getBackorders();
            $jsonRpcResult->result->cataloginventory_item_options_max_sale_qty = $this->_stockConfiguration->getMaxSaleQty();
            $jsonRpcResult->result->cataloginventory_item_options_min_qty = $this->_stockConfiguration->getMinQty();
            $jsonRpcResult->result->cataloginventory_item_options_min_sale_qty = $this->_stockConfiguration->getMinSaleQty();
            $jsonRpcResult->result->cataloginventory_item_options_notify_stock_qty = $this->_stockConfiguration->getNotifyStockQty();
            $jsonRpcResult->result->cataloginventory_item_options_enable_qty_increments = $this->_stockConfiguration->getEnableQtyIncrements();
            $jsonRpcResult->result->cataloginventory_item_options_qty_increments = $this->_stockConfiguration->getQtyIncrements();

            $indexerCollection = $this->_indexerCollectionFactory->create();
            $jsonRpcResult->result->indexers = array();

            foreach ($indexerCollection->getItems() as $indexer) {
                $indexerInfo = new \stdClass();
                $indexerInfo->indexer_id = $indexer->getId();
                $indexerInfo->title = $indexer->getTitle();
                $indexerInfo->description = $indexer->getDescription();

                $jsonRpcResult->result->indexers[] = $indexerInfo;
            }

            $jsonRpcResult->result->product_manager_configuration = new \stdClass();
            $jsonRpcResult->result->product_manager_configuration->permissions = new \stdClass();
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_editing = disable_grid_editing;
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_mass_action = disable_grid_mass_action;
            $jsonRpcResult->result->product_manager_configuration->permissions->disable_grid_configuration = disable_grid_configuration;
            $jsonRpcResult->result->product_manager_configuration->permissions->force_simplified_view_in_editors = force_simplified_view_in_editors;
        }

        public function getAttributeSourceModels($entityType, $attributeCollectionFactory)
        {
            $models = array();

            $attributeCollection = $attributeCollectionFactory->create();
            $attributeCollection->addFieldToFilter('source_model', array('neq' => 'NULL'));
            $attributes = $attributeCollection->getItems();

            foreach ($attributes as $attribute) {
                $sourceModel = new \stdClass();

                try {
                    $sourceModel->entity_type = $entityType;
                    $sourceModel->attribute_id = $attribute->getAttributeId();
                    $sourceModel->attribute_code = $attribute->getAttributeCode();
                    $sourceModel->attribute_frontend_label = $attribute->getFrontendLabel();
                    $sourceModel->model_class = $attribute->getSourceModel();
                    $sourceModel->options = $attribute->getSource()->getAllOptions();
                } catch (\Throwable $e) {
                    $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
                }
                $models[] = $sourceModel;
            }

            return $models;
        }

        public function modelGetAllOptions($modelClass)
        {
            $sourceModel = new \stdClass();
            $sourceModel->model_class = $modelClass;

            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $objectManager->create($modelClass);

                if ($model) {
                    $sourceModel->options = $model->getAllOptions(true);
                }
            } catch (\Throwable $e) {
                $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            }

            return $sourceModel;
        }

        public function modelGetOptions($modelClass)
        {
            $sourceModel = new \stdClass();
            $sourceModel->model_class = $modelClass;

            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $objectManager->create($modelClass);

                if ($model) {
                    $sourceModel->options = $model->getOptions();
                }
            } catch (\Throwable $e) {
                $sourceModel->error = 'Exception: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString();
            }

            return $sourceModel;
        }

        public function getSourceModels(&$jsonRpcResult, $store_id, $locale_code)
        {
            $jsonRpcResult->result = new \stdClass();

            $models = array();

            $models[] = $this->modelGetAllOptions('Magento\Bundle\Model\Product\Attribute\Source\Price\View');
            $models[] = $this->modelGetAllOptions('Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Category\Attribute\Source\Layout');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Category\Attribute\Source\Mode');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Category\Attribute\Source\Page');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Entity\Product\Attribute\Design\Options\Container');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Product\Attribute\Source\Boolean');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Product\Attribute\Source\Layout');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Product\Attribute\Source\Status');
            $models[] = $this->modelGetOptions('Magento\Catalog\Model\Product\Type');
            $models[] = $this->modelGetAllOptions('Magento\Catalog\Model\Product\Visibility');
            $models[] = $this->modelGetAllOptions('Magento\CatalogInventory\Model\Source\Stock');
            $models[] = $this->modelGetAllOptions('Magento\Eav\Model\Entity\Attribute\Source\Boolean');
            $models[] = $this->modelGetAllOptions('Magento\Msrp\Model\Product\Attribute\Source\Type\Price');
            $models[] = $this->modelGetAllOptions('Magento\Tax\Model\TaxClass\Source\Product');
            $models[] = $this->modelGetAllOptions('Magento\Theme\Model\Theme\Source\Theme');

            $productAttributesSourceModels = $this->getAttributeSourceModels('product', $this->_productAttributeCollectionFactory);
            $categoryAttributesSourceModels = $this->getAttributeSourceModels('category', $this->_categoryAttributeCollectionFactory);

            $jsonRpcResult->result->source_models = array_merge($models, $productAttributesSourceModels, $categoryAttributesSourceModels);

            $customerGroups = $this->_customerGroupsCollection->toOptionArray();
            $jsonRpcResult->result->customer_groups = $customerGroups;
        }
    }

    class ProductManagerDatabaseConnectionHelper
    {
        protected $_storeManager;
        protected $_resourceConnection;
        protected $_connection;
        protected $_databaseAPI;

        public function __construct(
            Magento\Store\Model\StoreManager $storeManager,
            Magento\Framework\App\ResourceConnection $resourceConnection
        ) {
            $this->_storeManager = $storeManager;
            $this->_resourceConnection = $resourceConnection;
        }

        public function executeDatabaseConnection(&$jsonRpcResult, $databaseAPI)
        {
            $this->_databaseAPI = $databaseAPI;
            $jsonRpcResult->result = $databaseAPI;
        }

        public function closeConnection()
        {
            if ($this->_connection) {
                $this->_connection->closeConnection();
            }
        }

        public function pdo_executeSqlQuery(&$jsonRpcResult, $is_read, $sql, $binds)
        {
            if (!$this->_connection) {
                $this->_connection = $this->_resourceConnection->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            }

            $readonly = true;
            if ($readonly) {
                $isSelect = (strpos($sql, 'SELECT') === 0);
                $isShowColumns = (strpos($sql, 'SHOW') === 0);

                if (!$isSelect && !$isShowColumns) {
                    return;
                }

                if (strpos($sql, ';') !== false) {
                    return;
                }
            }

            try {
                if (count($binds) > 0) {
                    $query = $this->_connection->query($sql, $binds);
                } else {
                    $query = $this->_connection->query($sql);
                }

                if ($query === false) {
                    $jsonRpcResult->error = $query->errorInfo();
                } else {
                    if (strpos($sql, 'SELECT') === 0 || strpos($sql, 'SHOW') === 0) {
                        //starts with SELECT or SHOW

                        /*$a = array();

                        while ($row = $query->fetch(PDO::FETCH_NUM)) {
                            $a[] = $row;
                        }

                        $jsonrpcresult->result = new \stdClass();
                        $jsonrpcresult->result->rows = $a;*/

                        $jsonRpcResult->result = new \stdClass();
                        $jsonRpcResult->result->rows = $query->fetchAll(\Zend_Db::FETCH_NUM);
                    } else {
                        $jsonRpcResult->result = $query->rowCount();
                    }
                }
            } catch (\Exception $e) {
                $jsonRpcResult->error = $e->getMessage();
            }
        }
    }

    class ProductManagerImageHelper
    {
        protected $_storeManager;
        protected $_productMediaConfig;
        protected $_mediaDirectory;
        protected $_uploader;
        protected $_imageResize;

        public function __construct(
            \Magento\Store\Model\StoreManager $storeManager,
            \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\MediaStorage\Service\ImageResize $imageResize = null

        ) {
            $this->_storeManager = $storeManager;
            $this->_productMediaConfig = $productMediaConfig;
            $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $this->_imageResize = $imageResize;
        }

        public function uploadImage($jsonRpcResult, $type, $filename, $data, $lastModificationTime, $failIfFileExists, $useDispretionPath)
        {
            $nbBytes = 0;
            $error = null;
            $errorCode = null;
            $noDuplicatedFileName = '';
            $remoteFileSize = -1;
            $remoteLastModificationTime = 0;
            $imagesDeletedInCache = 0;

            // \Magento\Framework\Api\ImageProcessor

            $base = '';
            if ($type == 'product') {
                $base = $this->_productMediaConfig->getBaseMediaPath();
            }
            if ($type == 'category') {
                $base = 'catalog/category';
            }

            $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($filename);

            if ($useDispretionPath) {
                $dispretionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
                $fileNameWithDispretionPath = $dispretionPath . '/' . $fileName;
                $filePath = $base . $fileNameWithDispretionPath;
            } else {
                $fileNameWithDispretionPath = $fileName;
                $filePath = $base . '/' .  $fileNameWithDispretionPath;
            }


            $absoluteFilePath = $this->_mediaDirectory->getAbsolutePath($filePath);

            if ($this->_mediaDirectory->isExist($filePath)) {
                //   file_exists($absolutefilepath)

                $remoteLastModificationTime = filemtime($absoluteFilePath);

                if ($failIfFileExists) {
                    $error = 'ERROR_FILE_AREADY_EXISTS'; //'File '.$filePath.' already exists';

                    $noDuplicatedFileName = \Magento\MediaStorage\Model\File\Uploader::getNewFileName($absoluteFilePath);
                } elseif (!$this->_mediaDirectory->isWritable($filePath)) {
                    $error = 'ERROR_FILE_NOT_WRITABLE'; //'File '.$filePath.' is not writable';
                }
            } else {
                $destinationDirectory = dirname($filePath);

                if ($this->_mediaDirectory->isExist($destinationDirectory) && !$this->_mediaDirectory->isWritable($destinationDirectory)) {
                    $error = 'ERROR_DIRECTORY_NOT_WRITABLE'; //'Directory '.$destinationDirectory.' is not writable';
                }
            }

            // FILES
            if ($error == null) {
                $fileUploaded = false;

                if (count($_FILES)) {
                    foreach ($_FILES as $file) {
                        if ($file['name'] == $filename) {
                            $uploaded_size = $file['size'];
                            $uploaded_type = $file['type'];

                            /*if ($uploaded_size > 1024*1024*100)  //in bytes  1024*1024*100 = 100 mo
                            {
                              $error = 'ERROR_FILE_TOO_LARGE'. ;
                            } else*/

                            if ($file['error'] === UPLOAD_ERR_OK) {
                                try {
                                    $tempPath = $this->_productMediaConfig->getTmpMediaPath(basename($file['tmp_name']));

                                    $this->_mediaDirectory->create(dirname($tempPath));

                                    $tempAbsolutePath = $this->_mediaDirectory->getAbsolutePath($tempPath);

                                    $r = move_uploaded_file($file['tmp_name'], $tempAbsolutePath);
                                    $this->_mediaDirectory->copyFile($tempPath, $filePath);

                                    //   $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));

                                    $fileUploaded = true;
                                } catch (\Exception $e) {
                                    //$error = 'ERROR_MOVE_UPLOADED_FILE';
                                    $error = $e->getMessage();
                                }
                            } else {
                                $error = 'ERROR_UPLOAD_ERR ' . $file['error'];
                            }
                        }
                    }
                }

                if ($fileUploaded) {
                    touch($absoluteFilePath, $lastModificationTime);
                    $remoteLastModificationTime = filemtime($absoluteFilePath);
                    //if (!is_null($mode)) chmod($filename, $mode);

                    if ($type == 'product') {
                        //remove image from the cache
                        //$dir=escapeshellcmd($base . DS . 'cache'); $files = shell_exec("find $dir -name '$fileName' -delete");

                        $imagesDeletedInCache = array();

                        $search = $base . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $fileNameWithDispretionPath;  //media\catalog\product\cache\1\small_image\135x\9df78eab33525d08d6e5fb8d27136e95\y\u\yulips.jpg
                        $paths1 = glob($search);

                        $search = $base . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $fileNameWithDispretionPath;         //media\catalog\product\cache\1\image\9df78eab33525d08d6e5fb8d27136e95\y\u\yulips.jpg
                        $paths2 = glob($search);

                        $imagesDeletedInCache = array_merge($paths1, $paths2);

                        foreach ($imagesDeletedInCache as $file) {
                            @unlink($file);
                        }

                        if ($this->_imageResize) {
                            // Create resized images in cache
                            $this->_imageResize->resizeFromImageName($fileNameWithDispretionPath);
                        }
                    }
                }
            }

            $remoteFileSize = 0;

            if (file_exists($absoluteFilePath)) {
                $remoteFileSize = filesize($absoluteFilePath);
            }

            $fileNameWithDispretionPath = str_replace(DIRECTORY_SEPARATOR, '/', $fileNameWithDispretionPath);

            if ($error) {
                $jsonRpcResult->error = new \stdClass();
                $jsonRpcResult->error->errorcode = $error;
                $jsonRpcResult->error->remotefilename = $fileNameWithDispretionPath;
                $jsonRpcResult->error->remotefilepath = $filePath;
                $jsonRpcResult->error->remoteabsolutefilepath = $absoluteFilePath;
                $jsonRpcResult->error->remotefilesize = $remoteFileSize;
                $jsonRpcResult->error->remotelastmodificationtime = $remoteLastModificationTime;
                $jsonRpcResult->error->noduplicatedfilename = $noDuplicatedFileName;
                $jsonRpcResult->error->base = $base;
            } else {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->nbbytes = $nbBytes;
                $jsonRpcResult->result->remotefilename = $fileNameWithDispretionPath;
                $jsonRpcResult->result->remotefilepath = $filePath;
                $jsonRpcResult->result->remoteabsolutefilepath = $absoluteFilePath;
                $jsonRpcResult->result->remotefilesize = $remoteFileSize;
                $jsonRpcResult->result->remotelastmodificationtime = $remoteLastModificationTime;
                $jsonRpcResult->result->imagesDeletedInCache = $imagesDeletedInCache;
                $jsonRpcResult->result->base = $base;
            }
        }

        public function deleteImage($jsonRpcResult, $type, $filename)
        {
            $error = null;

            $base = '';
            if ($type == 'product') {
                $base = $this->_productMediaConfig->getBaseMediaPath();
            }
            if ($type == 'category') {
                $base = $this->_storeManager->getStore()->getBaseMediaDir() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category';
            }

            $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($filename);
            $dispretionPath = \Magento\MediaStorage\Model\File\Uploader::getDispretionPath($fileName);
            $fileNameWithDispretionPath = $dispretionPath . '/' . $fileName;

            $filePath = $base . $fileNameWithDispretionPath;

            if (!file_exists($filePath)) {
                $error = 'ERROR_FILE_DOESNT_EXIST'; //'File '.$filePath." doesn't exist";
            } else {
                if (!unlink($filePath)) {
                    $error = 'ERROR_FILE_CANT_BE_DELETED'; // 'File '.$filePath." cannot be deleted";
                }
            }

            if ($error) {
                $jsonRpcResult->error = new \stdClass();
                $jsonRpcResult->error->errorcode = $error;
                $jsonRpcResult->error->remotefilename = $fileName;
                $jsonRpcResult->error->remotefilepath = $filePath;
            } else {
                $jsonRpcResult->result = new \stdClass();
                $jsonRpcResult->result->remotefilename = $fileName;
                $jsonRpcResult->result->remotefilepath = $filePath;
            }
        }
    }

    class ProductManagerReindexHelper
    {
        protected $_storeManager;
        protected $_productModel;
        protected $_productFactory;
        protected $_productRepository;
        protected $_indexerCollectionFactory;
        protected $_productCollectionFactory;
        protected $_urlRewriteGenerator;
        protected $_urlRewrite;
        protected $_urlPersist;
        protected $_urlFinder;
        protected $_productUrlPathGenerator;
        protected $_cacheManager;
        protected $_productMetadata;
        protected $_searchCriteriaBuilder;
        protected $_sourceItemsBySku;
        protected $_sourceItemRepository;

        public function __construct(
            \Magento\Store\Model\StoreManager $storeManager,
            \Magento\Catalog\Model\Product $productModel,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $urlRewriteGenerator,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite $urlRewrite,
            \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
            \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
            \Magento\Framework\App\CacheInterface $cacheManager,
            \Magento\Framework\App\ProductMetadataInterface $productMetadata,
            \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder = null,
            \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku = null,
            \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepository = null
        ) {
            $this->_storeManager = $storeManager;
            $this->_productModel = $productModel;
            $this->_productFactory = $productFactory;
            $this->_productRepository = $productRepository;
            $this->_indexerCollectionFactory = $indexerCollectionFactory;
            $this->_productCollectionFactory = $productCollectionFactory;
            $this->_urlRewriteGenerator = $urlRewriteGenerator;
            $this->_urlRewrite = $urlRewrite;
            $this->_urlPersist = $urlPersist;
            $this->_urlFinder = $urlFinder;
            $this->_productUrlPathGenerator = $productUrlPathGenerator;
            $this->_cacheManager = $cacheManager;
            $this->_productMetadata = $productMetadata;
            $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
            $this->_sourceItemsBySku = $sourceItemsBySku;
            $this->_sourceItemRepository = $sourceItemRepository;
        }

        /*public function reindexProducts(&$jsonRpcResult, $productIds)
        {
            $startTime = microtime(true);

            $this->_storeManager->setCurrentStore('admin');
            $result = array();

            $this->loadAndSaveProducts($result, $productIds);
            $this->regenerateUrlRewrite($result, $productIds);
            $this->reindexProductsWithAllIndexers($result, $productIds);

            $jsonRpcResult->result = $result;

            $jsonRpcResult->executionTime = microtime(true) - $startTime;
        }*/

        public function loadAndSaveProducts(&$result, $productIds)
        {
            $this->_storeManager->setCurrentStore('admin');
            $a = array();

            $count = count($productIds);

            for ($i = 0; $i < $count; ++$i) {
                $productId = $productIds[$i];

                $startTime = microtime(true);

                $r = new \StdClass();
                $r->productId = $productId;
                $r->comment = "Load and save product in Magento (product ID $productId)";

                try {
                    $product = $this->_productRepository->getById($productId);

                    if ($product) {
                        //$product->setIsChangedCategories(true);
                        //$product->setOrigData('url_key', 'ruJrisesdu3useeu2nrYlir23Iuietghp9tedlXuife9eshur');

                        //Fix Magento 2.2 bug. See https://github.com/magento/magento2/issues/10687
                        $product->setMediaGalleryEntries($product->getMediaGalleryEntries());

                        // Fix has_options for configurable products
                        // https://magento.stackexchange.com/questions/201587/magento-2-how-to-create-configurable-product-programmatically
                        if ($product->getTypeId() == 'configurable') {
                            $configurable_attributes_data = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
                            $product->setCanSaveConfigurableAttributes(true);
                            $product->setConfigurableAttributesData($configurable_attributes_data);
                        }

                        // Fix has_options for bundle products
                        if ($product->getTypeId() == 'bundle') {
                            $product->setCanSaveBundleSelections(true);
                            //$bundleSelections = $product->getTypeInstance()->getOptions($product);
                            //$options = $product->getBundleOptionsData();
                            //$product->setBundleSelectionsData($bundleSelections);
                        }

                        if (!$this->_productRepository->save($product)) {
                            $r->error = "Load and save product error (product ID $productId):" . " save failed product";
                        } else {
                            $r->result = "Load and save product successful (product ID $productId)";
                        }
                    }
                } catch (\Exception $e) {
                    $r->error = "Load and save product error (product ID $productId):" . $e->getMessage() . '   stack trace: ' . $e->getTraceAsString();
                }
                $this->_cacheManager->clean('catalog_product_' . $productId);

                $r->executionTime = microtime(true) - $startTime;
                $a[] = $r;
            }


            $result->result = $a;

            //$jsonRpcResult->result = $result;
        }

        public function regenerateProductsUrlRewrites(&$result, $productIds)
        {
            $this->_storeManager->setCurrentStore('admin');

            $result->comment = "Regenerate URL rewrites for " . count($productIds) . ' product(s)';

            $stores = $this->_storeManager->getStores(false);
            $a = array();

            foreach ($stores as $store) {

                $collection = $this->_productCollectionFactory->create();
                $storeId = $store->getId();

                $collection->addStoreFilter($store->getId())
                    ->setStoreId($store->getId());

                if (!empty($productIds)) {
                    $collection->addIdFilter($productIds);
                }

                $collection->addAttributeToSelect(['url_path', 'url_key', 'visibility']);
                $productList = $collection->load();

                //$a[] = 'nb stores=' . count($stores) . ' store id=' . $storeId . 'nbProducts=' .  $productList->count();

                foreach ($productList as $product) {
                    $startTime = microtime(true);
                    $r = new \StdClass();
                    $r->comment = 'Regenerate URL rewrites for product ID ' . $product->getId();
                    $r->productId = $product->getId();
                    $r->storeId = $store->getId();
                    $r->storeCode = $store->getCode();
                    $r->urlKey = $product->getUrlKey();
                    $r->visibility = $product->getVisibility();

                    switch ($product->getVisibility()) {
                        case \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE:
                            $r->visibilityString = "Not Visible Individually";
                            break;
                        case \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG:
                            $r->visibilityString = "Catalog";
                            break;
                        case \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH:
                            $r->visibilityString = "Search";
                            break;
                        case \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH:
                            $r->visibilityString = "Catalog, Search";
                            break;
                    }

                    // Find existing rewrites
                    $existingUrls = $this->_urlFinder->findAllByData([
                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 0,
                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $store->getId(),
                    ]);

                    $r->existingUrlRewrites = array();
                    foreach ($existingUrls as &$urlRewrite) {
                        $u = new \StdClass();
                        $u->url_rewrite_id = $urlRewrite->getUrlRewriteId();
                        $u->entity_type = $urlRewrite->getEntityType();
                        $u->entity_id = $urlRewrite->getEntityId();
                        $u->request_path = $urlRewrite->getRequestPath();
                        $u->target_path = $urlRewrite->getTargetPath();
                        $u->redirect_type = $urlRewrite->getRedirectType();
                        $u->storeId = $urlRewrite->getStoreId();
                        $r->existingUrlRewrites[] = $u;
                    }

                    if ($product->isVisibleInSiteVisibility()) {
                        try {
                            $product->unsUrlPath();
                            $urlPath = $this->_productUrlPathGenerator->getUrlPath($product);
                            $product->setUrlPath($urlPath);

                            // Generate new rewrites
                            $newUrls = $this->_urlRewriteGenerator->generate($product);

                            $r->newUrlRewrites = array();
                            foreach ($newUrls as &$urlRewrite) {
                                $u = new \StdClass();
                                $u->entity_type = $urlRewrite->getEntityType();
                                $u->entity_id = $urlRewrite->getEntityId();
                                $u->request_path = $urlRewrite->getRequestPath();
                                $u->target_path = $urlRewrite->getTargetPath();
                                $u->storeId = $urlRewrite->getStoreId();
                                $r->newUrlRewrites[] = $u;
                            }

                            if (!$this->compareUrlRewriteArrays($newUrls, $existingUrls)) {
                                // The URL rewrites are not the same

                                // Remove conflicting 301 redirects
                                foreach ($newUrls as $newUrl) {
                                    $this->_urlPersist->deleteByData([
                                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REQUEST_PATH => $newUrl->getRequestPath(),
                                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 301,
                                        \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $store->getId()
                                    ]);
                                }

                                // Update URL rewrites in the database
                                $this->_urlPersist->replace($newUrls);

                                //$r->result = 'Regenerate url rewrite successful (product ' . $product->getId() . '). Urls=' . implode(', ', array_keys($newUrls));
                                $r->result = 'Url rewrites replaced';
                            } else {
                                // The URL rewrites are the same

                                $r->result = 'Url rewrites unchanged';
                                //$r->result = 'Regenerate url rewrite unchanged (product ' . $product->getId() . '). Urls=' . implode(', ', array_keys($newUrls));
                            }
                        } catch (\Exception $e) {
                            $r->error = 'Url rewrites error';
                            $r->message = $e->getMessage();
                        }

                        $r->executionTime = microtime(true) - $startTime;
                        $a[] = $r;
                    } else {
                        //$product->setStoreId($store->getId());
                        $this->_urlPersist->deleteByData([
                            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
                            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 0,
                            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $store->getId()
                        ]);

                        $r->result = 'Url rewrites deleted. Existing URLS deleted because product is not visible';
                        $a[] = $r;
                    }
                }
            }

            $result->result = $a;
        }


        public function simplifyUrlRewriteArray(array $urls)
        {
            $a = array();

            foreach ($urls as $url) {

                // Ignore 301 redirect
                if ($url->getRedirectType() == 0) {
                    $a[] = [$url->getRequestPath(), $url->getTargetPath()];
                }
            }

            asort($a);

            return $a;
        }


        public function compareUrlRewriteArrays(array $newUrls, array $existingUrls)
        {
            //shuffle($newUrls);
            $a = $this->simplifyUrlRewriteArray($newUrls);
            $b = $this->simplifyUrlRewriteArray($existingUrls);
            $result = ($a == $b);
            return $result;
        }



        public function reindexProductsUsingIndexers(&$jsonRpcResult, $productIds, $indexerIdsToUse)
        {
            // Set a default list of indexers to use to reindex products if $indexerIdsToUse is empty
            if (empty($indexerIdsToUse)) {
                $indexerIdsToUse = [
                    'catalog_product_category',
                    'catalog_product_attribute',
                    'inventory',
                    'catalogrule_product',
                    'cataloginventory_stock',
                    'catalog_product_price',
                    'catalogsearch_fulltext'
                ];
            }

            // Do not use these indexers to reindex products
            $indexerIdsToSkip = [
                'design_config_grid',
                'customer_grid',
                'catalog_category_product',
                'catalogrule_rule',
                'elasticsuite_thesaurus'
            ];

            $this->reindexUsingIndexers($jsonRpcResult, $productIds, $indexerIdsToUse, $indexerIdsToSkip, 'product ID', 'product(s)');
        }


        public function reindexUsingIndexers(&$jsonRpcResult, $productIds, $indexerIdsToUse, $indexerIdsToSkip, $entityTypeIdString, $entityTypeString)
        {
            $a = array();

            $this->_storeManager->setCurrentStore('admin');

            $jsonRpcResult->comment = 'Reindex ' . count($productIds) . ' ' . $entityTypeString;
            $jsonRpcResult->indexerIdsToUse = $indexerIdsToUse;
            $jsonRpcResult->indexerIdsToSkip = $indexerIdsToSkip;

            $indexerCollection = $this->_indexerCollectionFactory->create();
            $indexerIds = $indexerCollection->getAllIds();

            $productsIdsString = implode(',', $productIds);

            foreach ($indexerCollection->getItems() as $indexer) {
                $indexerId = $indexer->getId();
                $skipIndexer = false;

                // Skip if $indexerId is in $indexerIdsToSkip
                if (in_array($indexerId, $indexerIdsToSkip)) {
                    $skipIndexer = true;
                }

                // Skip if $indexerId is not in $reindexerIds
                if (!empty($indexerIds) && !in_array($indexerId, $indexerIdsToUse)) {
                    $skipIndexer = true;
                }

                if ($skipIndexer) {
                    $r = new \StdClass();
                    $r->result = 'Skip reindex ' . $indexerId;
                    $a[] = $r;
                } else {
                    $startTime = microtime(true);
                    if ($indexerId == 'inventory') {
                        $skus = $this->getSkus($productIds);
                        $sourceItems = $this->getSourceItems($skus);
                        // $sourceItems = ['2110', '2112', '2113'];
                        // vendor/magento/module-inventory-indexer/Indexer/SourceItem/SourceItemIndexer.php

                        if (!empty($sourceItems)) {
                            if (method_exists($indexer, 'executeList')) {
                                //Magento version >=  2.3
                                $indexer->executeList($sourceItems);
                            } else {
                                $indexer->reindexList($sourceItems);
                            }
                        }
                    } else {
                        if (method_exists($indexer, 'executeList')) {
                            //Magento version >=  2.3
                            $indexer->executeList($productIds);
                        } else {
                            $indexer->reindexList($productIds);
                        }
                    }

                    $r = new \StdClass();
                    $r->result = "Reindex $indexerId successful ($entityTypeIdString $productsIdsString)";
                    $r->executionTime = microtime(true) - $startTime;
                    $a[] = $r;
                }
            }

            $jsonRpcResult->result = $a;
        }

        public function getSkus($productIds)
        {
            $collection = $this->_productCollectionFactory->create();

            if (!empty($productIds)) {
                $collection->addIdFilter($productIds);
            }

            $collection->addAttributeToSelect(['sku']);
            $productList = $collection->load();

            $skus = array();
            foreach ($collection as $product) {
                $skus[] = $product->getSku();
            }

            return $skus;
        }

        public function getSourceItems($skus)
        {
            $sourceItems = array();

            /*foreach ($skus as $sku) {
                $s = $this->_sourceItemsBySku->getSourceItemBySku($sku);
            }*/

            $searchCriteria = $this->_searchCriteriaBuilder->addFilter(\Magento\Catalog\Api\Data\ProductInterface::SKU, $skus, 'in')->create();


            /*$searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(new \Magento\Framework\Api\Filter([
                    \Magento\Framework\Api\Filter::KEY_FIELD => \Magento\Catalog\Api\Data\ProductInterface::SKU,
                    \Magento\Framework\Api\Filter::KEY_CONDITION_TYPE => 'in',
                    \Magento\Framework\Api\Filter::KEY_VALUE => $skus
                ]))
                ->create();*/

            $sourceItemData = $this->_sourceItemRepository->getList($searchCriteria);

            foreach ($sourceItemData->getItems() as $sourceItem) {
                $sourceItems[] = $sourceItem->getSourceItemId();
            }

            return $sourceItems;
        }


        public function cleanProductsCache(&$result, $productIds)
        {
            $this->_storeManager->setCurrentStore('admin');
            $result->comment = 'Clean product cache for ' . count($productIds) . ' product(s)';

            $count = count($productIds);

            for ($i = 0; $i < $count; ++$i) {
                $productId = $productIds[$i];
                $this->_cacheManager->clean('catalog_product_' . $productId);
            }

            $productsIdsString = implode(',', $productIds);
            $result->result = "product cache cleaned (product ID $productsIdsString)";
        }
    }

    class OpenInMagentoHelper
    {
        protected $_storeManager;
        protected $_productModel;
        protected $_categoryModel;
        protected $_registry;
        protected $_backendHelper;

        public function __construct(
            Magento\Store\Model\StoreManager $storeManager,
            Magento\Catalog\Model\Product $productModel,
            Magento\Catalog\Model\Category $categoryModel,
            Magento\Framework\Registry $registry,
            Magento\Backend\Helper\Data $backendHelper
        ) {
            $this->_storeManager = $storeManager;
            $this->_productModel = $productModel;
            $this->_categoryModel = $categoryModel;
            $this->_registry = $registry;
            $this->_backendHelper = $backendHelper;
        }

        // EDIT PRODUCT OR CATEGORY IN MAGENTO
        public function openInMagento($action, $id, $storeId)
        {
            if (!is_numeric($id) || $id < 0 || $id != round($id)) {
                return;
            }

            if ($action == 'editproduct') {
                // http://magento-2-1-5.dev/pub/benabeebridge_may_sIVErbuxwmDWCykuyiLHq3CXBCN25h.php?editproduct=1845&XDEBUG_SESSION_START=PHPSTORM
                $path = $this->_backendHelper->getUrl(
                    'catalog/product/edit',
                    array('id' => $id)
                );

                header('Location: ' . $path);
            }

            if ($action == 'editcategory') {
                // http://magento-2-1-5.dev/pub/benabeebridge_may_sIVErbuxwmDWCykuyiLHq3CXBCN25h.php?editcategory=27&XDEBUG_SESSION_START=PHPSTORM
                $path = $this->_backendHelper->getUrl(
                    'catalog/category/edit',
                    array('id' => $id)
                );

                header('Location: ' . $path);
            }

            if ($action == 'viewproduct') {
                // http://magento-2-1-5.dev/pub/benabeebridge_may_sIVErbuxwmDWCykuyiLHq3CXBCN25h.php?viewproduct=1845&storeid=1&XDEBUG_SESSION_START=PHPSTORM
                $this->_storeManager->setCurrentStore($storeId);

                $product = $this->_productModel->load($id);
                $categories = $product->getCategoryIds();

                if (count($categories) > 0) {
                    $category_id = current($categories);
                    $category = $this->_categoryModel->load($category_id);

                    $this->_registry->unregister('current_category');
                    $this->_registry->register('current_category', $category);
                }

                $path = $product->getProductUrl(true);

                header('Location: ' . $path);
            }

            if ($action == 'viewcategory') {
                // http://magento-2-1-5.dev/pub/benabeebridge_may_sIVErbuxwmDWCykuyiLHq3CXBCN25h.php?viewcategory=20&storeid=1&XDEBUG_SESSION_START=PHPSTORM
                $this->_storeManager->setCurrentStore($storeId);

                $category = $this->_categoryModel->load($id);
                $path = $category->getUrl();

                header('Location: ' . $path);
            }

            exit();
        }
    }
}
