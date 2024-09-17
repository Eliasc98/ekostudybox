<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminLogin extends Model
{
    use HasFactory;

    public function admin()
    {
        return  $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
}
