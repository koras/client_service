<?php
namespace App\Utils;

use App\Controller\DownloadOrderController;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Services\LogService\BaseLogDataService;
use App\Services\LogService\LogsService;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class EmailGenerator
{
    private string $userFilesDir;
    private string $logoDir;
    private string $coverDir;

    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
        private DownloadOrderController $download,
        private BaseLogDataService $dataService,
        private ParameterBagInterface $parameterBag,
    )
    {
        $this->userFilesDir = $this->parameterBag->get('app.user_files_dir');
        $this->logoDir = $this->parameterBag->get('app.logo_dir');
        $this->coverDir = $this->parameterBag->get('app.cover_dir');
    }

    /**
     * @param Order $order
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function generateOrderSuccessEmail(Order $order): string
    {
        $this->logger->info(sprintf('Start generate orderSuccessEmail for order#%s', $order->getId()));
        $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'generateEmail', LogLevel::INFO, BaseLogDataService::TYPE_IN, printf('Start generate orderSuccessEmail for order#%s', $order->getId()));
        LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
        foreach($order->getOrderItems() as $orderItem) {
            $this->logger->info('order_item is: ' . print_r($orderItem->getId(), true));
            $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'generateEmail', LogLevel::INFO, BaseLogDataService::TYPE_IN, 'order_item is: ' . print_r($orderItem->getId(), true));
            $this->dataService->prepareDataFromOrderItem($orderItem);
            LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);
        }

        $templateDir = $this->parameterBag->get('email_template.order_created');
        $userFilesDir = $this->getUserFilesDirByWidgetId($order->getWidget()->getId());
        $http_host = str_replace('api.', '', $order->getPaymentData()['metadata']['http_host']);
        return $this->twig->render('@templates/' . basename($order->getWidget()->getMailTemplate()->getFilename()) . $templateDir, [
            'logo_dir' => $userFilesDir . $this->logoDir,
            'cover_dir' => $userFilesDir . $this->coverDir,
            'order' => $order,
            'delayed' => new DateTimeImmutable() < $order->getOrderItems()[0]->getTimeToSend() ? true : false,
            'template' => basename($order->getWidget()->getMailTemplate()->getFilename()),
            'http_host' => $http_host,
        ]);
    }

    public function generateCertificatesEmail(array $certificates): string
    {
        $this->logger->info(sprintf('Start generate certificatesEmail'));
        $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'generateEmail', LogLevel::INFO, BaseLogDataService::TYPE_IN, sprintf('Start generate certificatesEmail'));
        LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);

        $templateDir = $this->parameterBag->get('email_template.certificates');
        $userFilesDir = $this->getUserFilesDirByWidgetId($certificates[0]->getOrderItem()->getWidgetOrder()->getWidget()->getId());
        $paymentData = $certificates[0]->getOrderItem()->getWidgetOrder()->getPaymentData();
        $http_host = str_replace('api.', '', $paymentData['metadata']['http_host']);
        if (count($certificates) > 1) {
            $zip = $this->download->generateZip($certificates, $this->logger, $this->dataService);
        }
        return $this->twig->render('@templates/' . basename($certificates[0]->getOrderItem()->getWidgetOrder()->getWidget()->getMailTemplate()->getFilename()) . $templateDir, [
            'logo_dir' => $userFilesDir . $this->logoDir,
            'cover_dir' => $userFilesDir . $this->coverDir,
            'orderItem' => $certificates[0]->getOrderItem(),
            'certificates' => $certificates,
            'delayed' => new DateTimeImmutable() > $certificates[0]->getOrderItem()->getTimeToSend(),
            'template' => basename($certificates[0]->getOrderItem()->getWidgetOrder()->getWidget()->getMailTemplate()->getFilename()),
            'http_host' => $http_host,
            'zip' => $zip??false,
        ]);
    }

    public function generateDeliveredEmail(OrderItem $orderItem): string
    {
        $this->logger->info('Start generate deliveredEmail');
        $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'generateEmail', LogLevel::INFO, BaseLogDataService::TYPE_IN, sprintf('Start generate deliveredEmail'));
        LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);

        $templateDir = $this->parameterBag->get('email_template.mail_delivered');
        $userFilesDir = $this->getUserFilesDirByWidgetId($orderItem->getWidgetOrder()->getWidget()->getId());
        $http_host = str_replace('api.', '', $orderItem->getWidgetOrder()->getPaymentData()['metadata']['http_host']);
        return $this->twig->render('@templates/' . basename($orderItem->getWidgetOrder()->getWidget()->getMailTemplate()->getFilename()) . $templateDir, [
            'logo_dir' => $userFilesDir . $this->logoDir,
            'orderItem' => $orderItem,
            'template' => basename($orderItem->getWidgetOrder()->getWidget()->getMailTemplate()->getFilename()),
            'http_host' => $http_host,
        ]);
    }

    public function generateOrderDeliveredEmail(Order $order): string
    {
        $this->logger->info(sprintf('Start generate generateOrderDeliveredEmail for order#%s', $order->getId()));
        $this->dataService->prepareBaseData(__CLASS__, __FUNCTION__, 'generateEmail', LogLevel::INFO, BaseLogDataService::TYPE_IN, printf('Start generate orderSuccessEmail for order#%s', $order->getId()));
        LogsService::sendLog($this->dataService->getMethodName(), $this->dataService->getData(), $this->dataService->getTypeStorageTime(), $this->dataService->getParams(),'', $this->dataService->getClassShortName(), __FUNCTION__, null);

        $templateDir = $this->parameterBag->get('email_template.order_delivered');
        $userFilesDir = $this->getUserFilesDirByWidgetId($order->getWidget()->getId());
        $http_host = str_replace('api.', '', $order->getPaymentData()['metadata']['http_host']);
        return $this->twig->render('@templates/' . basename($order->getWidget()->getMailTemplate()->getFilename()) . $templateDir, [
            'logo_dir' => $userFilesDir . $this->logoDir,
            'cover_dir' => $userFilesDir . $this->coverDir,
            'order' => $order,
            'template' => basename($order->getWidget()->getMailTemplate()->getFilename()),
            'http_host' => $http_host,
        ]);
    }

    private function getUserFilesDirByWidgetId(string $widgetId): string
    {
        return basename($this->userFilesDir) . '/' . hash('sha256', $widgetId) . '/' ;
    }
}
