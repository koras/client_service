<?php

namespace App\Models;

use App\Contracts\Models\SystemSettingInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model implements SystemSettingInterface
{
    use HasFactory;

    protected $table = 'system_setting';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'section',
        'name',
        'value'
    ];

    protected $casts = [
        'value' => 'array'
    ];

}
