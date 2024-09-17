<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTopicProgress extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'admin_topic_id',
        'admin_subject_id',
        'completed_percentage',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function admin_topic(){
        return $this->belongsTo(AdminTopic::class);
    }

    public function admin_subject(){
        return $this->belongsTo(AdminSubject::class);
    }
}
