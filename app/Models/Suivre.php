<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suivre extends Model
{   
    use HasFactory;
    protected  $fillable=[
        'etudiant_id',
        'note_id' ,
        'niveau_id' ,
        'annee_id' ];
    public function etudiant(){
        return $this->belongsTo(Etudiant::class);
    }
    public function note()
    {
        return $this->belongsTo(Note::class);
    }
   
}
