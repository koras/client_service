<?php
namespace App\Utils;

use App\Services\LogService\LogsService;
use App\Services\LogService\LogTiberiumDataService;
use Arispati\EmojiRemover\EmojiRemover;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Tiberium
{
    private string $login;
    private string $password;
    private string $endpoint;
    private HttpClientInterface $httpClient;
    private XmlEncoder $encoder;
    private LoggerInterface $logger;
    private CacheInterface $tiberiumCache;
    private LogsService $loggerElastic;
    private LogTiberiumDataService $dataService;

    public function __construct($login, $password, $endpoint, HttpClientInterface $httpClient, XmlEncoder $encoder, LoggerInterface $logger, CacheInterface $tiberiumCache, LogTiberiumDataService $logDataService)
    {
        $this->login = $login;
        $this->password = $password;
        $this->endpoint = $endpoint;
        $this->httpClient = $httpClient;
        $this->encoder = $encoder;
        $this->logger = $logger;
        $this->tiberiumCache = $tiberiumCache;
        $this->loggerElastic = LogsService::getInstance();
        $this->dataService = $logDataService;
    }

    public function setDsn(string $dsn): void
    {
        $scheme  = parse_url($dsn, PHP_URL_SCHEME);
        $login = parse_url($dsn, PHP_URL_USER);
        $password = parse_url($dsn, PHP_URL_PASS);
        $host = parse_url($dsn, PHP_URL_HOST);
        $path = parse_url($dsn, PHP_URL_PATH);
        $this->setLogin($login);
        $this->setPassword($password);
        $this->setEndpoint($scheme . '://' . $host . $path . '/');
    }
    
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param int $product_id
     * @return array|null
     */
    public function getProduct(int $product_id):?array
    {
        $transaction_id = str_replace(".", "", microtime(true))+rand(0,10000);
        $hash = md5($transaction_id . 'GetProduct' . $this->login . $this->password);
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetProduct',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'Limit' => [
                    '@offset' => 0,
                    '@row_count' => 500,
                ],
                'Products' => [
                    'Product' => $product_id,
                ]
            ]
        ];
        return $this->_doRequest('GetProduct', $request, true);
    }

    public function getProducts(array $productIds): ?array
    {
        $transaction_id = str_replace(".", "", microtime(true))+rand(0,10000);
        $hash = md5($transaction_id . 'GetProduct' . $this->login . $this->password);

        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetProduct',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'Limit' => [
                    '@offset' => 0,
                    '@row_count' => 500,
                ],
                'Products' => [
                    'Product' => $productIds
                ]
            ]
        ];

        return $this->_doRequest('GetProduct', $request, true);
    }

    public function getCardsList():?array
    {
        $transaction_id = str_replace(".", "", microtime(true))+rand(0,10000);
        $hash = md5($transaction_id . 'GetProduct' . $this->login . $this->password);
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetProduct',
                'Hash' => $hash,
            ],
        ];
        return $this->_doRequest('GetCardsList', $request, true);
    }

    public function getCard(string $name, string $product, string $card, int $amount, string $email = null):?array
    {
        $name = trim($name);
        $name = $name != '' ? $name : 'Аноним';
        $transaction_id = str_replace(".", "", microtime(true))+rand(0,10000);
        $hash = md5($transaction_id . 'GetCard' . $this->login . $this->password);
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetCard',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'BasketID' => $transaction_id,
                'OfferAccepted' => 'true',
                'User' => [
//                    'FirstName' => htmlspecialchars(iconv("UTF-8", "UTF-8//IGNORE", $name), ENT_XML1 | ENT_QUOTES),
                    'FirstName' => $name,
//                    'LastName' => htmlspecialchars(iconv("UTF-8", "UTF-8//IGNORE", $name), ENT_XML1 | ENT_QUOTES),
                    'LastName' => $name,
                    'Phone' => '+79991234567',
                    'Email' => $email,
                ],
                'Products' => [
                    'Product' => [
                        'Id' => $product,
                        'Sum' => $amount,
                        'CardNumber' => $card,
                    ],
                ],
            ],
        ];
        
        return $this->_doRequest('GetCard', $request);
    }

    /**
     * @param array $products
     * @param int $basket_id
     * @param int|null $transaction_id
     * @return array|null
     */
    public function getDeliveryVariants(array $products, int $basket_id, int $transaction_id = null):?array
    {
        $transaction_id = $transaction_id ?? $this->generateTxId();
        $hash = md5($transaction_id . 'GetDeliveryVariants' . $this->login . $this->password);
        $items = [];
        foreach ($products as $product) {
            $items['Product'][] = [
                'Id' => $product['id'],
                'Quantity' => $product['quantity'],
            ];
        }
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetDeliveryVariants',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'BasketID' => $basket_id,
                'Products' => $items,
            ]
        ];
        return $this->_doRequest('GetDeliveryVariants', $request);
    }

    /**
     * @param array $products
     * @param int $basket_id
     * @param int $order_id
     * @param string $name
     * @param string $email
     * @param string|null $msisdn
     * @param int|null $transaction_id
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function makeOrder(array $products, int $basket_id, int $order_id, string $name, string $email, string $msisdn = null, string $payment_comment = null, int $transaction_id = null): array
    {
        $name = trim($name);
        $name = $name != '' ? $name :  'Аноним';
        $transaction_id = $transaction_id ?? $this->generateTxId();
        $hash = md5($transaction_id . 'MakeOrder' . $this->login . $this->password);
        $items = [];
        $name = EmojiRemover::filter($name, 'emoji');
        foreach($products as $product) {
            $items['Product'][] = [
                'Id' => $product['id'],
                'DeliveryID' => 1,
                'Quantity' => $product['quantity'],
            ];
        }
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'MakeOrder',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'OrderID' => $order_id,
                'BasketID' => $basket_id,
                'ExpectsPayment' => 'true',
                'User' => [
//                    'FirstName' => htmlspecialchars(iconv("UTF-8", "UTF-8//IGNORE", $name), ENT_XML1 | ENT_QUOTES),
                    'FirstName' => $name,
                    'MiddleName' => '',
//                    'LastName' => htmlspecialchars(iconv("UTF-8", "UTF-8//IGNORE", $name), ENT_XML1 | ENT_QUOTES),
                    'LastName' => $name,
                    'Email' => $email,
                    'Phone' => $msisdn,
                ],
                'Products' => $items,
                'OrderComment' => $payment_comment,
            ],
        ];
        if(strlen($msisdn) != 12) {
            $request['Parameters']['User']['Phone'] = '+70000000000';
        }
        return $this->_doRequest('MakeOrder', $request);
    }

    /**
    * @param int $basket_id
    * @param int|null $transaction_id
    * @return array
    * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
    */
    public function confirmOrder(int $basket_id, int $transaction_id = null): array
    {
        $transaction_id = $transaction_id ?? $this->generateTxId();
        $hash = md5($transaction_id . 'ConfirmOrder' . $this->login . $this->password);

        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'ConfirmOrder',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'BasketID' => $basket_id,
            ],
        ];
        return $this->_doRequest('ConfirmOrder', $request);
    }

    /**
     * @param int $basket_id
     * @param int|null $transaction_id
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getCertificates(int $basket_id, int $transaction_id = null): array
    {
        $transaction_id = $transaction_id ?? $this->generateTxId();
        $hash = md5($transaction_id . 'GetCertificates' . $this->login . $this->password);
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'GetCertificates',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'BasketID' => $basket_id,
                'ExcludeCertificateFile' => true,
            ],
        ];
        $result = $this->_doRequest('GetCertificates', $request);
        if($result['Status'] != 2) {
            $this->logger->warning(json_encode([
                'system' => 'tiberium',
                'method' => 'getCertificates',
                'status' => $result['Status'],
                'error_code' => $result['Error']['ErrorCode'] ?: 'cannot get error code',
                'error_description' => $result['Error']['ErrorMessage'] ?: 'cannot get error description',
            ]));

            $msg = isset($result['Error']['ErrorMessage']) ? 'status:' . $result['Status'] . ' ' . $result['Error']['ErrorMessage'] : 'status:' . $result['Status'] . ' ' . 'cannot get error description';
            $preparedData = $this->dataService->prepareData(__FUNCTION__, LogLevel::WARNING, $request, $msg);
            $this->dataService->buildData(__CLASS__, __FUNCTION__, $preparedData);
            $this->loggerElastic::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
            throw new \Exception('Wrong response from Tiberium');
        }
        $path = $result['OrderCertificates']['Logisticians']['Logistician']['Certificate'] ?: null;
        if(null !== $path && isset ($path['Number'])) {
//            $this->logger->warning("SINGLE CERTIFICATE DETECTED. ");
            $result['OrderCertificates']['Logisticians']['Logistician']['Certificate'] = [0 => $path];
        }
        return $result;
    }

	public function getOrderStatus(int $basket_id, int $transaction_id = null):?array
	{
		$transaction_id = $transaction_id ?? $this->generateTxId();
		$hash = md5($transaction_id . 'GetOrderStatus' . $this->login . $this->password);
		$request = [
			'Authentication' => [
				'Login' => $this->login,
				'TransactionID' => $transaction_id,
				'MethodName' => 'GetOrderStatus',
				'Hash' => $hash,
			],
			'Parameters' => [
				'BasketID' => $basket_id,
				'ExcludeCertificateFile' => true,
			],
		];
		return $this->_doRequest('GetOrderStatus', $request);
	}

    /**
     * @param int $basket_id
     * @param array $data_payment
     * @param int|null $transaction_id
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function setPaymentMethod( int $basket_id, array $data_payment, int $transaction_id = null): array
    {
        $transaction_id = $transaction_id ?? $this->generateTxId();
        $hash = md5($transaction_id . 'SetPaymentMethod' . $this->login . $this->password);
        $request = [
            'Authentication' => [
                'Login' => $this->login,
                'TransactionID' => $transaction_id,
                'MethodName' => 'SetPaymentMethod',
                'Hash' => $hash,
            ],
            'Parameters' => [
                'BasketID' => $basket_id,
                'PaymentMethods' => $data_payment
            ],
        ];
        $result = $this->_doRequest('SetPaymentMethod', $request);
        if($result['Status'] != 2) {
            $this->logger->warning(json_encode([
                'system' => 'tiberium',
                'method' => 'SetPaymentMethod',
                'status' => $result['Status'],
                'error_code' => $result['Error']['ErrorCode'] ?: 'cannot get error code',
                'error_description' => $result['Error']['ErrorMessage'] ?: 'cannot get error description',
            ]));

            $msg = isset($result['Error']['ErrorMessage']) ? 'status:' . $result['Status'] . ' ' . $result['Error']['ErrorMessage'] : 'status:' . $result['Status'] . ' ' . 'cannot get error description';
            $preparedData = $this->dataService->prepareData(__FUNCTION__, LogLevel::WARNING, $request, $msg);
            $this->dataService->buildData(__CLASS__, __FUNCTION__, $preparedData);
            $this->loggerElastic::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
            throw new \Exception('Wrong response from Tiberium');
        }
        return $result;
    }

    private function _doRequest(string $method, array $request, bool $use_cache = false):?array
    {
        $xml = $this->encoder->encode($request, 'xml', [
            'xml_root_node_name' => 'Request',
        ]);
//        $hash = sha1($xml);
//        if($use_cache) {
//            $httpClient = $this->httpClient;
//            $logger = $this->logger;
//            $response = $this->tiberiumCache->get($hash, function (ItemInterface $item) use ($xml, $method, $httpClient, $logger) {
//                $logger->info(sprintf("get from cache: %s", $xml));
//                $response = $httpClient->request('POST', $this->endpoint . $method, [
//                    'body' => $xml,
//                ]);
//                $item->expiresAfter(3600);
//                return $response->getContent();
//            });
//        } else {
            $this->logger->info(sprintf("Request to Tiberium (%s): %s", $this->endpoint, $xml));
            $preparedData = $this->dataService->prepareData($method, LogLevel::INFO, $request, $xml);
            $this->dataService->buildData(__CLASS__, $method, $preparedData);
            $this->loggerElastic::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);

            $response = $this->httpClient->request('POST', $this->endpoint . $method, [
                'body' => $xml,
            ])->getContent();

            $this->logger->info(sprintf("response from tiberium: %s", json_encode($this->encoder->decode($response, 'xml'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
            $preparedData = $this->dataService->prepareData($method, LogLevel::INFO, $request, $xml);
            $this->dataService->buildData(__CLASS__, $method, $preparedData);
            $this->loggerElastic::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
//        }
        return $this->encoder->decode($response, 'xml');
    }

    private function generateTxId(): string
    {
        return str_shuffle(time()+rand(0,1000000));
    }
}