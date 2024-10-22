<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use DateTime;

class Counseling extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'result' => 'boolean',
        'answer' => 'boolean',
    ];

    protected $appends = ['date', 'enable', 'remaining'];


    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                ->orwhereHas('contact', function ($query) use ($search) {
                    return $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('unit_number', 'like', '%' . $search . '%')
                        ->orWhere('national_code', 'like', '%' . $search . '%');
                });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        })->when($filters['sort'] ?? null, function ($query, $sort) {
            if ($sort === 'old') {
                $query->orderBy('created_at', 'ASC');
            } elseif ($sort === 'new') {
                $query->orderBy('created_at', 'DESC');
            }
        }, function ($query) {
            $query->orderBy('created_at', 'DESC');
        })->when($filters['date'] ?? null, function ($query, $type) use ($filters) {
            if ($type === 'today') {
                $dt = new DateTime;
                $start = $dt->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $dt = new DateTime;
                $end = $dt->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($type === 'yesterday') {
                $dt = new DateTime;
                $start = $dt->modify('-1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                $dt = new DateTime;
                $end = $dt->modify('-1 day')->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($type === 'custom') {
                if (isset($filters['start']) && isset($filters['end'])) {
                    $dt = DateTime::createFromFormat('Y-m-d', $filters['start']);
                    $start = $dt->setTime(0, 0, 0)->format('Y-m-d H:i:s');
                    $dt = DateTime::createFromFormat('Y-m-d', $filters['end']);
                    $end = $dt->setTime(23, 59, 59)->format('Y-m-d H:i:s');
                    $query->whereBetween('created_at', [$start, $end]);
                }
            } 
        });
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    

    public function consultant()
    {
        return $this->belongsTo(Consultant::class, 'consultant_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'id', 'counseling_id');
    }

    public function getDateAttribute()
    {
        return $this->created_at ? Jalalian::forge($this->created_at)->format('H:i Y/m/d') : null;
    }

    public function getEnableAttribute()
    {
        $expire = Carbon::now()->diffInHours(Carbon::parse($this->created_at)) < 48 ? true : false ;
        /* if($expire && $this->active){
            return true;
        } */
        if($this->active){
            return true;
        }
        return false;
    }

    public function getRemainingAttribute()
    {
        return 42 - Carbon::now()->diffInHours(Carbon::parse($this->created_at));
    }
}
