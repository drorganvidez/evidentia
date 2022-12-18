<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Proof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Evidence;
use App\Models\User;
use App\Models\Role;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\Comittee;
use App\Models\Secretary;

class LectureDashboardController extends Controller {


    public function view($instace){

        $instance = \Instantiation::instance();

        /*====================================================================================

            DATOS GENERALES DE LAS JORNADAS

        ====================================================================================*/


        /*Usuarios totales DENTRO DE LA VISTA Y ARREGLADO ===========*/
        
        $total_users = $this->getTotalUsers();

        /*Evidencia totales DENTRO DE LA VISTA Y ARREGLADO ========*/

        $total_evidences_not_draft_count = Evidence::evidences_not_draft()->count();
        
        /*Evidencias medias por persona DENTRO DE LA VISTA Y ARREGLADO ========*/
        if( !($total_evidences_not_draft_count <= 0)){
            $evidences_per_user = $total_evidences_not_draft_count / $total_users;
        } else {
            $evidences_per_user = 0;
        }
        

        /*Evidencias en un rango X de horas (entre 0 y 1,1 y 2, 3 y 5) DENTRO DE LA VISTA Y ARREGLADO ==========*/
        $dict_evidences_hours = $this->getEvidencesInRange();

        /* Numero de archivos subidos a las evidencias DENTRO DE LA VISTA ===========*/
        $total_files = $this->getTotalFiles();
        
        /*Media de archivo por evidencia 1 linea DENTRO DE LA VISTA =========*/
        if( !($total_evidences_not_draft_count <= 0)){
            $mean_evidence_proof = $total_files / $total_evidences_not_draft_count;
        } else {
            $mean_evidence_proof = 0;
        }

        /*Evidencias en un rango X archivos (0,1,2,3 o mas) 14 lineas DENTRO DE LA VISTA Y ARREGLADO ==============*/
        $dict_evidences_proof_ranges = $this->getProofRanges();

        /*Peso total de los archivos subidos DENTRO DE LA VISTA Y ARREGLADO ==========*/
        $total_weight = $this->totalWeight();
        $total_weight_reformed = $this->getSize($total_weight);

        /*Peso medio de archivos por evidencia DENTRO DE LA VISTA Y ARREGLADO =========*/
        if( !($total_evidences_not_draft_count <= 0)){
            $mean_evidences_proof_weight = $this->getSize($total_weight / $total_evidences_not_draft_count);
        } else{
            $mean_evidences_proof_weight = 0;
        }
        /*Reuniones totales DENTRO LA VISTA Y ARREGLADO ========= */
        $total_meetings = Meeting::all()->count();       

        /*Tiempo total de reunion DENTRO LA VISTA Y ARREGLADO ========*/
        $total_time_meetings = $this->getTotalTimeMeetings();

        /*Tiempo medio de cada reunion DENTRO LA VISTA Y ARREGLADO*/
        if( !($total_meetings <= 0)){
            $meen_time_meetings = $total_time_meetings/$total_meetings;
        }else{
            $meen_time_meetings = 0;
        }
        /* ====================================================================================

            DATOS PARA CADA COMITE

        ====================================================================================*/

        /*Nombres de cada comite DENTRO DE LA VISTA Y ARREGLADO*/

        $comite_name = $this->getComiteNames();    

        /*Numero evidencias por comite 21 Lineas DENTRO DE LA VISTA Y ARREGLADO*/

        $evidence_per_comite = $this->getEvidencesComite();

        /*Peso de archivos por comite DENTRO DE LA VISTA Y ARREGLADO*/

        $comite_weight = $this->getComiteProofWeight();

        /*Reuniones por cada comite DENTRO DE LA VISTA Y ARREGLADO*/

        $comite_meetings = $this->getComiteMeetings();

        /*Numero de Secretarios por comite 20 Lineas DENTRO DE LA VISTA Y ARREGLADO*/

        $secretarios_por_comite = $this->getSecretariosComite();


        return view('dashboard.view',['instance'=> $instance, 'total_evidences_not_draft'=>$total_evidences_not_draft_count, 
        'total_users' => $total_users, 'evidences_per_user' => $evidences_per_user, 'total_files' => $total_files, 'dict_evidences_hours' => $dict_evidences_hours,
        'mean_evidence_proof' => $mean_evidence_proof, 'total_weight' => $total_weight_reformed, 'mean_evidences_proof_weight' => $mean_evidences_proof_weight,
        'secretarios_comite' => $secretarios_por_comite, 'comite_name' => $comite_name, 'evidences_per_comite' => $evidence_per_comite,
        'evidences_proof_range' => $dict_evidences_proof_ranges, 'comite_weight' => $comite_weight, 'total_meetings' => $total_meetings, 
        'total_time_meetings' => $total_time_meetings, 'meen_time_meetings' => $meen_time_meetings, 'meetings_comite' => $comite_meetings
        ]);
    }

