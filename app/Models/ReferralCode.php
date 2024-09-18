<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = ['influencer_id', 'influencer_name', 'code'];

    // public function influencer()
    // {
    //     return $this->belongsTo(User::class, 'influencer_id');
    // }
}
