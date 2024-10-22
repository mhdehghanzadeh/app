<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Storage;

class Ticket_Message extends Model
{
    use HasFactory;

    protected $table = 'ticket_messages';

    protected $appends = ['sender', 'date', 'hour', 'timestamp'];


     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSenderAttribute()
    {
        if($this->user_id == auth()->user()->id){
            return true;
        }
        return false;
    }

    public function getFileAttribute($value)
    {
        if($value){
            return Storage::url($value);
        }
    }

    public function getDateAttribute()
    {
        return Jalalian::forge($this->created_at)->format('Y/m/d');
    }

    public function getHourAttribute()
    {
        return Jalalian::forge($this->created_at)->format('H:i');
    }

    public function getTimestampAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at);
    }

}
