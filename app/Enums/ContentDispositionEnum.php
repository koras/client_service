<?php

namespace App\Enums;

enum ContentDispositionEnum: string
{
    case ATTACHMENT = 'attachment';
    case INLINE = 'inline';

    /**
     * Получить значение по признаку - isDownload
     *
     * @param bool $isDownload
     * @return self
     */
    public static function getByIsDownloadFlag(bool $isDownload): self
    {
        return $isDownload ? self::ATTACHMENT : self::INLINE;
    }

}

