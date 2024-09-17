<?php

namespace App\Models\Assessment;

use App\Models\Assessment\Category;
use App\Models\Assessment\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    protected $fillable = ['subjectname', 'category_id'];
}
