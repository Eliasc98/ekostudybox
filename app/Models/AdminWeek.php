<?php

namespace App\Models;

use Database\Seeders\AdminSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminWeek extends Model
{
    use HasFactory;

    public function admin_subject(){
        return $this->belongsTo(AdminSubject::class);
    }

    public function topics(){
        return $this->hasMany(AdminTopic::class);
    }

    protected $fillable = ['week_name', 'admin_subject_id'];
}
