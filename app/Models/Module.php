<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
    public function prof()
    {
        return $this->belongsTo(Prof::class);
    }
    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }
    public function etudiants()
    {
        return $this->hasMany(Etudiant::class,'semestre_id', 'semestre_id');
    }
    
}
