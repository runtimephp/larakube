<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum RbacVerb: string
{
    case All = '*';
    case Create = 'create';
    case Delete = 'delete';
    case Get = 'get';
    case List = 'list';
    case Patch = 'patch';
    case Update = 'update';
    case Watch = 'watch';
}
