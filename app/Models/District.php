<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;

class District extends Model
{
    use HasFactory;

    public function school(){
        return $this->hasMany(School::class);
    }
}
