<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Contact_Image extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contact_images';

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function getImageAttribute($value)
    {
        if($value){
            return Storage::url($value);
        }
    }
}
