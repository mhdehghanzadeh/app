<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    

    public function consultant()
    {
        return $this->belongsTo(Consultant::class, 'consultant_id');
    }
}
