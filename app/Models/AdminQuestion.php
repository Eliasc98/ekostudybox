<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminQuestion extends Model
{
    use HasFactory;
    public function admin_topic(){
        return $this->belongsTo(AdminTopic::class);
    }    

    protected $fillable = ['admin_topic_id','questionText','explanation', 'image', 'optionA', 'optionB', 'optionC', 'optionD', 'optionE', 'correct_option'];
}
