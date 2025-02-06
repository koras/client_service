<?php

namespace App\Services;

use App\Contracts\Services\OrderSendingServiceInterface;
use App\Contracts\Services\ValidationServiceInterface;
use App\Contracts\Services\XlsParseServiceInterface;
use App\Enums\DeliveryTypeEnum;
use App\Traits\CleanDataTrait;
use Exception;
use Illuminate\Http\UploadedFile;

class OrderSendingService implements OrderSendingServiceInterface
{
    use CleanDataTrait;
    private const int HEADER_ROW_COUNT = 4;
    private const int MAX_ROW_COUNT = 50;
    private const int MAX_COLUMN_COUNT = 4;
    private const int MAX_NAME_LENGTH = 50;
    private const int MAX_MESSAGE_LENGTH = 600;

    private DeliveryTypeEnum $recipientTypeEnum;
    private array $validRows = [];
    private array $errorRows = [];

    public function __construct(
        private readonly ValidationServiceInterface $validationService,
        private readonly XlsParseServiceInterface $xlsParseService,
    )
    {
    }

    /**
     * @return array
     */
    public function getValidRows(): array
    {
        return $this->validRows;
    }

    /**
     * @return array
     */
    public function getErrorRows(): array
    {
        return $this->errorRows;
    }

    /**
     * Получить массив с данными из XLS файла для рассылки
     *
     * @param UploadedFile $file
     * @param DeliveryTypeEnum $recipientTypeEnum
     * @return bool
     * @throws Exception
     */
    public function importFromXls(UploadedFile $file, DeliveryTypeEnum $recipientTypeEnum): bool
    {
        $this->recipientTypeEnum = $recipientTypeEnum;
        $parsedData = $this->xlsParseService->parseOrderSenderXlsToArray(
            $file,
            self::MAX_ROW_COUNT,
            self::MAX_COLUMN_COUNT,
            self::HEADER_ROW_COUNT
        );

        $this->processingParsedData($parsedData);
        return empty($this->errorRows);
    }

    private function processingParsedData(array $parsedData): void
    {
        foreach ($parsedData as $i => $row) {
            $this->prepareRow($row, $i);
        }
    }

    private function prepareRow(array $row, int $index): void
    {
        // Если вся строка пустая, пропускаем ее
        if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
            return;
        }

        $rowError = $this->checkRowErrors($row);
        if (!empty($rowError)) {
            $this->errorRows[] = [
                'row' => $index + self::HEADER_ROW_COUNT + 1,
                'error' => $rowError
            ];
            return;
        }

        $recipient = $this->cleanRecipient($row[1]);
        if ($this->isDuplicateRecipient($recipient)) {
            $this->errorRows[] = [
                'row' => $index + self::HEADER_ROW_COUNT + 1,
                'error' => 'Продублирован получатель'
            ];
            return;
        }

        $this->validRows[] = [
            'index' => $index,
            'name' => $this->cleanString($row[0]),
            'recipient' => $recipient,
            'message' => $this->cleanString($row[2])
        ];
    }

    private function checkRowErrors(array $row): ?string
    {
        if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
            return 'Пустые ячейки в строке';
        }

        $nameError = $this->validationService->validateString($row[0], self::MAX_NAME_LENGTH);
        if ($nameError) {
            return "Имя: " . $nameError;
        }

        $messageError = $this->validationService->validateString($row[2], self::MAX_MESSAGE_LENGTH);
        if ($messageError) {
            return "Поздравление: " . $messageError;
        }

        $recipientError = $this->validateRecipient($row[1]);
        if ($recipientError) {
            return $recipientError;
        }

        return null;
    }

    private function cleanRecipient(string $recipient): string
    {
        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return $this->cleanString($recipient);
        }
        return $this->cleanPhone($recipient);
    }

    private function validateRecipient(string $recipient): ?string
    {
        return match ($this->recipientTypeEnum) {
            DeliveryTypeEnum::Email => $this->validationService->validateEmail($recipient),
            default => $this->validationService->validatePhoneNumber($recipient)
        };
    }

    private function isDuplicateRecipient(string $recipient): bool
    {
        $recipients = array_column($this->validRows, 'recipient');
        return in_array($recipient, $recipients);
    }



}