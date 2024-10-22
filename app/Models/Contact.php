<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sex' => 'boolean',
    ];

    protected $appends = ['name', 'gender', 'gender_pronouns','age'];


    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('unit_number', 'like', '%' . $search . '%')
                    ->orWhere('national_code', 'like', '%' . $search . '%')
                    ->orwhereHas('user', function ($query) use ($search) {
                        return $query->where('phone', 'like', '%' . $search . '%');
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
        });
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    
    public function getGenderPronounsAttribute()
    {
        if($this->sex){
            return 'آقای';
        }else{
            return 'خانم';
        }
    }

    public function getGenderAttribute()
    {
        if($this->sex){
            return 'مذکر';
        }else{
            return 'مؤنث';
        }
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->birthday)->age;
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function counselings()
    {
        return $this->hasMany(Counseling::class, "contact_id");
    }

    public function images()
    {
        return $this->hasMany(Contact_Image::class, "contact_id");
    }

    
}
