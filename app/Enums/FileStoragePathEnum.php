<?php

namespace App\Enums;

enum FileStoragePathEnum: string
{
    case PdfTemplates = 'templates';
    case Certificates = 'certificates';
    case Zip = 'zip';
    case UserFiles = 'user_files';
    case LogoDir = '/logo/';
    case CoverDir = '/cover/';
    case CustomCoverDir = 'user';
    case FaviconDir = 'favicon';
    case SaleImageDir = 'sale-image';
    case BackgroundDir = '/background/';
    case CssDir = '/css/';
}
