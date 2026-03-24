<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class OrganizationUser extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    protected $table = 'organization_user';

    protected $keyType = 'string';
}
