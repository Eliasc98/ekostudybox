<?php

namespace App\Models\Assessment;

use App\Models\Assessment\Subject;
use App\Models\Assessment\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    public function subject(){
        return $this->hasMany(Subject::class);
    }

    public function question(){
        return $this->hasMany(Question::class);
    }

    protected $fillable = ['cat_name'];
}
