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

        /*

            DATOS GENERALES DE LAS JORNADAS

        */

        /*Usuarios totales 5 linea FUNCIONA*/
        $total_users = User::all();
        $total_users_filtered = 0;
        foreach($total_users as $users){
            if(!($users->hasRole('LECTURE'))){
                $total_users_filtered = $total_users_filtered +1;
            }
        }

        /*Evidencia totales 1 linea FUNCIONA*/
        $total_evidences_not_draft_count = Evidence::evidences_not_draft()->count();
        
        /*Evidencias medias por persona 1 linea FUNCIONA*/
        $evidences_per_user = $total_evidences_not_draft_count / $total_users_filtered;

        /*Evidencias en un rango X de horas (entre 0 y 1,1 y 2, 3 y 5) 13 lineas FUNCIONA*/

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

        /*Numero de archivos subidos a las evidencias 5 lineas TERMINADO*/
        
        $total_files = 0;
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach ($total_evidences_not_draft as $evidence) {
           foreach($evidence->proofs as $proof){
                $total_files = $total_files + 1;
           }
        }
        
        /*Media de archivo por evidencia 1 linea TERMINADO*/

        $mean_evidence_proof = $total_files / $total_evidences_not_draft_count;

        /*Evidencias en un rango X archivos (0,1,2,3 o mas) 14 lineas TERMINADO*/

        for($i = 0; $i<4;$i++){ 
            $dict_evidences_proof_ranges[$i] = 0;
        }

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

        /*Peso total de los archivos subidos 6 lineas FALTA PONER TAMAÑO EN LEGIBLE EN GB*/

        $total_weight = 0;
        $total_evidences_not_draft = Evidence::evidences_not_draft();
        foreach($total_evidences_not_draft as $evidence){
            foreach($evidence->proofs as $proof){
                $size = $proof->file->size;
                $total_weight = $total_weight + $size;
            }
        }

        /*Peso medio de archivos por evidencia 1 Linea TERMINADO*/
        $mean_evidences_proof_weight = $total_weight / $total_evidences_not_draft_count;

        /*ESTOS DOS METODOS DE AQUI ABAJO HAY QUE ARREGLARLOS POR QUE SI NO SE TIENEN MEETIGNS NO SE PUEDE COMPROBA*/

        /*Reuniones totales 1 Linea TERMINADO*/
        $total_meetings_counts = Meeting::all()->count();        

        /*Tiempo total de reunion 10 Lineas*/
/*
        $total_time_meetings_hours = 5; /*ESTE VALOR PONERLO A 0, ES UNA PRUEBA*/
/*        $total_time_meetings_minutes = 65; /*ESTE VALOR PONERLO A 0, ES UNA PRUEBA*/
/*        $total_meetings = Meeting::all();
        foreach($total_meetings as $meeting){
            $total_time_meetings_hours = $total_time_meetings + $meeting->hours;
            /* TENGO QUE ARREGLARLO ME DA ERROR NO SE POR QUE AHI
            $minutes = MeetingMinutes::find($meeting->id)
            $total_time_meetings_minutes = $total_time_meetings_minutes + $minutes;
            if($total_time_meetings_minutes > 60){
                $total_time_meetings_hours = $total_time_meetings_hours + 1;
                $total_time_meetings_minutes = $total_time_meetings_minutes - 60;
            }

            */
        /*}*/


        /* 

            DATOS PARA CADA COMITE

        */

        /*Personas por comite 21 Lineas FUNCIONA pero hay que revisar por que tiene pinta que son las evidencias*/

        $comites = Comittee::all();
        for($i=0;$i<8;$i++){
            $user_comite[$i]=0;
        }
        
        foreach($total_users as $user){
            $comites_user = $user->committee_belonging();
                if(str_contains($comites_user,'Presidencia')){
                    $user_comite[0] = $user_comite[0] + 1;
                } elseif (str_contains($comites_user,'Secretar')){
                    $user_comite[1] = $user_comite[1] + 1;
                } elseif (str_contains($comites_user,'Programa')){
                    $user_comite[2] = $user_comite[2] + 1;
                } elseif (str_contains($comites_user,'Igualdad')){
                    $user_comite[3] = $user_comite[3] + 1;
                } elseif (str_contains($comites_user,'Sostenibilidad')){
                    $user_comite[4] = $user_comite[4] + 1;
                } elseif (str_contains($comites_user,'Finanzas')){
                    $user_comite[5] = $user_comite[5] + 1;
                } elseif (str_contains($comites_user,'Log')){
                    $user_comite[6] = $user_comite[6] + 1;
                } elseif (str_contains($comites_user,'Comunicaci')){
                    $user_comite[7] = $user_comite[7] + 1;
                }
            
            
        }
        
        /*Numero evidencias por comite 21 Lineas FUNCIONA*/

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

        /*Numero medio de evidencias por personas dentro del comite 2 lineas da division por 0*/

        /*for($i = 0; $i<8;$i++){ 
            if($user_comite>0 or $user_comite!=null){
                $mean_evidence_proof_comite[$i] = $evidence_per_comite[$i]/$user_comite[$i];
            } else {
                $mean_evidence_proof_comite[$i] = 0;
            }
        }*/
        
        /*Peso total archivos por comite*/

        /*Numero de reuniones efectivas segun cambridge*/

        /* Esta comentado por que no hay meetings, cuando se creen entonces se quita
        $mean_time_meetings_hour = $total_time_meetings_hours / $total_meetings_counts;
        $mean_time_meetings_minutes = $total_time_meetings_minutes / $total_meetings_counts;
        */

        /*Reuniones por cada comite*/

       /* $all_comites = Comittee::all();

        for($i=0; $i<8;$i++){ 
            $meetings_comite[$i] = 0;
        }

        foreach($all_comites as $comite){
            $meetings_comite = $comite->meetings();
        }*/

        /*Media tiempo de reuniones por cada comite*/

        /*Numero de Secretarios por comite 20 Lineas TERMINADO*/

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


        /*Media de Reuniones por secretario ¿REALMENTE UTIL?*/

        /*Reuniones en los tiempos de 0 a 1 hora, de 1 a 2 horas, de 2 a 3 horas y de 3 a mas horas ordenadas segun el comite*/


        return view('dashboard.view',['instance'=> $instance, 'total_evidences_not_draft'=>$total_evidences_not_draft_count, 
        'total_users' => $total_users_filtered, 'evidences_per_user' => $evidences_per_user, 'total_files' => $total_files,
        'mean_evidence_proof' => $mean_evidence_proof, 'total_weight' => $total_weight, 'mean_evidences_proof_weight' => $mean_evidences_proof_weight,
        'comites' => $user_comite, "secretarios_comite" => $secretarios_por_comite
        ]);
    }







}