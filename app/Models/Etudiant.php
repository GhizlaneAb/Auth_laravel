<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;
    public function semestre()
    {
        return $this->belongsTo(Semestre::class,);
    }
    public function noteEtudiant()
    {
        return $this->hasMany(Suivre::class);
    }
}
