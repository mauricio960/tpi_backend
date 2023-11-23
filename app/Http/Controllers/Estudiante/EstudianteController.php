<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudiante\postAplicacionOferta;
use App\Models\TblNAplicacionOferta;
use App\Models\TblNOferta;
use App\Models\User;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    /**
     * Obtiene todas las aplicaciones a ofertas en el sistema.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAplicacionOferta(Request $request){
        $usuario = User::find($request->get("usuario_tk"));
        try{
            $aplicaciones = TblNAplicacionOferta::where('fk_usuario', $usuario->id)
                            ->where('activo',true)
                            ->get();
            $aplicaciones->load('oferta.requisitos_aspirante',
                                'oferta.responsabilidades_puesto',
                                'oferta.empresa_ctg',
                                'oferta.puesto_ctg',
                                'oferta.estado_oferta');
            $aplicaciones->load('estado_aplicacion_oferta');

            return response()->json([
                'success'=> true,
                'lista_aplicaciones'=>$aplicaciones,
            ]);


        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'error'=>$error,
                'success'=>false,
                'message'=>'Ocurrio un error al obtener las aplicaciones a ofertas del usuario.'
            ]);
        }
    }
    /**
     * Crea una nueva aplicación de oferta en el sistema.
     *
     * @return \Illuminate\Http\Response
     */
    public function postAplicacionOferta(postAplicacionOferta $request){
        $usuario = User::find($request->get("usuario_tk"));
        try{
            $oferta = TblNOferta::findOrFail($request->id_oferta);

            //Creando la aplicación de la oferta.
            $aplicacionOferta = new TblNAplicacionOferta();
            $aplicacionOferta->fk_usuario = $usuario->id;
            $aplicacionOferta->fk_oferta = $oferta->id;
            $aplicacionOferta->fk_estado_aplicacion_oferta=1; //Enviada.
            $aplicacionOferta->save();

            return response()->json([
                'success'=>true,
                'message'=>'La aplicación a la oferta se realizó correctamente.'
            ]);


        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'success'=>false,
                'error'=>$error,
                'message'=>'Ocurrió un error al crear la aplicación a la oferta.'
            ]);
        }
    }
    /**
     * Obtiene una aplicación de oferta.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAplicacionOferta($id_aplicacion_oferta, Request $request){
        $usuario = User::find($request->get("usuario_tk"));
        try{
            $aplicacionOferta = TblNAplicacionOferta::findOrFail($id_aplicacion_oferta);
            $aplicacionOferta->load('oferta');
            $aplicacionOferta->load('oferta.requisitos_aspirante');
            $aplicacionOferta->load('oferta.responsabilidades_puesto');
            $aplicacionOferta->load('oferta.empresa_ctg');
            $aplicacionOferta->load('oferta.puesto_ctg');
            $aplicacionOferta->load('oferta.estado_oferta');
            return response()->json([
                'success'=>true,
                'aplicacion_oferta'=>$aplicacionOferta,
            ]);
        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'success'=>false,
                'error'=>$error,
                'message'=>'Ocurrió un error al obtener la aplicación a la oferta.'
            ]);
        }
    }

    /**
     * Obtiene las ofertas disponibles.
     */
    public function getOfertasDisponibles(Request $request){
        $usuario = User::find($request->get("usuario_tk"));
        try{

            $aplicaciones = TblNAplicacionOferta::where('fk_usuario', $usuario->id)
                            ->where('activo',true)
                            ->get();
            $aplicaciones->load('oferta');

            $ofertas_id =[];
            foreach($aplicaciones as $aplicacion){
                array_push($ofertas_id, $aplicacion->oferta->id);
            }


            $ofertas = TblNOferta::whereNotIn('id',$ofertas_id)->get();

            $ofertas->load('requisitos_aspirante');
            $ofertas->load('responsabilidades_puesto');
            $ofertas->load('empresa_ctg');
            $ofertas->load('puesto_ctg');
            $ofertas->load('estado_oferta');

            return response()->json([
                'ofertas'=>$ofertas
            ]);


        }catch(\Exception $e){
            $error = $e->getMessage();
            return response()->json([
                'success'=>false,
                'error'=>$error,
                'message'=>'Ocurrió un error al obtener las ofertas.'
            ]);
        }
    }
}
