<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\TblNAptitud;
use App\Models\TblNAptitudCurriculum;
use App\Models\TblNCurriculum;
use App\Models\TblNEmpresa;
use App\Models\TblNEstudiante;
use App\Models\TblNExperienciaAcademica;
use App\Models\TblNExperienciaLaboral;
use App\Models\TblNPuesto;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CurriculumController extends Controller
{
    /**
     * Obtiene el curriculum del estudiante.
     *
     * @return \Illuminate\Http\Response
     * ADAPTAR A QUE TRAIGA TODO DE UNA VEZ, FALTA: IMAGEN, DOCUMENTO.
     */
    public function getCurriculumEstudiante(Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $estudiante = TblNEstudiante::find($usuario->fk_estudiante);
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $documento = null;
            $imagen = null;
            if($usuario->ruta_imagen != null){
                $imagePath = storage_path('app/public/' . $usuario->ruta_imagen);
                $imagen = base64_encode(file_get_contents($imagePath));
            }


            if($curriculum == null){
                $curriculum = new TblNCurriculum();
                $curriculum->fk_usuario = $usuario->id;
                $curriculum->save();
            }

            $curriculum?->load('experiencias_laborales.empresa_ctg','experiencias_laborales.puesto_ctg');
            $curriculum?->load('experiencias_academicas');
            $curriculum?->load('aptitudes_curriculum.aptitud');

            // if($curriculum?->ruta_documento != null){
            //     $documentoPath = storage_path('app/public/documentos_curriculum'. $curriculum->ruta_documento);
            //     $documento = Storage::get($documentoPath);
            // }
            $correo_electronico = $usuario->email;
            return response()->json([
                'success'=>true,
                'curriculum'=>$curriculum,
                'imagen'=>$imagen,
                'estudiante'=>$estudiante,
                'correo_electronico'=>$correo_electronico,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                "error"=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al obtener el curriculum del estudiante.',
            ]);
        }
    }

    public function putDatosEstudiante (Request $request){
        try{

            $usuario = User::find($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            ['telefono'=>$telefono, 'descripcion_usuario'=>$descripcion_usuario] = $request->all();

            $estudiante = TblNEstudiante::find($usuario->fk_estudiante);
            $estudiante->telefono =$telefono;
            $estudiante->save();

            $curriculum->descripcion_usuario =$descripcion_usuario;
            $curriculum->save();

            return response()->json([
                'success'=>true,
                'message'=>'Cambio realizado correctamente'
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al actualizar los datos del estudiante en el servidor.',
            ]);
        }
    }

    public function postImagenCurriculumEstudiante(Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $imagen = $request->file('imagen_perfil');

            if($imagen != null){
                //Guardando imagen en el storage de la aplicación.
                $imagePath = $imagen->store('uploads','public');
                $usuario->ruta_imagen = $imagePath;
                $usuario->save();
            }

            return response()->json([
                'success'=>true,
                'message'=>'Imagen Guardada Correctamente'
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al guardar la imagen de ',
            ]);
        }
    }

    public function postDocumentoCurriculumEstudiante(Request $request){
        try{

            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $curriculum_path=null;

            if($request->file('documento_cv') == true){
                //Guardando documento en el storage de la aplicacion.
                $documento = $request->file('documento_cv');
                $nombre_original = $documento->getClientOriginalName();
                Storage::putFileAs(
                    'public/documentos_cv', $request->file('documento_cv'), $nombre_original
                );
                $curriculum_path = Storage::url('documentos_cv/' . $nombre_original);
            }

            if($curriculum_path != null){
                $curriculum->ruta_documento = $curriculum_path;
                $curriculum->save();
            }

            return response()->json([
                'success'=>true,
                'message'=> 'El documento se ha guardado correctamente.',
            ]);


        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'success'=>false,
                'error'=> $error,
                'message'=> 'Ocurrio un error al guardar el documento en el curriculum del estudiante.'
            ]);
        }
    }

    public function getInicializarExperienciaLaboral(Request $request){
        try{
            $puestos = TblNPuesto::all();
            $empresas = TblNEmpresa::all();

            return response()->json([
                'success'=>true,
                'puestos'=>$puestos,
                'empresas'=>$empresas,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al obtener los catalogos. ',
            ]);
        }
    }

    public function getExperienciaLaboral(Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $experiencias_laborales = [];
            if($curriculum != null){
                $curriculum->load('experiencias_laborales.empresa_ctg');
                $curriculum->load('experiencias_laborales.puesto_ctg');
                $experiencias_laborales = $curriculum->experiencias_laborales;
            }

            return response()->json([
                'success'=>true,
                'experiencias_laborales'=>$experiencias_laborales,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=>$error,
                'success'=>false,
                'message'=>'Ocurrio un error al obtener las experiencias laborales.',
            ]);
        }
    }

    public function postExperienciaLaboral(Request $request){
        try{
            [
            'fk_empresa'=>$fk_empresa,
            'fk_puesto'=>$fk_puesto,
            'fecha_inicio'=>$fecha_inicio,
            'fecha_finalizacion'=>$fecha_finalizacion] = $request->all();
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $experiencia_laboral = new TblNExperienciaLaboral();

            $fecha_inicio = new DateTime($fecha_inicio);
            $fecha_finalizacion = new DateTime($fecha_finalizacion);

            //obteniendo datos de catalogo.
            if($fk_empresa != null){
                $fk_empresa = TblNEmpresa::find($fk_empresa['id']);
            }
            if($fk_puesto != null){
                $fk_puesto = TblNPuesto::find($fk_puesto['id']);
            }
            $experiencia_laboral->fk_curriculum = $curriculum->id;
            $experiencia_laboral->fk_empresa = $fk_empresa?->id;
            $experiencia_laboral->fk_puesto = $fk_puesto?->id;
            $experiencia_laboral->fecha_inicio =$fecha_inicio;
            $experiencia_laboral->fecha_finalizacion = $fecha_finalizacion;
            $experiencia_laboral->empresa =null;
            $experiencia_laboral->puesto =null;
            $experiencia_laboral->duracion =null;
            $experiencia_laboral->save();

            return response()->json([
                'success'=>true,
                'message'=>'Experiencia laboral añadida correctamente.'
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al guardar la experiencia laboral.',
            ]);
        }
    }

    public function updateExperienciaLaboral($id_experiencia_laboral,Request $request){
        try{
            ['empresa'=>$empresa,
            'fk_empresa'=>$fk_empresa,
            'fk_puesto'=>$fk_puesto,
            'puesto'=>$puesto,
            'duracion'=>$duracion] = $request->all();

            $experiencia_laboral = TblNExperienciaLaboral::findOrFail($id_experiencia_laboral);

            //obteniendo datos de catalogo.
            if($fk_empresa != null and $empresa == null){
                $fk_empresa = TblNEmpresa::findOrFail($fk_empresa);
            }
            if($fk_puesto != null and $puesto == null){
                $fk_puesto = TblNPuesto::findOrFail($fk_puesto);
            }
            $experiencia_laboral->fk_empresa = $fk_empresa?->id;
            $experiencia_laboral->fk_puesto = $fk_puesto?->id;
            $experiencia_laboral->empresa =$empresa;
            $experiencia_laboral->puesto =$puesto;
            $experiencia_laboral->duracion =$duracion;
            $experiencia_laboral->save();

            return response()->json([
                'success'=>true,
                'message'=>'Experiencia laboral actualizado correctamente.'
            ]);


        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'success'=>false,
                'message'=> 'Ocurrio un error al actualizar la experiencia laboral.',
            ]);

        }
    }

    public function deleteExperienciaLaboral($id_experiencia_laboral,Request $request){
        try{
            $experiencia_laboral = TblNExperienciaLaboral::findOrFail($id_experiencia_laboral);
            $experiencia_laboral->delete();

            return response()->json([
                'success'=>true,
                'message'=>'Experiencia laboral eliminada correctamente',
             ]);
        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=>'Ocurrio un error al eliminar la experiencia laboral.',
                'success'=>false,
            ]);
        }
    }

    public function getExperienciaAcademica(Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $curriculum->load('experiencias_academicas');
            $experiencias_laborales = [];
            $experiencias_laborales = $curriculum->experiencias_laborales;

            return response()->json([
                'success'=>true,
                'experiencias_laborales'=>$experiencias_laborales,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al obtener la experiencia academica.',
                'success'=>false,
            ]);
        }
    }

    public function postExperienciaAcademica(Request $request){
        $entra_if=false;
        $documento_path=null;
        try{
            DB::beginTransaction();
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();


            $institucion_academica = $request->input('institucion_academica');
            $titulo = $request->input('titulo');
            $finalizado = $request->input('finalizado') === 'true';
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_finalizacion = $request->input('fecha_finalizacion');

            //obteniendo el documento de experiencia de venir.
            if($request->file('documento_experiencia') == true){
                $entra_if = true;
                $documento = $request->file('documento_experiencia');

                $extension = $documento->getClientOriginalExtension();
                $nombre_original = $documento->getClientOriginalName();
                $nombre_unico = uniqid().".".$extension;

                $documento_path = $nombre_unico;

                // Almacenar el archivo en el sistema de archivos
                // Storage::putFileAs('public/documentos_experiencia',$documento, $nombre_unico);

                // // $documento_path = Storage::url('documentos_experiencia/' . $nombre_unico);
                // $documento_path = $documento->storeAs(
                //     'documento_exp'.uniqid(), $request->get('usuario_tk')
                // );

                $documento_path = Storage::putFileAs(
                    'public/documentos_experiencia', $request->file('documento_experiencia'), $nombre_original
                );
                $documento_path = Storage::url('documentos_experiencia/' . $nombre_original);

            }

            $fecha_inicio = new DateTime($fecha_inicio);
            $fecha_finalizacion = new DateTime($fecha_finalizacion);

            $experiencia_academica = new TblNExperienciaAcademica();
            $experiencia_academica->fk_curriculum = $curriculum->id;
            $experiencia_academica->institucion_academica=$institucion_academica;
            $experiencia_academica->titulo = $titulo;
            $experiencia_academica->finalizado = $finalizado;
            $experiencia_academica->fecha_inicio = $fecha_inicio;
            $experiencia_academica->fecha_finalizacion = $fecha_finalizacion;
            $experiencia_academica->ruta_documento =$documento_path;
            $experiencia_academica->save();
            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Se ha guardado la experiencia academica correctamente.',
                'entra_if'=>$entra_if,
                'documento_path'=>$documento_path,
            ]);



        }catch(\Exception $e){
            DB::rollBack();
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al guardar la experiencia academica.',
                'success'=>false,
                'entra_if'=>$entra_if,
            ]);
        }
    }

    public function putExperienciaAcademica($id_experiencia_academica, Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));

            $institucion_academica = $request->input('institucion_academica');
            $titulo = $request->input('titulo');
            $finalizado = $request->input('finalizado') === 'true';
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_finalizacion = $request->input('fecha_finalizacion');
            $documento = $request->file('documento_experiencia');
            $documento_path=null;

            if($documento != null){
                //Guardando documento en el storage de la aplicacion.
                $documento_path = $documento->store('public/documentos_experiencia_academica');
            }



            $fecha_inicio = new DateTime($fecha_inicio);
            $fecha_finalizacion = new DateTime($fecha_finalizacion);

            $experiencia_academica = TblNExperienciaAcademica::find($id_experiencia_academica);
            $experiencia_academica->titulo = $titulo;
            $experiencia_academica->institucion_academica=$institucion_academica;
            $experiencia_academica->finalizado = $finalizado;
            $experiencia_academica->fecha_inicio = $fecha_inicio;
            $experiencia_academica->fecha_finalizado = $fecha_finalizacion;
            if($documento != null){
                $experiencia_academica->ruta_documento =$documento_path;
            }
            $experiencia_academica->save();

            return response()->json([
                'success'=>true,
                'message'=>'Se ha guardado la experiencia academica correctamente.'
            ]);



        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al guardar la experiencia academica.',
                'success'=>false,
            ]);
        }
    }

    public function deleteExperienciaAcademica($id_experiencia_academica, Request $request){
        try{
            $experiencia_academica = TblNExperienciaAcademica::find($id_experiencia_academica);
            $experiencia_academica->delete();

            return response()->json([
                'success'=>true,
                'message'=>'Se ha eliminado la experiencia academica correctamente.',
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al eliminar la experiencia academica.',
                'success'=>false
            ]);
        }
    }



    public function inicializarAptitudCurriculum(Request $request){
        try{
            $aptitudes = TblNAptitud::all();
            return response()->json([
                'success'=>true,
                'aptitudes'=>$aptitudes,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al obtener los catalogos para las aptitudes del curriculum.',
                'success'=>false,
            ]);
        }
    }

    public function getAptitudCurriculum(Request $request){
        try{
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();
            $aptitudes_curriculum=[];
            if($curriculum){
                $curriculum->load('aptitudes_curriculum.aptitud');
                $aptitudes_curriculum = $curriculum->aptitudes_curriculum;
            }

            return response()->json([
                'success'=>true,
                'aptitudes_curriculum'=>$aptitudes_curriculum,
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al obtener las aptitudes del curriculum.',
                'success'=>false,
            ]);
        }
    }

    public function postAptitudCurriculum(Request $request){
        try{
            // ['fk_aptitud'=>$fk_aptitud] = $request->all();
            // $usuario = User::findOrFail($request->get('usuario_tk'));
            // $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();

            // $aptitud = TblNAptitud::find($fk_aptitud)->first();
            // $aptitud_curriculum = new TblNAptitudCurriculum();
            // $aptitud_curriculum->fk_curriculum = $curriculum->id;
            // $aptitud_curriculum->fk_aptitud = $aptitud->id;
            // $aptitud_curriculum->save();
            ['lista_aptitudes_cv'=>$lista_aptitudes_cv] = $request->all();
            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();

            foreach($lista_aptitudes_cv as $aptitud_it){
               if($aptitud_it != null){
                $coincidencia = TblNAptitudCurriculum::where('fk_curriculum',$curriculum->id)
                ->where('fk_aptitud',$aptitud_it['id'])->first();
                if($coincidencia){
                    //Existe.
                    $coincidencia->activo=true;
                    $coincidencia->save();
                }else{
                    $aptitud = TblNAptitud::find($aptitud_it['id']);
                    $n_aptitud_cv = new TblNAptitudCurriculum();
                    $n_aptitud_cv->fk_curriculum = $curriculum->id;
                    $n_aptitud_cv->fk_aptitud = $aptitud->id;
                    $n_aptitud_cv->activo = true;
                    $n_aptitud_cv->save();
                }
               }
            }//foreach.

            $lista_ids_aptitudes =[];
            foreach($lista_aptitudes_cv as $aptitud_it){
                if($aptitud_it != null){
                    array_push($lista_ids_aptitudes, $aptitud_it['id']);
                }
            }
            if(sizeof($lista_ids_aptitudes) > 0){

                TblNAptitudCurriculum::where('fk_curriculum',$curriculum->id)
                    ->where('activo',true)
                    ->whereNotIn('fk_aptitud', $lista_ids_aptitudes)
                    ->update(['activo'=>false]);

            }else{
                TblNAptitudCurriculum::where('fk_curriculum',$curriculum->id)
                ->where('activo',true)
                ->update(['activo'=>false]);
            }


            return response()->json([
                'success'=>true,
                'message'=>'Se Creo Correctamente la aptitud del curriculum'
            ]);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al guardar la aptitud del curriculum.',
                'success'=>false,
            ]);
        }
    }

    public function putAptitudCurriculum($id_aptitud_curriculum, Request $request){
        try{
            ['fk_aptitud'=>$fk_aptitud] = $request->all();

            $usuario = User::findOrFail($request->get('usuario_tk'));
            $curriculum = TblNCurriculum::where('fk_usuario',$usuario->id)->first();

            $aptitud = TblNAptitud::find($fk_aptitud)->first();
            $aptitud_curriculum =  TblNAptitudCurriculum::find($id_aptitud_curriculum);
            $aptitud_curriculum->fk_curriculum = $curriculum->id;
            $aptitud_curriculum->fk_aptitud = $aptitud->id;
            $aptitud_curriculum->save();

            return response()->json([
                'success'=>true,
                'message'=>'Se Actualizó Correctamente la aptitud del curriculum'
            ]);
        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al actualizar la aptitud del curriculum.',
                'success'=>false,
            ]);
        }
    }

    public function deleteAptitudCurriculum($id_aptitud_curriculum){
        try{
            $aptitud_curriculum =  TblNAptitudCurriculum::find($id_aptitud_curriculum);
            $aptitud_curriculum->delete();
            return response()->json([
                'success'=>true,
                'message'=> 'Se eliminó la aptitud correctamente.'
            ]);
        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=> $error,
                'message'=> 'Ocurrio un error al eliminar la aptitud del curriculum.',
                'success'=>false,
            ]);
        }
    }


}
