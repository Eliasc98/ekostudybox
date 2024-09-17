<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Feedback;
use App\Models\PayInfo;
use App\Models\School;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'phone_number',        
        'student_code',
        'email',
        'password',
        'admin_class_id',
        'state',
        'referal_code',
        'school_id',
        'gender',
        'middlename',
        'assoc_cat_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function login(){
        return $this->hasMany(Login::class);
    }
    
    public function subjectOpening(){
        return $this->hasMany(SubjectOpening::class);
    }

    public function admin_class(){
        return $this->belongsTo(AdminClass::class);
    }

    public function school(){
        return $this->belongsTo(School::class);
    }

    public function userSubjectProgress(){
        return $this->hasMany(UserSubjectProgress::class);
    }

    public function feedback(){
        return $this->hasMany(Feedback::class);
    }

    public function pay_info(){
        return $this->hasMany(PayInfo::class);
    }
}
