<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_Permission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';
    protected $fillable = ['id', 'role_id', 'permission_id' ];
    public $timestamps = false;

    public function permission_info()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
