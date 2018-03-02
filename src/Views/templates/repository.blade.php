{?php}


namespace App\Repositories;

<?php
$yavan = [];
foreach($config['campos'] as $campo => $datos){
    if ($datos['tipo']=='relationship' || $datos['tipo']=='relationships' || $datos['tipo']=='relationshipssel'){
        if (!in_array($datos['modelo'], $yavan)){
            $yavan[] = $datos['modelo'];
            $otroModelo = basename($datos['modelo']);
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
            $otroModelo = basename($datos['modelo']);
            $otromodelo = strtolower($otroModelo);
?>
    /**
     * Get all of the {model}s for a given user.
     *
     * @param  {{$otroModelo}}  ${{$otromodelo}}
     * @return Collection
     */
    public function for{{$otroModelo}}({{$otroModelo}} ${{$otromodelo}})
    {
        return ${{$otromodelo}}->{model}s()
                    ->orderBy('created_at', 'asc')
                    ->get();
    }
<?php
        }
    }
}
?>
}
