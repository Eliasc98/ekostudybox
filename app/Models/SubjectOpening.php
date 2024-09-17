<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_subject_id',
        'opened_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin_subject()
    {
        return $this->belongsTo(AdminSubject::class);
    }
}