{?php}


namespace App\Repositories;

<?php
$yavan = [];
foreach($config['campos'] as $campo => $datos){
    if ($datos['tipo']=='relationship' || $datos['tipo']=='relationships' || $datos['tipo']=='relationshipssel'){
        if (!in_array($datos['modelo'], $yavan)){
            $yavan[] = $datos['modelo'];
            $otroModelo = class_basename($datos['modelo']);
            $otromodelo = strtolower($otroModelo);
?>
use {{$datos['modelo']}};
<?php
        }
    }
}
?>
class {Model}Repository
{
<?php
$yavan = [];
foreach($config['campos'] as $campo => $datos){
    if ($datos['tipo']=='relationship' || $datos['tipo']=='relationships' || $datos['tipo']=='relationshipssel'){
        if (!in_array($datos['modelo'], $yavan)){
            $yavan[] = $datos['modelo'];
            $otroModelo = class_basename($datos['modelo']);
            $otromodelo = strtolower($otroModelo);
?>
    /**
     * Get all of the {{\Illuminate\Support\Str::plural($modelo)}} for a given user.
     *
     * @param  {{$otroModelo}}  ${{$otromodelo}}
     * @return Collection
     */
    public function for{{$otroModelo}}({{$otroModelo}} ${{$otromodelo}})
    {
        return ${{$otromodelo}}->{{\Illuminate\Support\Str::plural($modelo)}}()
                    ->orderBy('created_at', 'asc')
                    ->get();
    }
<?php
        }
    }
}
?>
}
