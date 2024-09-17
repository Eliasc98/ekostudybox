<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\AdminContent;
use App\Models\AdminSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminTopic extends Model
{
    use HasFactory;

    public function admin_subject()
    {
        return $this->belongsTo(AdminSubject::class);
    }

    public function contents()
    {
        return $this->hasMany(AdminContent::class);
    }

    public function questions(){
        return $this->hasMany(AdminQuestion::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    protected $fillable = ['topics', 'week', 'admin_subject_id', 'author','num_of_test_questions','content_status', 'status', 'term_id'];

    protected $casts = [
        'num_of_test_questions' => 'integer',
    ];
}
