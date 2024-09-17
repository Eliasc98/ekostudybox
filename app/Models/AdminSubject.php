<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSubject extends Model
{
    use HasFactory;

    public function admin_class(){
        return $this->belongsTo(AdminClass::class);
    }

    public function topics(){
        return $this->hasMany(AdminTopic::class, 'admin_subject', 'id');
    }    

    protected $fillable = ['subject_name', 'subject_img','admin_class_id'];
}
