<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestScheduler extends Model
{
    use HasFactory;

    protected $fillable = ['test_type_id', 'admin_id', 'school_id', 'start_date', 'end_date'];
}
