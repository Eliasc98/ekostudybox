<?php

namespace App\Models;

use App\Models\Assessment\Subject;
use App\Models\Assessment\Year;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentTakeTest extends Model
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

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    protected $fillable = [
        'user_id', 'year_id', 'subject_id', 'assessment_test_taken_id',
        'num_question', 'correctly_answ', 'wrongly_answ'
    ];
}
