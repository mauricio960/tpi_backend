<?php

namespace App\Console\Commands;

use App\Models\TblNEmpresa;
use App\Models\TblNEstadoOferta;
use App\Models\TblNOferta;
use App\Models\TblNPuesto;
use App\Models\TblNRequisitoAspirante;
use App\Models\TblNResponsabilidadPuesto;
use DateTime;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObtenerOfertas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obtener-ofertas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // echo "INICIA";
        // var_dump("OBTENER OFERTAS INICIA");
        // $this->info("Entra al comando correctamente");
        // Log::info("Entra al comando con Log correctamente");
       try{

        // DB::beginTransaction();
         //Creamos una instanacia de Guzzle Client.

         $client = new Client(['base_uri' => 'https://run.mocky.io/']);

         $endpoint = 'https://run.mocky.io/v3/f65fb26c-0bd5-4031-b502-65220da53450';

         $respuesta = '';

         $respuesta = '';

         $promise = $client->getAsync($endpoint)->then(
             function($response) use (&$respuesta){
                 $respuesta = $response->getBody()->getContents();
             }
         )->wait();

         //ES NECESARIO QUE las llaves del json vengan en comillas en algunos casos, si esto sale null.
         $data = json_decode($respuesta,true);

         ["ofertas"=>$ofertas] = $data;


         foreach($ofertas as $oferta_it) {

             $id_oferta = $oferta_it['id'];

             //Buscando si esta está ya registrada.
             $oferta_local = TblNOferta::where('id_oferta_sistema_externo',$id_oferta)->first();

             if($oferta_local != null){

                 //Actualizando solo el estado oferta.
                 $fk_estado_oferta = $oferta_it['fk_estado_oferta'];
                 $fk_estado_oferta = TblNEstadoOferta::find($fk_estado_oferta);
                 $oferta_local->fk_estado_oferta = $fk_estado_oferta->id;
                 $oferta_local->save();

                 //Creando las nuevas responsabilidades.
                 $responsabilidades = $oferta_it['responsabilidades'];
                 foreach($responsabilidades as $responsabilidad_it) {

                     $responsabilidad_coincidente = TblNResponsabilidadPuesto::where('fk_oferta',$oferta_local->id)
                                                     ->where('responsabilidad_puesto',$responsabilidad_it['responsabilidad'])->first();
                     if($responsabilidad_coincidente == null){
                         $responsabilidad = new TblNResponsabilidadPuesto();
                         $responsabilidad->fk_oferta = $oferta_local->id;
                         $responsabilidad->responsabilidad_puesto = $responsabilidad_it['responsabilidad'];
                         $responsabilidad->save();
                     }
                 }

                 //Creando los nuevos requisitos del aspirante.
                 $requisitos = $oferta_it['requisitos'];
                 foreach($requisitos as $requisitos_it) {
                     $requisito_coincidente = TblNRequisitoAspirante::where('fk_oferta',$oferta_local->id)
                                         ->where('requisito',$requisitos_it['requisito'])
                                         ->where('descripcion',$requisitos_it['descripcion'])->first();

                     if($requisito_coincidente == null){
                         $requisito = new TblNRequisitoAspirante();
                         $requisito->fk_oferta = $oferta_local->id;
                         $requisito->requisito = $requisitos_it['requisito'];
                         $requisito->descripcion = $requisitos_it['descripcion'];
                         $requisito->save();
                     }
                 }


             }else{

                 //Creando la nueva oferta.
                 $oferta = new TblNOferta();

                 $fk_estado_oferta = $oferta_it['fk_estado_oferta'];
                 $fk_estado_oferta = TblNEstadoOferta::find($fk_estado_oferta);
                 $oferta->fk_estado_oferta = $fk_estado_oferta->id;

                 $fk_empresa = $oferta_it['fk_empresa'];
                 $fk_empresa = TblNEmpresa::find($fk_empresa);
                 $oferta->fk_empresa = $fk_empresa->id;

                 $fk_puesto = $oferta_it['fk_puesto'];
                 $fk_puesto = TblNPuesto::find($fk_puesto);
                 $oferta->fk_puesto = $fk_puesto->id;

                 $fecha_inicio_oferta = new DateTime($oferta_it['fecha_inicio_oferta']);
                 $fecha_finalizacion_oferta = new DateTime($oferta_it['fecha_finalizacion_oferta']);

                 $oferta->fecha_inicio_oferta =$fecha_inicio_oferta;
                 $oferta->fecha_finalizacion_oferta=$fecha_finalizacion_oferta;
                 $oferta->empresa =null;
                 $oferta->puesto = null;

                 $oferta->descripcion = $oferta_it['descripcion'];
                 $oferta->id_oferta_sistema_externo = $oferta_it['id'];

                 $oferta->save();

                 //Creando las nuevas responsabilidades.
                 $responsabilidades = $oferta_it['responsabilidades'];
                 foreach($responsabilidades as $responsabilidad_it) {

                     $responsabilidad = new TblNResponsabilidadPuesto();
                     $responsabilidad->fk_oferta = $oferta->id;
                     $responsabilidad->responsabilidad_puesto = $responsabilidad_it['responsabilidad'];
                     $responsabilidad->save();

                 }

                 //Creando los nuevos requisitos del aspirante.
                 $requisitos = $oferta_it['requisitos'];
                 foreach($requisitos as $requisitos_it) {

                     $requisito = new TblNRequisitoAspirante();
                     $requisito->fk_oferta = $oferta->id;
                     $requisito->requisito = $requisitos_it['requisito'];
                     $requisito->descripcion = $requisitos_it['descripcion'];
                     $requisito->save();
                 }
             }

         } //foreach
        //  DB::commit();

       }catch(\Exception $e){
        // DB::rollBack();
    //     var_dump("OBTENER OFERTAS FALLA");
    //     echo "no se algo falla";
    //     $this->info("Termina el comando con error correctamente");
    //    Log::info("Termina el comando con error con Log correctamente");
       }

    //    $this->info("Termina el comando correctamente");
    //    Log::info("Termina el comando con Log correctamente");


        return Command::SUCCESS;
    }
}
