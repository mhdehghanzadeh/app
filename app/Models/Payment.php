<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Morilog\Jalali\Jalalian;
use DateTime;

class Payment extends Model
{
    use HasFactory;


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'result' => 'boolean',
        'verify' => 'boolean',
    ];

    protected $appends = ['user_name', 'status_label', 'transaction_id_number'];


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

    public function counseling()
    {
        return $this->belongsTo(Counseling::class, 'counseling_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Jalalian::forge($value)->format('H:i Y/m/d') : null;
    }

    public function getUserNameAttribute()
    {
        return $this->contact->first_name . ' ' . $this->contact->last_name;
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'هدایت به درگاه پرداخت';
                break;
            case 1:
                return 'پرداخت موفق';
                break;
            case 2:
                return 'پرداخت ناموفق';
                break;
            default:
                return '';
        }
    }

    public function getTransactionIdNumberAttribute()
    {
        return (int) filter_var($this->transaction_id, FILTER_SANITIZE_NUMBER_INT);
    }
}
