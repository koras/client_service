<?php

namespace App\Enums;

enum HttpMethodEnum: string
{
    case METHOD_POST = 'post';
    case METHOD_GET = 'get';
    case METHOD_PUT = 'put';
    case METHOD_DELETE = 'delete';
    case METHOD_PATCH = 'patch';

}
