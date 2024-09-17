<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    use HasFactory;

    protected $fillable = ['test_type_name', 'duration', 'num_of_questions', 'subject_id', 'assoc_cat_id', 'admin_class_id', 'admin_id', 'passcode'];
}
