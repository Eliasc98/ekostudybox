<?php

namespace App\Models\Assessment;

use App\Models\Passage;
use App\Models\Assessment\Year;
use App\Models\Assessment\Subject;
use App\Models\Assessment\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function passage()
    {
        return $this->belongsTo(Passage::class);
    }
    
    protected $fillable = ['subject_id', 'test_type_id', 'school_id','category_id','year_id', 'passage_id','questionText', 'image', 'optionA', 'optionB', 'optionC', 'optionD', 'optionE', 'correct_option', 'explanation'];

}
