<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminContent extends Model
{
    use HasFactory;

    public function admin_topic(){
        return $this->belongsTo(AdminTopic::class);
    }

    public function questions(){
        return $this->hasMany(AdminQuestion::class);
    }

    protected $fillable = ['admin_topic_id', 'content'];
}
