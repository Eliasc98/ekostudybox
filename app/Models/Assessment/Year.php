<?php

namespace App\Models\Assessment;

use App\Models\Assessment\Subject;
use App\Models\Assessment\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Year extends Model
{
    use HasFactory;

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    protected $fillable = ['subject_id', 'yearname'];
}
