<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetUserWidget extends Model
{
    use HasFactory;

    protected $table = 'widget_user_widget';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'widget_user_id', 'widget_id',
    ];

    // Add relationships or additional methods if needed
}
