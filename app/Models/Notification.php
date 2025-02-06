<?php

namespace App\Models;

use App\Contracts\Models\NotificationInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model implements NotificationInterface
{
    use HasFactory;
    protected $table = 'notification';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'hash', 'status',
    ];

    // Add relationships or additional methods if needed
}
