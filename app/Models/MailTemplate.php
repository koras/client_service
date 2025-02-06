<?php

namespace App\Models;

use App\Contracts\Models\MailTemplateInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model implements MailTemplateInterface
{
    use HasFactory;

    protected $table = 'mail_template';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 'filename', 'is_enabled', 'sort_order',
    ];
}
