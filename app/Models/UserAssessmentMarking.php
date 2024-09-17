<?php

namespace App\Models;

use App\Models\Assessment\Year;
use App\Models\Assessment\Subject;
use App\Models\Assessment\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAssessmentMarking extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    protected $fillable = ['user_id', 'year_id', 'subject_id', 'question_id','assessment_test_taken_id', 'selected_option', 'correct_option'];
}
