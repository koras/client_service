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

class WidgetRemainderNotification
{
	protected Widget $widget;
	protected EntityManagerInterface $entityManager;
	protected NotificationServiceInterface $notificationService;
    protected BaseLogDataService $logDataService;

	protected string $hash;
	protected string $product;
	protected string $emailFrom;
	protected string $emailFromName;
	protected string $emailToNotification;
	protected string $type = 'widget_remainder';

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

	public function configure(Widget $widget, string $product): void
	{
		$this->widget = $widget;
		$this->product = $product;
		$this->hash =  hash('sha512', $this->widget.$this->product.$this->type);
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
					->subject($this->widget->getName() . '. Закончился товар.')
					->html('У виджета ' . $this->widget->getName() . ' закончился товар ' . $this->product);
                try {
                    $this->notificationService->send($email);
                } catch (TransportExceptionInterface $e) {
                    $msg = "Error for send Email: " . $e->getMessage();
                    $this->logDataService->prepareBaseData(__CLASS__, __FUNCTION__, 'WidgetRemainderNotification', LogLevel::ERROR,BaseLogDataService::TYPE_OUT, $msg);
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