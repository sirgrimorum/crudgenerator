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
$tabla = $config['tabla'];
$campos = $config['campos'];
$botones = $config['botones'];
$nombre = $config['nombre'];
if (isset($config['relaciones'])) {
    $relaciones = $config['relaciones'];
}
$identificador = $config['id'];
?>
<div class="panel panel-default">
    <div class="panel-heading text-center"><h2>{{ $registro->{$nombre} }}</h2></div>
    <div class="panel-body">

        @foreach($campos as $columna => $datos)
        @if (CrudLoader::inside_array($datos,"hide","show")===false)
        <strong>{{ ucfirst($datos['label']) }}: </strong>
        @if (isset($datos["pre"]))
        {{ $datos["pre"] }}
        @endif
        @if ($datos['tipo']=="relationship")
        @if (CrudLoader::hasRelation($registro, $columna))
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            {!! CrudLoader::getNombreDeLista($registro->{$columna}, $datos['campo']) !!}
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        @else
        {!! print_r($registro->{$columna},true) !!}
        @endif
        @elseif ($datos['tipo']=="relationships")
        @if (CrudLoader::hasRelation($registro, $columna))
        @foreach($registro->{$columna}()->get() as $sub)
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            {!! CrudLoader::getNombreDeLista($sub, $datos['campo']) !!}
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        ,
        @endforeach
        @else
        @endif
        @elseif ($datos['tipo']=="relationshipssel")
        @if (CrudLoader::hasRelation($registro, $columna))
        @foreach($registro->{$columna}()->get() as $sub)
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            {!! CrudLoader::getNombreDeLista($sub, $datos['campo']) !!}
                        @if(array_key_exists('columnas',$datos))
                            @if(is_array($datos['columnas']))
                                @if (is_object($sub->pivot))
                                (
                                @foreach($datos['columnas'] as $infoPivote)
                                    @if($infoPivote['type'] != "hidden" && $infoPivote['type'] != "label")
                                        @if ($infoPivote['type'] == "number" && isset($infoPivote['format']))
                                        {!! number_format($sub->pivot->{$infoPivote['campo']},$infoPivote['format'][0],$infoPivote['format'][1],$infoPivote['format'][2]) !!}
                                        @elseif ($infoPivote['type'] == "select" && isset($infoPivote['opciones']))
                                        {!! $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}] !!}
                                        @else
                                        {!! $sub->pivot->{$infoPivote['campo']} !!},
                                        @endif
                                    @endif
                                @endforeach
                                )
                                @endif
                            @endif
                        @endif
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        ,
        @endforeach
        @else
        @endif
        @elseif ($datos['tipo']=="select")
        @if (array_key_exists($registro->{$columna},$datos['opciones']))
        {{ $datos['opciones'][$registro->{$columna}] }}
        @endif
        @elseif ($datos['tipo']=="checkbox")
        @if (is_array($datos['value']))
        @if (array_key_exists($registro->{$columna},$datos['value']))
        {!! $datos['value'][$registro->{$columna}] !!}
        @else
        @if($registro->{$columna}===true)
        {{trans('crudgenerator::admin.layout.labels.yes')}}
        @else
        {{trans('crudgenerator::admin.layout.labels.no')}}
        @endif
        @endif
        @else
        @if ($datos['value']==$registro->{$columna} && $registro->{$columna} ==true)
        {{trans('crudgenerator::admin.layout.labels.yes')}}
        @elseif($registro->{$columna}==$datos['value'])
        {!! $datos['value'] !!}
        @elseif ($registro->{$columna}==true)
        {!! $datos['value'] !!}
        @else
        {{trans('crudgenerator::admin.layout.labels.no')}}
        @endif
        @endif
        @elseif($datos['tipo']=="date" || $datos['tipo']=="datetime" || $datos['tipo']=="time")
                <?php
                $format = "Y-m-d H:i:s";
                if($datos['tipo']=="date"){
                    $format = "Y-m-d";
                }elseif($datos['tipo']=="time"){
                    $format = "H:i:s";
                }
                if (isset($datos["format"]["carbon"])) {
                    $format = $datos["format"]["carbon"];
                } elseif (isset(trans("crudgenerator::admin.formats.carbon")[$datos['tipo']])) {
                    $format = trans("crudgenerator::admin.formats.carbon.".$datos['tipo']);
                }
                $dato = $registro->{$columna};
                
                if ($dato != "") {
                    if (isset($datos["timezone"])) {
                        $timezone = $datos["timezone"];
                    } else {
                        $timezone = config("app.timezone");
                    }
                    $date = new \Carbon\Carbon($dato, $timezone);
                    $dato = $date->format($format);
                }
                //echo $dato;
                ?>
        @elseif ($datos['tipo']=="function")
        @if (isset($datos['format']))
        @if (is_array($datos['format']))
        {{ number_format($registro->{$columna}(),$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
        @else
        {{ number_format($registro->{$columna}()) }}
        @endif
        @else            
        {{ $registro->{$columna}() }}
        @endif
        @elseif ($datos['tipo']=="url")
        <a href='{{ $registro->{$columna} }}' target='_blank'>{{ $registro->{$columna} }}</a>
        @elseif ($datos['tipo']=="file" && isset($datos['pathImage']))
        <div class="container">
            @if ($registro->{$columna} == "" )
            -
            @else
            @if (isset($datos['enlace']))
            <a href='{{ str_replace("{value}", $registro->{$columna}, $datos['enlace'] ) }}' target="_blank">
                @endif
                @if (preg_match('/(\.jpg|\.png|\.bmp)$/', $registro->{$columna}))
                <?php
                if ($datos['saveCompletePath']) {
                    $image_name = basename($registro->{$columna});
                } else {
                    $image_name = $registro->{$columna};
                }
                ?>
                <image class="img-thumbnail" src="{{ asset('/images/' . str_finish($datos['pathImage'],'/') . $image_name ) }}" alt="{{ $columna }}"/>
                @else
                <image class="img-thumbnail" src="{{ asset('/images/img/file.png' ) }}" alt="{{ $columna }}"/>
                @endif
                @if (isset($datos['enlace']))
            </a>
            @endif
            @endif
        </div>
        @elseif ($datos['tipo']=="file")
        @if ($registro->{$columna} == "" )
        -
        @else
        @if (isset($datos['enlace']))
        <a href='{{ str_replace("{value}", $registro->{$columna}, $datos['enlace'] ) }}' target="_blank">
            @endif
            {!! $registro->{$columna} !!}
            @if (isset($datos['enlace']))
        </a>
        @endif
        @endif
        @elseif ($datos['tipo']=="number" && isset($datos['format']))
        @if (is_array($datos['format']))
        {{ number_format($registro->{$columna},$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
        @else
        {{ number_format($registro->{$columna}) }}
        @endif
        @else
        {!! $registro->{$columna} !!}
        @endif
        @if (isset($datos["post"]))
        {!! " " . $datos["post"] !!}
        @endif
        <br/>
        @endif
        @endforeach

    </div>
</div>
