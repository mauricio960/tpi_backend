<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use app\models\TblNAplicacionOferta;
use app\models\TblNEstudiante;

use Illuminate\Support\Facades\Hash;

class ApiController extends Controller{

    public function aplicaciones(){

        $apli = TblNEstudiante::all();
        return response()->json($apli);
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

    public function index()
    {
        // Obtener la lista de aplicaciones de trabajo con información específica del estudiante
        $aplicaciones = TblNAplicacionOferta::with(['' => function ($query) {
            // Seleccionar solo los campos que deseas del estudiante
            $query->select('id', 'nombre', 'correo');
        }])->get();

        // Retornar la respuesta en formato JSON
        return response()->json($aplicaciones);
    }


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