    /*====================================================================================

        FUNCIONES AUXILIARES USADAS PARA CALCULAR ESTADISTICAS

    ====================================================================================*/    


    public function getSize($sizeToConvert){
        $KB = 1024;
        $MB = pow(1024,2);
        $GB = pow(1024,3);
        if($sizeToConvert>=$GB){
            $sizeReformed = round($sizeToConvert/$GB,2)." GB";
        }else if($sizeToConvert>=$MB){
            $sizeReformed = round($sizeToConvert/$MB,2)." MB";
        }
        else if($sizeToConvert>=$KB){
            $sizeReformed = round($sizeToConvert/$KB,2)." KB";
        } else {
            $sizeReformed = $sizeToConvert." KB";
        }

        return $sizeReformed;
    }

    public function getTotalUsers(){
        $total_users = User::all();
        $total_users_filtered = 0;
        foreach($total_users as $users){
            if(!($users->hasRole('LECTURE'))){
                $total_users_filtered = $total_users_filtered +1;
            }
        }

        return $total_users_filtered;
    }

    /*Funcion para las evidencias en un rango especifico*/

    public function getEvidencesInRange(){
        $evidences_not_draft = Evidence::evidences_not_draft();
        
        for($i=0; $i<4;$i++){ 
            $dict_evidences_hours[$i] = 0;
        }

        foreach ($evidences_not_draft as $evidence) {
            $hours = $evidence->hours;
            if($hours < 1){
                $dict_evidences_hours[0] = $dict_evidences_hours[0] + 1;
            } else if ($hours >= 1 and $hours < 2){
                $dict_evidences_hours[1] = $dict_evidences_hours[1] + 1;
            } else if ($hours >= 2 and $hours < 3){
                $dict_evidences_hours[2] = $dict_evidences_hours[2] + 1;
            } else if ($hours >= 1 and $hours < 2){
                $dict_evidences_hours[3] = $dict_evidences_hours[3] + 1;
            }
        }

        return $dict_evidences_hours;
        
    }

    public function getTotalFiles(){
        $total_files = 0;
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach ($total_evidences_not_draft as $evidence) {
           foreach($evidence->proofs as $proof){
                $total_files = $total_files + 1;
           }
        }

        return $total_files;
    }

    public function getProofRanges(){
        for($i = 0; $i<4;$i++){ 
            $dict_evidences_proof_ranges[$i] = 0;
        }
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach ($total_evidences_not_draft as $evidence) {
            $number_proofs = 0;
            foreach($evidence->proofs as $proof){
                $number_proofs = $number_proofs + 1;
            }
            if($number_proofs < 1 and $number_proofs >= 0){
                $dict_evidences_proof_ranges[0] = $dict_evidences_proof_ranges[0] + 1;
            } else if ($number_proofs >= 1 and $number_proofs < 2){
                $dict_evidences_proof_ranges[1] = $dict_evidences_proof_ranges[1] + 1;
            } else if ($number_proofs >= 2 and $number_proofs < 3){
                $dict_evidences_proof_ranges[2] = $dict_evidences_proof_ranges[2] + 1;
            } else if ($number_proofs >= 3){
                $dict_evidences_proof_ranges[3] = $dict_evidences_proof_ranges[3] + 1;
            }
        }

        return  $dict_evidences_proof_ranges;
    }


    public function totalWeight(){
        $total_weight = 0;
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach($total_evidences_not_draft as $evidence){
            foreach($evidence->proofs as $proof){
                $size = $proof->file->size;
                $total_weight = $total_weight + $size;
            }
        }

        return $total_weight;
    }

    public function getTotalTimeMeetings(){
        $total_time_meetings = 0; /*ESTE VALOR PONERLO A 0, ES UNA PRUEBA*/
        $all_meetings = Meeting::all();
        foreach($all_meetings as $meeting){
            $total_time_meetings = $total_time_meetings + $meeting->hours;
        }
        return $total_time_meetings;
    }


    public function getComiteNames(){
        $comite_name[0] = "Presidencia";
        $comite_name[1] = "Secretaría";
        $comite_name[2] = "Programa";
        $comite_name[3] = "Igualdad";
        $comite_name[4] = "Sostenibilidad";
        $comite_name[5] = "Finanzas";
        $comite_name[6] = "Logistica";
        $comite_name[7] = "Comunicación";

        return $comite_name;
    }
   
