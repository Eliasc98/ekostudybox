<?php

namespace App\Models;

use App\Models\Assessment\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passage extends Model
{
    use HasFactory;

    public function question()
    {
        return $this->hasMany(Question::class);
    }
    
    protected $fillable = ['passage', 'year_id', 'test_id'];
}
