<?php
namespace App\Utils\Notification;

use App\Entity\Notification;
use App\Entity\Widget;
use App\Services\LogService\BaseLogDataService;
use App\Services\LogService\LogsService;
use App\Services\Notifications\Contracts\NotificationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class PaymentRemainderNotification
{
	protected Widget $widget;
	protected EntityManagerInterface $entityManager;
	protected NotificationServiceInterface $notificationService;
    protected BaseLogDataService $logDataService;

	protected string $hash;
	protected string $emailFrom;
	protected string $emailFromName;
	protected string $emailToNotification;
	protected string $type = 'payment_remainder';

	protected string $order_id;
	protected string $tiberium_order_id;
	protected string $error;

	public function __construct(
		string $emailFrom,
		string $emailFromName,
		string $emailToNotification,
		EntityManagerInterface $entityManager,
        NotificationServiceInterface $notificationService,
        BaseLogDataService $logDataService,
	)
	{
		$this->emailFrom = $emailFrom;
		$this->emailFromName = $emailFromName;
		$this->emailToNotification = $emailToNotification;
		$this->entityManager = $entityManager;
		$this->notificationService = $notificationService;
        $this->logDataService = $logDataService;
	}

	public function configure(Widget $widget, string $order_id, string $tiberium_order_id, string $error): void
	{
		$this->widget = $widget;
		$this->order_id = $order_id;
		$this->tiberium_order_id = $tiberium_order_id;
		$this->error = $error;
		$this->hash = hash('sha512', $this->widget.$this->order_id.$this->tiberium_order_id.$this->type);
	}

	public function commit(): void
	{
		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy(['hash' => $this->hash]);

		if (null === $notification || $notification->getStatus() === 0) {
			$emails = explode(',', $this->emailToNotification);
			foreach ($emails as $to) {
				$email = (new Email())
					->from(new Address($this->emailFrom, $this->emailFromName))
					->to($to)
					->subject($this->widget->getName() . '. Неудалось подтвердить оплату.')
					->html("Номер заказа виджета: $this->order_id </br> Номер заказа тибериума: $this->tiberium_order_id </br> Текст ошибки: $this->error");

                try {
                    $this->notificationService->send($email);
                } catch (TransportExceptionInterface $e) {
                    $msg = "Error for send Email: " . $e->getMessage();
                    $this->logDataService->prepareBaseData(__CLASS__, __FUNCTION__, 'PaymentRemainderNotification', LogLevel::ERROR,BaseLogDataService::TYPE_OUT, $msg);
                    LogsService::sendLog($this->logDataService->getMethodName(), $this->logDataService->getData(), $this->logDataService->getTypeStorageTime(), $this->logDataService->getParams(),'', $this->logDataService->getClassShortName(), __FUNCTION__, null);
                }
			}
			if (null === $notification) {
				$notification = new Notification($this->hash);
			}

			$notification->setStatus(1);

			$this->entityManager->persist($notification);
			$this->entityManager->flush();
		}
	}

	public function rollback(): void
	{
		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy(['hash' => $this->hash]);
		if (null === $notification) {
			$notification = new Notification($this->hash);
		}
		$notification->setStatus(0);

		$this->entityManager->persist($notification);
		$this->entityManager->flush();
	}
}