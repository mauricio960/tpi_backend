<?php

namespace App\Console\Commands;

use App\Models\TblNAplicacionOferta;
use App\Models\TblNEstudiante;
use App\Models\User;
use Illuminate\Console\Command;

class EnviarAplicaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar-aplicaciones';

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
        try{
            $aplicaciones_ofertas = TblNAplicacionOferta::where('enviado',false)->get();
            $aplicaciones_ofertas->load('oferta');

            $lista_aplicaciones_ofertas =[];
            foreach($aplicaciones_ofertas as $aplicacion){
                $usuario_estudiante = User::find($aplicacion->fk_usuario);
                $estudiante = TblNEstudiante::find($usuario_estudiante?->fk_estudiante);


                array_push($lista_aplicaciones_ofertas,[
                    'id'=>$aplicacion->id,
                    'oferta'=>$aplicacion->oferta,
                    'estudiante'=>$estudiante,
                ]);

                $aplicacion->enviado = true;
                $aplicacion->save();
            }

            //ENVIO PERO NO HAY ENDPOINT.


        }catch(\Exception $e){
        }
        return Command::SUCCESS;
    }
}
