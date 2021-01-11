@if (Session::has(config("sirgrimorum.crudgenerator.status_messages_key")))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    {!! Session::pull(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
</div>
@endif
@if (count($errors->all())>0)
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<?php
$config = CrudGenerator::loadDefaultClasses($config);
$tabla = $config['tabla'];
$campos = $config['campos'];
$nombre = $config['nombre'];
?>
<div class="card border-dark">
    @if (is_array($registro))
        @include("sirgrimorum::crudgen.show_inner",[
            "registro" => $registro,
            "config" => $config,
            "tabla" => $tabla,
            "campos" => $campos,
            "nombre" => $nombre,
        ])
    @else
        @include("sirgrimorum::crudgen.show_inner",[
            "registro" => CrudGenerator::registry_array($config, $registro, 'complete'),
            "config" => $config,
            "tabla" => $tabla,
            "campos" => $campos,
            "nombre" => $nombre,
        ])
    @endif
</div>
@include("sirgrimorum::crudgen.general_scripts", [
    'js_section' => $js_section,
])