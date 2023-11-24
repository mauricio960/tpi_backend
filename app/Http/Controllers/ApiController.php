<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\TblNAplicacionOferta;
use App\Models\TblNEstudiante;
use App\Models\TblNCurriculum;
use App\Models\TblNEstadoAplicacionOferta;
use App\Models\TblNOferta;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class ApiController extends Controller{

    /*public function aplicaciones(){

        $listaCombinada = DB::table('tbl_n_aplicacion_oferta as a')
                        ->join('tbl_n_usuario as u', 'a.fk_usuario', '=', 'u.id')
                        ->join('tbl_n_estudiante as e', 'u.fk_estudiante', '=', 'e.id')
                        ->join('tbl_n_estado_aplicacion_oferta as s', 'a.fk_estado_aplicacion_oferta', '=', 's.id')
                        ->select('a.id', 'u.activo', 'e.primer_nombre', 'e.segundo_nombre')
                        ->get();

        return response()->json([
            'status' => true, 
            'lista_combinada' => $listaCombinada
        ]);
    }*/

    public function aplicaciones(){
        $aplicaciones = TblNAplicacionOferta::all();
        return response()->json($aplicaciones);
    }

    /*public function aplicaciones2(Request $request){
        $students = Student::join('', 'students.id', '=', 'job_applications.student_id')
             ->select('students.name', 'students.email', 'job_applications.position', 'job_applications.status')
             ->get();
    }*/

    /*public function aplicaciones(Request $request){
        $listaCombinada = TblNEstudiante::with('')->get();

        // Personaliza la estructura de la respuesta
        $resultado = $listaCombinada->map(function ($estudiante) {
            return [
                'id_estudiante' => $estudiante->id,
                'nombre_estudiante' => $estudiante->nombre,
                'solicitudes_trabajo' => $estudiante->solicitudesTrabajo->map(function ($solicitud) {
                    return [
                        'id_solicitud' => $solicitud->id,
                        'descripcion_solicitud' => $solicitud->descripcion,
                        // Agrega más atributos de la solicitud según tus necesidades
                    ];
                }),
                // Agrega más atributos del estudiante según tus necesidades
            ];
        });

        // Puedes ajustar la estructura según tus necesidades específicas

        return response()->json($resultado);
    }*/

    //Metodo put (modificar)
    public function actualizar_apli(Request $request, $id)
    {
        // Validación básica, puedes personalizar según tus necesidades
        /*$request->validate([
            'estado' => 'required|in:pendiente,aprobada,rechazada', // Ajusta los posibles estados
        ]);*/

        // Buscar la solicitud de trabajo por ID
        $solicitud = TblNAplicacionOferta::find($id);

        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud de trabajo no encontrada'], 404);
        }

        // Actualizar el estado de la solicitud
        $solicitud->estado = $request->input('estado');
        $solicitud->save();

        // Retornar la respuesta en formato JSON
        return response()->json(['message' => 'Estado de la solicitud de trabajo actualizado con éxito']);
    }
}