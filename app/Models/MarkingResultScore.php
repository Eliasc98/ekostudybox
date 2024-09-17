<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkingResultScore extends Model
{
    use HasFactory;

    public function user_study_marking(){
        return $this->belongsTo(UserStudyMarking::class);
    }

    protected $fillable = ['user_study_marking_id', 'score'];
}
