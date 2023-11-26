<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\TblNAplicacionOferta;
use App\Models\TblNEstudiante;
use App\Models\TblNCurriculum;
use App\Models\TblNEstadoAplicacionOferta;
use App\Models\TblNOferta;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use GuzzleHttp\Client;

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
        $jsonFilePath = storage_path('aplicaciones.json');
        $jsonContent = file_get_contents($jsonFilePath);
        $jsonData = json_decode($jsonContent, true);
        //return $jsonContent;
        return response()->json($jsonData, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function aplicacionesIndividual($id){
        $jsonFilePath = storage_path('aplicaciones.json');
        $jsonContent = file_get_contents($jsonFilePath);
        $data = json_decode($jsonContent, true);
        foreach ($data as &$aplicacion) {
            if ($aplicacion['id_aplicacion_trabajo'] == $id) {
                return response()->json($aplicacion, 200, [], JSON_UNESCAPED_UNICODE);
            }
        }   
    }
    //Metodo put (modificar)
    public function actualizar_apli(Request $request, $id)
    {
        // Validar y procesar la solicitud de actualización
        $jsonFilePath = storage_path('aplicaciones.json');
        $jsonContent = file_get_contents($jsonFilePath);
        $data = json_decode($jsonContent, true);
        $json = response()->json($request);
        $datos = $request->json()->all();
        $datosJson = json_encode($datos);
        $datosDecodificados = json_decode($datosJson, true);
        //$json = $request->json()->all();
        //$estadoAplicacion = $json['estado_aplicacion'];
        foreach ($data as &$aplicacion) {
            if ($aplicacion['id_aplicacion_trabajo'] == $id) {
                // Actualizar el estado (utiliza $request->json)
                $aplicacion['estado_aplicacion'] = 'aceptado';
                // Guardar los cambios en el archivo JSON
                file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT));

                // Puedes devolver una respuesta JSON indicando el éxito
                return response()->json(['message' => 'Aplicación actualizada con éxito']);
                //return response()->json($datosDecodificados);
            }
        }   

        // Si no se encuentra la aplicación con la ID especificada
        return response()->json(['error' => 'Aplicación no encontrada'], 404);
    }

    public function actualizar_apli2(Request $request, $id){
        // Validar y procesar la solicitud de actualización
        $jsonFilePath = storage_path('app/json/aplicaciones.json');
        $jsonContent = file_get_contents($jsonFilePath);
        $data = json_decode($jsonContent, true);
        $json = response()->json($request);
        $datos = $request->json()->all();
        $datosJson = json_encode($datos);
        $datosDecodificados = json_decode($datosJson, true);
        //$json = $request->json()->all();
        //$estadoAplicacion = $json['estado_aplicacion'];
        foreach ($data as &$aplicacion) {
            if ($aplicacion['id_aplicacion_trabajo'] == $id) {
                // Actualizar el estado (utiliza $request->json)
                $aplicacion['estado_aplicacion'] = 'rechazado';
                // Guardar los cambios en el archivo JSON
                file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT));

                // Puedes devolver una respuesta JSON indicando el éxito
                return response()->json(['message' => 'Aplicación actualizada con éxito']);
                //return response()->json($datosDecodificados);
            }
        }   

        // Si no se encuentra la aplicación con la ID especificada
        return response()->json(['error' => 'Aplicación no encontrada'], 404);
    }

    public function listadoapli(){
        $resultados = DB::table('tbl_n_estudiante AS ES')
            ->join('tbl_n_usuario AS US', 'ES.id', '=', 'US.fk_estudiante')
            ->join('tbl_n_aplicacion_oferta AS APF', 'US.id', '=', 'APF.fk_usuario')
            ->select('ES.*', 'US.*', 'APF.*')
            ->orderBy('APF.id')
            ->get();

        return response()->json([
            'status' => true, 
            'resultados' => $resultados 
        ]);        
    }
}