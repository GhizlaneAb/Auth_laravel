<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Prof;
use App\Models\Admin;
use App\Models\Module;
use App\Models\Suivre;
use App\Models\Student;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class authController extends Controller
{

    //Test on JWT Authentication
    public function login(Request $req){
        $credentials=$req->only(['login','password']);
        $token=Auth::guard('admin-api')->attempt($credentials);
        if(!$token){
            return response([
                'message'=>'Invalid credentials'
            ],status:Response::HTTP_UNAUTHORIZED);
        }

        $admin=Auth::guard('admin-api')->user();
        
        return $admin;


    }

    public function admin(){

          return Auth::user();
    }

    //Here the end of the test
    //Test on JWT Authentication for student just another test
    public function logins(Request $req){
        $credentials=$req->only(['email','password']);
        $token=Auth::guard('student-api')->attempt($credentials);
        if(!$token){
            return response([
                'message'=>'Invalid credentials'
            ],status:Response::HTTP_UNAUTHORIZED);
        }

        $admin=Auth::guard('student-api')->user();
        
        return $admin;


    }

    public function student(){

          return Auth::user();
    }

    //Here the end of the test

    function auth(Request $request)
    {
        $etudiant=NULL;
        $prof=Null;
        $admin=Null;
        if($request->role =="etudiant"){
           $etudiant= Etudiant::where([["email","=",$request->email],['date de naissance', "=", $request->date]])->first();
           
        }else
           { $prof= Prof::where([["login","=",$request->login],['password', "=", $request->password]])->first();
        $admin = Admin::where([["login", "=", $request->login], ['password', "=", $request->password]])->first();
       } 
       if($etudiant != Null){
            return [
                'role' => 'etudiant',
                'logged'=>'success',
                'email'=> $request->email,
                'date'=> $request->date,
                'id' => $etudiant->id
            ];}

        else if ($prof != Null) {
            return [
                'role' => 'prof',
                'logged' => 'success',
                'login' => $request->login,
                'password' => $request->password,
                'id' => $prof->id
            ];
        }


       else  if ($admin != Null) {
            return [
                'role' => 'admin',
                'logged' => 'success',
                'login' => $request->login,
                'password' => $request->password,
                'id' => $admin->id
            ];
        }

 else{
            return [
                'logged' => 'failed',
            ];
 }
    }

    public function getEmploi(Request $request)
    {
        /* $etudiant = Etudiant::where([['email', '=', $request->email],[ 'date de naissance', '=', $request->date]])->get(); */
      if($request->role=="etudiant")
       { $etudiant = Etudiant::where('id',$request->id)->get();
        $emploit = new emploit();
        $emploit->setUtilisateur($etudiant[0]);
        
       $semestres= $emploit->getSemestre();} 
        else if($request->role == "prof"){
            $prof_ = Prof::where('id', $request->id)->get();
            $prof = Module::where(
                [
                    ['prof_id', '=', $prof_[0]->id],
                ]
            )->get();

            $emploit = new emploit_prof();
            $emploit->setUtilisateur($prof);
            $semestres = $emploit->getSemestre($request->semestres);
        } else if ($request->role == "admin") {
            $etudiant = Etudiant::where('semestre_id', $request->semestre_id)->first();
            $emploit = new emploit();
            $emploit->setUtilisateur($etudiant);
            $semestres = $emploit->getSemestre();
        } 
        return  [
            'nom' => $emploit->getNom(),
            'semestre' => $semestres,
            'ModuleLundiMatin' => $emploit->getModuleLundiMatin(),
            'ModuleLundiSoire' =>  $emploit->getModuleLundiSoire(),
            'ModuleMardiMatin' =>  $emploit->getModuleMardiMatin(),
            'ModuleMardiSoire' =>  $emploit->getModuleMardiSoire(),
            'ModuleMercrediMatin' => $emploit->getModuleMercrediMatin(),
            'ModuleMercrediSoire' => $emploit->getModuleMercrediSoire(),
            'ModuleJeudiMatin' => $emploit->getModuleJeudiMatin(),
            'ModuleJeudiSoire' => $emploit->getModuleJeudiSoire(),
            'ModuleVendrediMatin' => $emploit->getModuleVendrediMatin(),
            'ModuleVendrediSoire' => $emploit->getModuleVendrediSoire(),
            'ModuleSamediMatin' => $emploit->getModuleSamediMatin(),
            'ModuleSamediSoire' => $emploit->getModuleSamediSoire(),
        ];
    }
   
   public function getNoteEtudiant(Request $request){
    
    $etudiant = Suivre::where('etudiant_id','=',$request->id)->get();
    $Notes = array();
    $Modules= array();
    $semestres=array();
    foreach($etudiant as $etu ){
            array_push($Notes,$etu->note);
            array_push($Modules, $etu->note->module->module);
            array_push($Modules, $etu->note->module->semestre);

    }
    return ["notes" => $Notes];
   }
    public function getNoteProf(Request $request)
    {

        $modules = Module::where("prof_id",$request->id)->get();
        
        $Modules = array();
        $etudiants= array();
        $notes= array();
        foreach ($modules as $mod) {
            array_push($Modules,$mod);
            foreach ($mod->etudiants as $etu) {
                if($etu->noteEtudiant!=null )
                {array_push($notes, $etu->noteEtudiant);
                    
                
                   
                    foreach ($etu->noteEtudiant as $note) {
                        if ($note->note != null&& $mod->id == $note->note->module_id ) {
                            array_push($etudiants, (object) array('module_id'=>$mod->id,'etudiant_id' => $etu->id, 'note' => $note->note->note));
                            array_push($notes, $note->note);
                        }
                    }
                
                }
            }       
        } 

        return ["modules" => $Modules,
               /*  "notes"=>$notes, */
                "notes_etudiant"=>$etudiants
                    ];
    }
    public function saveNoteProf(Request $request)
    {  
      $modules = Module::all();
      $etudiants = Etudiant::all();

       //chercher le module et l'etudiant d'apres le module_etudiant key
        $semestre="";
        $module_id="";
        $etudiant_id = "";
        $module = "";
        /*  $abs= array(); */
       foreach($modules as $m){
         foreach( $etudiants  as $e){
        /*      array_push($abs, $m->module . $e->id); */
         if($m->module.$e->id == $request->module_etudiant){
             $found="success";
             $etudiant_id= $e->id;
             $module= $m->module;
             $module_id= $m->id;
             $semestre= $m->semestre_id;
         }
     }
     }
     //definir le niveau apartire des semestres
if ($semestre == "1" || $semestre == "2" ){
          $niveau_id="1";
} else if ($semestre == "3" || $semestre == "4") {
            $niveau_id = "2";
        } else if ($semestre == "5" || $semestre == "6") {
            $niveau_id = "3";
        }


     //chercher si la note existe dans la table note

    $found_note= Note::where([
     ['note','=', $request->note],
     ['module_id','=', $module_id]
     ])->get();

        //chercher l'etudiant a une note deja dans ce module

        $suivre_etudiant = Suivre::where([
            ['etudiant_id', '=', $etudiant_id]
        ])->get();
     $found_etudiant = null;
foreach($suivre_etudiant as $etudiant){

      $note_exist =  Note::where('module_id',$module_id)->get(); 
        
      foreach($note_exist as $note){ 
                    if ($note->id == $etudiant->note_id) {
                        $found_etudiant = $etudiant;
                    }
                
      }
        
    }
//si la nouvelle  note de module existe
    $note_id ="";
     if(!$found_note->isEmpty()){
            $note_id = $found_note[0]->id;

        }
       else {
 //si non on cree la note          
            $note_id =  DB::table('notes')->insertGetId([
                'note' => $request->note,
                'module_id' => $module_id
            ]);
        }

    
       
 if($found_etudiant==null ){
    // ajoute  note et niveau et annee au etudiant si il n'a pas une note dans ce module
            Suivre::create([
                'etudiant_id' => $etudiant_id,
                'note_id' => $note_id,
                'niveau_id' =>  $niveau_id,
                'annee_id' => "1"
            ]);          
}else  {
            //update si il a deja une note dans ce module
            Suivre::where("id",$found_etudiant->id)->update([
                'note_id' => $note_id
            ]);
        } 



        return ["note" => $request->note,
            "module_etudiant" => $request->module_etudiant,
            "etudiant_id"=> $etudiant_id,
            "module"=>$module,
            "suivre"=> $suivre_etudiant
        ];
    }
}
class  emploit
{
    private $etudiant;

