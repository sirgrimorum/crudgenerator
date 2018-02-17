@if (Session::has('message'))
<div class="alert alert-info">{{ Session::pull('message') }}</div>
@endif
@if (count($errors->all())>0)
<div class="alert alert-danger">
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

if (isset($config['render'])) {
    $selects = array('column_name as field', 'column_type as type', 'is_nullable as null', 'column_key as key', 'column_default as default', 'extra as extra');
    $table_describes = DB::table('information_schema.columns')
            ->where('table_name', '=', $tabla)
            ->get($selects);
    foreach ($table_describes as $k => $v) {
        if (($kt = array_search($v, $table_describes)) !== false and $k != $kt) {
            unset($table_describes[$kt]);
        }
    }
}
?>
<div class="panel panel-default">
    <div class="panel-heading text-center"><h2>{{ $registro->{$nombre} }}</h2></div>
    <div class="panel-body">
        
        @foreach($campos as $columna => $datos)
        <strong>{{ ucfirst($datos['label']) }}: </strong>
        @if (isset($datos["pre"]))
        {{ $datos["pre"] }}
        @endif
        @if ($datos['tipo']=="relationship")
        @if (count($registro->{$datos['modelo']}))
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$registro->{$datos['modelo']}->{$datos['id']},$registro->{$datos['modelo']}->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$registro->{$datos['modelo']}->{$datos['id']},$registro->{$datos['modelo']}->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            @if(is_array($datos['campo']))
            <?php
            $prefijoCampo = "";
            foreach ($datos['campo'] as $campo) {
                echo $prefijoCampo . $registro->{$datos['modelo']}->{$campo};
                $prefijoCampo = ", ";
            }
            ?>
            @else
            {!! $registro->{$datos['modelo']}->{$datos['campo']} !!}
            @endif
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        @elseif (count($registro->{$columna}))
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            @if(is_array($datos['campo']))
            <?php
            $prefijoCampo = "";
            foreach ($datos['campo'] as $campo) {
                echo $prefijoCampo . $registro->{$columna}->{$campo};
                $prefijoCampo = ", ";
            }
            ?>
            @else
            {!! $registro->{$columna}->{$datos['campo']} !!}
            @endif
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        @endif
        @elseif ($datos['tipo']=="relationships")
        @if (count($registro->{$columna}()->get())>0)
        @foreach($registro->{$columna}()->get() as $sub)
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            @if(is_array($datos['campo']))
            <?php
            $prefijoCampo = "";
            foreach ($datos['campo'] as $campo) {
                echo $prefijoCampo . $sub->{$campo};
                $prefijoCampo = ", ";
            }
            ?>
            @else
            {!! $sub->{$datos['campo']} !!}
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
                    if ($datos['saveCompletePath']){
                        $image_name = basename($registro->{$columna});
                    }else{
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
        @endforeach
        
    </div>
</div>
