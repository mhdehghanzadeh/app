<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use \Morilog\Jalali\Jalalian;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('subject', 'like', '%' . $search . '%')
                ->orWhere('descriptions', 'like', '%' . $search . '%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed) {
                $query->withTrashed();
            } 
        })->when($filters['type'] ?? null, function ($query, $type) {
            if ($type === 'unread') {
                $query->select('notifications.*', 'notification_id', 'user_id')->leftJoin('notifications_seen','notifications.id','=','notifications_seen.notification_id')
                ->whereNull('notifications_seen.notification_id')
                ->whereNull('notifications_seen.user_id');
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

    public function getCreatedAtAttribute($value)
    {
        return $value ? Jalalian::forge($value)->format('H:i Y/m/d') : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Jalalian::forge($value)->format('H:i Y/m/d') : null;
    }
}