    public function setUtilisateur($etudiant)
    {
        $this->etudiant = $etudiant;
    }
    public function getNom()
    {
        return $this->etudiant->nom;
    }
    public function getSemestre()
    {

        return $this->etudiant->semestre->semestre;
    }
    public function getModuleLundiMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {

            if ($module->seance->jour == "lundi" && $module->seance->heure == "9:00h -> 12:45h") {

                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleLundiSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "lundi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMardiMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "mardi" && $module->seance->heure == "9:00h -> 12:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMardiSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "mardi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleMercrediMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "mercredi" && $module->seance->heure == "9:00h -> 12:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMercrediSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "mercredi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleJeudiMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "jeudi" && $module->seance->heure == "9:00h -> 12:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleJeudiSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "jeudi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleVendrediMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "vendredi" && $module->seance->heure == "9:00h -> 12:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleVendrediSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "vendredi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleSamediMatin()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "samedi" && $module->seance->heure == "9:00h -> 12:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleSamediSoire()
    {
        $the_module = " ";
        foreach ($this->etudiant->semestre->module as $module) {
            if ($module->seance->jour == "samedi" && $module->seance->heure == "15:00h -> 18:45h") {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
}
class  emploit_prof
{
    private $prof;
    private $S1;
    private $S2;
    private $S3;
    

    public function setUtilisateur($prof)
    {
        $this->prof = $prof;
    }
    public function getNom()
    {
        return $this->prof[0]->prof->nom;
    }
    public function getSemestre($semestres)
    {
        $s1="";
        $s2="";
        $s3="";
         if($semestres=="1"){
        $this->S1= 'S1';
        $this->S2 = 'S3';
        $this->S3 = 'S5';
         }else{
            $this->S1 = 'S2';
            $this->S2 = 'S4';
            $this->S3 = 'S6';  
         }
        foreach ($this->prof as $module) {

            if ($module->semestre->semestre == $this->S1)
              {  $s1  =  $module->semestre->semestre;}
            if ($module->semestre->semestre == $this->S2)
               { $s2  =  $module->semestre->semestre;}
            if ($module->semestre->semestre == $this->S3)
               { $s3  =  $module->semestre->semestre;}
        }

        return $s1 . " " . $s2 . " " . $s3 ;
    }
    public function getModuleLundiMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "lundi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3))
             {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleLundiSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "lundi" && $module->seance->heure == "15:00h -> 18:45h"&& ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMardiMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "mardi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMardiSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "mardi" && $module->seance->heure == "15:00h -> 18:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleMercrediMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "mercredi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleMercrediSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "mercredi" && $module->seance->heure == "15:00h -> 18:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleJeudiMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "jeudi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleJeudiSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "jeudi" && $module->seance->heure == "15:00h -> 18:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleVendrediMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "vendredi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleVendrediSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "vendredi" && $module->seance->heure == "15:00h -> 18:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
    public function getModuleSamediMatin()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "samedi" && $module->seance->heure == "9:00h -> 12:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }

    public function getModuleSamediSoire()
    {
        $the_module = " ";
        foreach ($this->prof as $module) {
            if ($module->seance->jour == "samedi" && $module->seance->heure == "15:00h -> 18:45h" && ($module->semestre->semestre == $this->S1 || $module->semestre->semestre == $this->S2 || $module->semestre->semestre == $this->S3)) {
                $the_module =  $the_module . $module->module;
            }
        }
        return $the_module;
    }
}