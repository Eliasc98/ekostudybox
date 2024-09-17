<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GenerateCode;

class Activation extends Model
{
    use HasFactory;

    protected $fillable = ['number_of_used','number_of_users','name_of_client','current_date','expiry_date', 'generated_code_id'];

    protected $table = 'activations';

    public function generated_code(){
        return $this->belongsTo(GenerateCode::class, 'id');
    }
}
