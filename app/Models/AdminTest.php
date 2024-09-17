<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminTest extends Model
{
    use HasFactory;

    protected $fillable = ['test_type', 'duration', 'num_questions'];
}