    public function getEvidencesComite(){
        for($i=0; $i<8;$i++){ 
            $evidence_per_comite[$i] = 0;
        }

        $total_evidences_not_draft = Evidence::all();
        foreach($total_evidences_not_draft as $evidence){
            $name_comite = Comittee::find($evidence->comittee_id)->name;
            if(str_contains($name_comite,'Presidencia')){
                $evidence_per_comite[0] = $evidence_per_comite[0] + 1;
            } elseif (str_contains($name_comite,'Secretar')){
                $evidence_per_comite[1] = $evidence_per_comite[1] + 1;
            } elseif (str_contains($name_comite,'Programa')){
                $evidence_per_comite[2] = $evidence_per_comite[2] + 1;
            } elseif (str_contains($name_comite,'Igualdad')){
                $evidence_per_comite[3] = $evidence_per_comite[3] + 1;
            } elseif (str_contains($name_comite,'Sostenibilidad')){
                $evidence_per_comite[4] = $evidence_per_comite[4] + 1;
            } elseif (str_contains($name_comite,'Finanzas')){
                $evidence_per_comite[5] = $evidence_per_comite[5] + 1;
            } elseif (str_contains($name_comite,'Log')){
                $evidence_per_comite[6] = $evidence_per_comite[6] + 1;
            } elseif (str_contains($name_comite,'Comunicaci')){
                $evidence_per_comite[7] = $evidence_per_comite[7] + 1;
            }
        }

        return $evidence_per_comite;
    }

    public function getComiteProofWeight(){
        for($i=0;$i<8;$i++){
            $comite_weight[$i]=0;
        }
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach($total_evidences_not_draft as $evidence){
            $name_comite_proof = Comittee::find($evidence->comittee_id)->name;
            foreach($evidence->proofs as $proof){
                if(str_contains($name_comite_proof,'Presidencia')){
                    $comite_weight[0] = $comite_weight[0] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Secretar')){
                    $comite_weight[1] = $comite_weight[1] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Programa')){
                    $comite_weight[2] = $comite_weight[2] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Igualdad')){
                    $comite_weight[3] = $comite_weight[3] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Sostenibilidad')){
                    $comite_weight[4] = $comite_weight[4] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Finanzas')){
                    $comite_weight[5] = $comite_weight[5] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Log')){
                    $comite_weight[6] = $comite_weight[6] + $proof->file->size;
                } elseif (str_contains($name_comite_proof,'Comunicaci')){
                    $comite_weight[7] = $comite_weight[7] + $proof->file->size;
                }
            }
        }

        for($i=0;$i<8;$i++){
            $comite_weight[$i] = $this->getSize($comite_weight[$i]);
        }
        return $comite_weight;
    }

    public function getSecretariosComite(){

        for($i=0; $i<8;$i++){ 
            $secretarios_por_comite[$i] = 0;
        }
        
        $comites = Comittee::all();

        foreach($comites as $comiteElegido){
            $comiteSecretarios = $comiteElegido->name;

            if(str_contains($comiteSecretarios,'Presidencia')){
                $secretarios_por_comite[0] = $secretarios_por_comite[0] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Secretar')){
                $secretarios_por_comite[1] = $secretarios_por_comite[1] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Programa')){
                $secretarios_por_comite[2] = $secretarios_por_comite[2] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Igualdad')){
                $secretarios_por_comite[3] = $secretarios_por_comite[3] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Sostenibilidad')){
                $secretarios_por_comite[4] = $secretarios_por_comite[4] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Finanzas')){
                $secretarios_por_comite[5] = $secretarios_por_comite[5] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Log')){
                $secretarios_por_comite[6] = $secretarios_por_comite[6] + $comiteElegido-> secretaries()->count();
            } elseif (str_contains($comiteSecretarios,'Comunicaci')){
                $secretarios_por_comite[7] = $secretarios_por_comite[7] + $comiteElegido-> secretaries()->count();
            }

        }
        return $secretarios_por_comite;
    }

    public function getComiteMeetings(){

        for($i=0; $i<8;$i++){ 
            $meetings_comite[$i] = 0;
        }
        $comites = Comittee::all();

        foreach($comites as $comite){
            $comiteName = $comite->name;
            if(str_contains($comiteName,'Presidencia')){  
                $meetings_comite[0] = $meetings_comite[0] + $comite->meetings()->count();              
            } elseif (str_contains($comiteName,'Secretar')){  
                $meetings_comite[1] = $meetings_comite[1] + $comite->meetings()->count();               
            } elseif (str_contains($comiteName,'Programa')){                
                $meetings_comite[2] = $meetings_comite[2] + $comite->meetings()->count(); 
            } elseif (str_contains($comiteName,'Igualdad')){                
                $meetings_comite[3] = $meetings_comite[3] + $comite->meetings()->count(); 
            } elseif (str_contains($comiteName,'Sostenibilidad')){                
                $meetings_comite[4] = $meetings_comite[4] + $comite->meetings()->count(); 
            } elseif (str_contains($comiteName,'Finanzas')){
                $meetings_comite[5] = $meetings_comite[5] + $comite->meetings()->count(); 
            } elseif (str_contains($comiteName,'Log')){
                $meetings_comite[6] = $meetings_comite[6] + $comite->meetings()->count(); 
            } elseif (str_contains($comiteName,'Comunicaci')){
                $meetings_comite[7] = $meetings_comite[7] + $comite->meetings()->count(); 
            }
        }
        return $meetings_comite;
    }

}