<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    use HasFactory;

    public function searches()
    {
        return $this->hasMany(Search::class);
    }
}
