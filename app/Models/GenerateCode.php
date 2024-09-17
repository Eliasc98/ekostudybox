<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivateCode;


class GenerateCode extends Model
{
    use HasFactory;

    protected $fillable = ['number_of_months','code_generated','number_of_users','platform','client_name','client_phone_number','client_email_address','client_remarks'];

    protected $table = 'generated_codes';

    public function activations(){
        return $this->hasOne(Activation::class);
    }
}
