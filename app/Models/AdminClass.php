<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminClass extends Model
{
    use HasFactory;

    protected $fillable = ['class_name', 'status', 'class_level', 'author'];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function subjects()
    {
        return $this->hasMany(AdminSubject::class);
    }
}
