<?php

namespace App\Models;

use App\Contracts\Models\WidgetUserInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetUser extends Model implements WidgetUserInterface
{
    use HasFactory;

    protected $table = 'widget_user';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'last_login', 'roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'roles' => 'json',
    ];

}
