<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification_Seen extends Model
{
    use HasFactory;
    protected $table = 'notifications_seen';
    public $timestamps = false;
}
