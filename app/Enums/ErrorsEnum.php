<?php

namespace App\Enums;

enum ErrorsEnum: string
{
    case INTERNAL_SERVER_ERROR = 'Внутренняя ошибка сервера';
    case DATABASE_ERROR = 'Ошибка базы данных';
    case USER_NOT_FOUND = 'Пользователь не найден';
    case BAD_PASSWORD = 'Неверный пароль';
    case FILE_NOT_SAVED = 'Не удалось сохранить файл';
    case FILE_NOT_EXIST = 'Отсутствует файл';
    case FILE_FORMAT_INVALID = 'Неверный формат файла';
    case FILE_SIZE_EXCEEDED = 'Превышен максимальный размер файла';
    case INVALID_FIELDS = 'Некорректные поля в запросе';
    case INVALID_FIELD_MAX = 'Поле :attribute должно содержать меньше символов';
    case INVALID_FIELD_MIN = 'Поле :attribute должно содержать больше символов';
    case SYSTEM_ERROR = 'Системная ошибка';
    case REQUIRED_FIELDS_MISSING = 'Отсутствуют обязательные поля';
    case ALREADY_EXISTS = 'Уже существует';
    case EMAIL_NOT_VALID = 'Указан неправильный адрес электронной почты, адрес должен быть вида XXX@XXXX.XXX';
    case REQUEST_CODE_INVALID = 'Неверный код подтверждения';
    case REQUEST_CODE_ERROR = 'Не удалось проверить код подтверждения';
    case SEND_REQUEST_CODE_ERROR = 'Не удалось отправить код подтверждения';
    case TOKEN_INVALID = 'Неверный токен';
    case SENDER_ERROR = 'Ошибка при отправке сообщения';
    case FIELD_SIZE_EXCEEDED = 'Некорректная длина данных';
    case PERMISSION_DENIED = 'Доступ запрещен';
    case NOT_FOUND = 'Не найдено';
    case CERTIFICATE_NOT_FOUND = 'Сертификат не найден';
    case WIDGET_NOT_FOUND = 'Виджет на найден';
    case ORDER_NOT_FOUND = 'Заказ на найден';
    case INVALID_URL_FORMAT = 'Некорректный url';
    case MAXIMUM_ATTEMPTS_EXCEEDED = 'Превышено максимальное количество попыток';
    case USER_UPDATE_ERROR = 'Не удалось обновить пользователя';
    case DATA_PROCESSING_ERROR = 'Ошибка при обработке данных';
    case GENERATION_PDF_SERVICE_ERROR = 'Ошибка сервиса генерации PDF';
    case CREATE_ORDER_ERROR = 'Ошибка при создании заказа';
    case ORDER_STATUS_INVALID = 'Некорректный статус заказа для выполнения действия';
    case SEND_SUPPORT_ERROR = 'Ошибка при отправке сообщения в поддержку';
    case YOOKASSA_EMPTY_CALLBACK_OBJ = 'Пустой объект callback yookassa';
    case YOOKASSA_INVALID_EVENT_TYPE = 'Некорректный тип события для обработки';
    case UNAUTHENTICATE = 'Ошибка авторизации';
    case ERROR_SENDING_TO_PC = 'Ошибка отправки данных в ПЦ';
    case PROMO_CODE_NOT_FOUND = 'Промокод не найден';


    /**
     * Получить HTTP код ответа
     *
     * @return int
     */
    public function getRequestCode(): int
    {
        return match ($this) {
            self::DATABASE_ERROR => 503,

            self::TOKEN_INVALID,
            self::BAD_PASSWORD,
            self::UNAUTHENTICATE,
            => 401,

            self::FILE_NOT_SAVED,
            self::FILE_NOT_EXIST,
            self::FILE_FORMAT_INVALID,
            self::FILE_SIZE_EXCEEDED,
            self::INVALID_FIELDS,
            self::REQUIRED_FIELDS_MISSING,
            self::ALREADY_EXISTS,
            self::INVALID_FIELD_MAX,
            self::INVALID_FIELD_MIN,
            self::EMAIL_NOT_VALID,
            self::REQUEST_CODE_INVALID,
            self::REQUEST_CODE_ERROR,
            self::SENDER_ERROR,
            self::FIELD_SIZE_EXCEEDED,
            self::SEND_REQUEST_CODE_ERROR,
            self::INVALID_URL_FORMAT,
            self::MAXIMUM_ATTEMPTS_EXCEEDED,
            self::USER_UPDATE_ERROR,
            self::CREATE_ORDER_ERROR,
            self::ORDER_STATUS_INVALID,
            self::YOOKASSA_EMPTY_CALLBACK_OBJ,
            self::YOOKASSA_INVALID_EVENT_TYPE,
            => 422,

            self::USER_NOT_FOUND,
            self::NOT_FOUND,
            self::CERTIFICATE_NOT_FOUND,
            self::WIDGET_NOT_FOUND,
            self::ORDER_NOT_FOUND,
            => 404,

            self::PERMISSION_DENIED => 403,

            self::SYSTEM_ERROR => 400,
            default => 500,
        };
    }

    public static function tryFromCode(string $code): ?ErrorsEnum
    {
        foreach (self::cases() as $enum) {
            if ($enum->name === $code) {
                return $enum;
            }
        }

        return null;
    }
}
