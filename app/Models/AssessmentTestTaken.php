<?php

namespace App\Models;

use App\Models\User;
use App\Models\Assessment\TestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentTestTaken extends Model
{
    use HasFactory;

    public function test_type() {
        return $this->belongsTo(TestType::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }

    protected $fillable = ['user_id', 'test_type_id', 'year_id', 'subject_id', 'num_question'];
}
