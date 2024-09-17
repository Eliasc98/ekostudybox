<?php

namespace App\Models;

use App\Models\PayInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public function pay_info(){
        return $this->belongsTo(PayInfo::class);
    }
    
    protected $fillable = ['pay_info_id', 'user_id', 'expiry_date', 'subscription_type'];
}
