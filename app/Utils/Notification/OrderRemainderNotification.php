<?php
namespace App\Utils\Notification;

use App\Entity\Widget;
use App\Services\LogService\BaseLogDataService;
use App\Services\LogService\LogsService;
use App\Services\Notifications\Contracts\NotificationServiceInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class OrderRemainderNotification
{
	protected Widget $widget;
	protected NotificationServiceInterface $notificationService;
    protected BaseLogDataService $logDataService;

	protected string $emailFrom;
	protected string $emailFromName;
	protected string $emailToNotification;

	public function __construct(
		string $emailFrom,
		string $emailFromName,
		string $emailToNotification,
		NotificationServiceInterface $notificationService,
        BaseLogDataService $logDataService,
	)
	{
		$this->emailFrom = $emailFrom;
		$this->emailFromName = $emailFromName;
		$this->emailToNotification = $emailToNotification;
		$this->notificationService = $notificationService;
        $this->logDataService = $logDataService;
	}

	public function send(
		Widget $widget,
		string $client,
		string $email,
		string $nominal,
		string $quantity,
		string $remainder): void
	{
		$emails = explode(',', $this->emailToNotification);
		foreach ($emails as $to) {
			$email = (new Email())
				->from(new Address($this->emailFrom, $this->emailFromName))
				->to($to)
				->subject($widget->getName() . '. Недостаточные остатки для заказа клиента.')
				->html("Имя клиента: $client </br>Email клиент: $email </br>Номинал: $nominal </br>Кол-во сертификатов в заказе: $quantity </br>Текущий остаток: $remainder");
            try {
                $this->notificationService->send($email);
            } catch (TransportExceptionInterface $e) {
                $msg = "Error for send Email: " . $e->getMessage();
                $this->logDataService->prepareBaseData(__CLASS__, __FUNCTION__, 'OrderRemainderNotification', LogLevel::ERROR,BaseLogDataService::TYPE_OUT, $msg);
                LogsService::sendLog($this->logDataService->getMethodName(), $this->logDataService->getData(), $this->logDataService->getTypeStorageTime(), $this->logDataService->getParams(),'', $this->logDataService->getClassShortName(), __FUNCTION__, null);
            }
        }
	}
}