<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStudyMarking extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin_topic()
    {
        return $this->belongsTo(AdminTopic::class);
    }

    public function admin_question(){
        return $this->belongsTo(AdminQuestion::class);
    }

    protected $fillable = ['user_id', 'admin_topic_id', 'admin_question_id', 'selected_option', 'correct_option'];
}
