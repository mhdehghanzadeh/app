<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Morilog\Jalali\Jalalian;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['name', 'section_name'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('subject', 'like', '%' . $search . '%')
                ->orwhereHas('contact', function ($query) use ($search) {
                    return $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('unit_number', 'like', '%' . $search . '%')
                        ->orWhere('national_code', 'like', '%' . $search . '%');
                });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed) {
                $query->withTrashed();
            }
        })->when($filters['active'] ?? null, function ($query, $active) {
            if ($active == 'true') {
                $query->where('active', true);
            } else {
                $query->where('active', false);
            }
        })->when($filters['sort'] ?? null, function ($query, $sort) {
            if ($sort === 'old') {
                $query->orderBy('created_at', 'ASC');
            } elseif ($sort === 'new') {
                $query->orderBy('created_at', 'DESC');
            }
        }, function ($query) {
            $query->orderBy('created_at', 'DESC');
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket_messages()
    {
        return $this->hasMany(Ticket_Message::class, "ticket_id");
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Jalalian::forge($value)->format('H:i Y/m/d') : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Jalalian::forge($value)->format('H:i Y/m/d') : null;
    }

    public function getNameAttribute()
    {
        if($this->user->contact){
            return $this->user->contact->first_name . ' ' . $this->user->contact->last_name;
        }
        return $this->user->username;
    }

    public function getSectionNameAttribute()
    {
        switch ($this->section) {
            case 1:
                return 'بخش فنی';
                break;
            case 2:
                return 'بخش مالی';
                break;
            default:
                return 'بخش فنی';
        }
    }

}
