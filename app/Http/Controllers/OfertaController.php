<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class OfertaController extends Controller
{


    public function obtenerOfertas(Request $request){
        try{

            //Creamos una instanacia de Guzzle Client.
            $client = new Client(['base_uri' => 'https://run.mocky.io/']);

            //Especifica la URL del endpoint que se desea consumir.
            $endpoint = 'https://run.mocky.io/v3/f65fb26c-0bd5-4031-b502-65220da53450';

            $respuesta = '';

            $promise = $client->getAsync($endpoint)->then(
                function($response) use (&$respuesta){
                    $respuesta = $response->getBody()->getContents();
                }
            )->wait();

            //ES NECESARIO QUE las llaves del json vengan en comillas en algunos casos, si esto sale null.
            $data = json_decode($respuesta,true);

            return response()->json($data, 200);

        }catch(\Exception $e){
            $error = $e->getMessage();
            return response([
                "error"=> $error,
                "message"=> "Ocurrio un error al obtener las ofertas del sistema de ofertas",
                'success'=>false
            ]);
        }
    }
}
