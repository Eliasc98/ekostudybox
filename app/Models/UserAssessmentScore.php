<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssessmentScore extends Model
{
    use HasFactory;

    public function user_assessment_marking()
    {
        return $this->belongsTo(UserAssessmentMarking::class);
    }

    protected $fillable = ['assessment_test_taken_id',   'score'];
}
