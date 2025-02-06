<?php
namespace App\Utils;

use App\Services\LogService\BaseLogDataService;
use App\Services\LogService\LogsService;
use Psr\Log\LoggerInterface;
use App\CustomExceptions\SmsException;
use Psr\Log\LogLevel;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StreamTelecom
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $host;
    private string $login;
    private string $password;
    private BaseLogDataService $dataService;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, string $host, string $login, string $password, BaseLogDataService $dataService)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->host = $host;
        $this->login = $login;
        $this->password = $password;
        $this->dataService = $dataService;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws SmsException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function sendSms(string $msisdn, string $sms): ResponseInterface
    {
        $this->logger->info(sprintf("send sms with text %s to msisdn %s", $sms, $msisdn));
        $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'sendSMS', LogLevel::INFO, BaseLogDataService::TYPE_OUT, sprintf("send sms with text %s to msisdn %s", $sms, $msisdn));
        LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);

        $response = $this->httpClient->request('GET', $this->host, [
            'query' => [
                'user' => $this->login,
                'pwd' => $this->password,
                'name_deliver' => 'MGC-loyalty Widget',
                'sadr' => 'MGC-loyalty',
                'dadr' => $msisdn,
                'text' => $sms
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode === 200) {
            $this->logger->info("RESPONSE FROM TRANSTELECOM: " . $response->getStatusCode() . ', ' . $response->getContent());
            $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'sendSMS', LogLevel::INFO, BaseLogDataService::TYPE_OUT, sprintf("send sms with text %s to msisdn %s", $sms, $msisdn));
            LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
            return $response;
        } else {
            throw new SmsException('SMS to '.$msisdn.' was not sent via stream telecom');
        }
    }
}