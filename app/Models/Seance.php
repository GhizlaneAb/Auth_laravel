<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;
       public function salle(){

        return $this->belongsTo(Salle::class);
    } 
    
}
