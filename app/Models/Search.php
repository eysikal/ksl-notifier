<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use HasFactory;

    public function user()
    {
        $this->belongsTo(User::class);
    }

    public function frequency()
    {
        return $this->belongsTo(Frequency::class);
    }
}
