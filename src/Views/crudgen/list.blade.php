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
$tablaid = $tabla . "_" . str_random(5);
$campos = $config['campos'];
if (isset($config['botones'])) {
    if ($config['botones'] != "") {
        $botones = $config['botones'];
    } else {
        $botones = [];
    }
} else {
    $botones = [];
}
if (isset($config['relaciones'])) {
    $relaciones = $config['relaciones'];
}
$identificador = $config['id'];
$nombre = $config['nombre'];
        
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
<table class="table table-striped table-bordered" id='list_{{ $tablaid }}'>
    <thead>
        <tr>
            
            @foreach($campos as $columna => $datos)
            <th>{{ ucfirst($datos['label']) }}</th>
            @endforeach
            @if (count($botones)>0)
            <th></th>
            @endif
            
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $key => $value)
        <tr>
            
            @foreach($campos as $columna => $datos)
            <td>
                @if (isset($datos["pre"]))
                {!! $datos["pre"] !!}
                @endif
                @if ($datos['tipo']=="relationship")
                @if (Sirgrimorum\Cms\CrudLoader\CrudController::hasRelation($value,$datos['modelo']))
                @if(array_key_exists('enlace',$datos))
                <a href="{{ str_replace([":modelId",":modelName"],[$value->{$datos['modelo']}->{$datos['id']},$value->{$datos['modelo']}->{$datos['nombre']}],str_replace([urlencode(":modelId"),urlencode(":modelName")],[$value->{$datos['modelo']}->{$datos['id']},$value->{$datos['modelo']}->{$datos['nombre']}],$datos['enlace'])) }}">
                    @endif
                    @if($value->{$datos['modelo']})
                    @if(is_array($datos['campo']))
                    <?php
                    $prefijoCampo = "";
                    foreach ($datos['campo'] as $campo) {
                        echo $prefijoCampo . $value->{$datos['modelo']}->{$campo};
                        $prefijoCampo = ", ";
                    }
                    ?>
                    @else
                    {!! $value->{$datos['modelo']}->{$datos['campo']} !!}
                    @endif
                    @else
                    -
                    @endif
                    @if(array_key_exists('enlace',$datos))
                </a>
                @endif
                @elseif (Sirgrimorum\Cms\CrudLoader\CrudController::hasRelation($value,$columna))
                @if(array_key_exists('enlace',$datos))
                <a href="{{ str_replace([":modelId", ":modelName"],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
                    @endif
                    @if($value->{$columna})
                    @if(is_array($datos['campo']))
                    <?php
                    $prefijoCampo = "";
                    foreach ($datos['campo'] as $campo) {
                        echo $prefijoCampo . $value->{$columna}->{$campo};
                        $prefijoCampo = ", ";
                    }
                    ?>
                    @else
                    {!! $value->{$columna}->{$datos['campo']} !!}
                    @endif
                    @else
                    -
                    @endif
                    @if(array_key_exists('enlace',$datos))
                </a>
                @endif
                @else
                -
                @endif
                @elseif ($datos['tipo']=="relationships")
                @if (count($value->{$datos['modelo']}()->get())>0)
                @foreach($value->{$datos['modelo']}()->get() as $sub)
                <p>
                    @if(array_key_exists('enlace',$datos))
                    <a href="{{ str_replace([":modelId", ":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
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
                </p>
                @endforeach
                @elseif (count($value->{$columna}()->get())>0)
                @foreach($value->{$columna}()->get() as $sub)
                <p>
                    @if(array_key_exists('enlace',$datos))
                    <a href="{{ str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],$datos['enlace'])) }}">
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
                </p>
                @endforeach
                @else
                -
                @endif
                @elseif ($datos['tipo']=="select")
                @if (array_key_exists($value->{$columna},$datos['opciones']))
                {!! $datos['opciones'][$value->{$columna}] !!}
                @else
                -
                @endif
                @elseif ($datos['tipo']=="function")
                @if (isset($datos['format']))
                @if (is_array($datos['format']))
                {{ number_format($value->{$columna}(),$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
                @else
                {{ number_format($value->{$columna}()) }}
                @endif
                @else            
                {!! $value->{$columna}() !!}
                @endif
                @elseif ($datos['tipo']=="url")
                <a href='{{ $value->{$columna} }}' target='_blank'>{{ $value->{$columna} }}</a>
                @elseif ($datos['tipo']=="file")
                @if (isset($datos['pathImage']))
                @if ($value->{$columna} == "" )
                -
                @else
                @if (isset($datos['enlace']))
                <a href='{{ str_replace("{value}", $value->{$columna}, $datos['enlace'] ) }}' target="_blank">
                    @endif
                    @if (preg_match('/(\.jpg|\.png|\.bmp)$/', $value->{$columna}))
                    <?php
                    if ($datos['saveCompletePath']){
                        $image_name = basename($value->{$columna});
                    }else{
                        $image_name = $value->{$columna};
                    }
                    ?>
                    <image class="img-responsive" src="{{ asset('/images/' . str_finish($datos['pathImage'],'/') . $image_name ) }}" alt="{{ $columna }}"/>
                    @else
                    <image class="img-responsive" src="{{ asset('/images/img/file.png' ) }}" alt="{{ $columna }}"/>
                    @endif
                    @if (isset($datos['enlace']))
                </a>
                @endif
                @endif
                @else
                @if ($value->{$columna} == "" )
                -
                @else
                @if (isset($datos['enlace']))
                <a href='{{ str_replace("{value}", $value->{$columna}, $datos['enlace'] ) }}' target="_blank">
                    @endif
                    {!! $value->{$columna} !!}
                    @if (isset($datos['enlace']))
                </a>
                @endif
                @endif
                @endif
                @else
                @if(array_key_exists('enlace',$datos))
                <a href="{{ str_replace([":modelId",":modelName"],[$value->{$identificador},$value->{$nombre}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$value->{$identificador},$value->{$nombre}],$datos['enlace'])) }}">
                    @endif
                    @if ($datos['tipo']=="number" && isset($datos['format']))
                    @if (is_array($datos['format']))
                    {{ number_format($value->{$columna},$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
                    @else
                    {{ number_format($value->{$columna}) }}
                    @endif
                    @else            
                    {!! $value->{$columna} !!}
                    @endif
                    @if(array_key_exists('enlace',$datos))
                </a>
                @endif
                @endif
                @if (isset($datos["post"]))
                {!! " " . $datos["post"] !!}
                @endif
            </td>
            @endforeach
            
            @if (count($botones)>0)
            <td>
                @if (is_array($botones))
                @foreach ($botones as $boton)
                {!! str_replace([":modelId",":modelName"],[$value->{$identificador},$value->{$nombre}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$value->{$identificador},$value->{$nombre}],$boton)) !!}
                @endforeach
                @else
                {!! str_replace([":modelId",":modelName"],[$value->{$identificador},$value->{$nombre}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$value->{$identificador},$value->{$nombre}],$botones)) !!}
                @endif
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<?php if (config("sirgrimorum.crudgenerator.css_section") != "") { ?>
    @section(config("sirgrimorum.crudgenerator.css_section"))
    @parent
    <?php
}
if (str_contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    echo '<link href="' . config("sirgrimorum.crudgenerator.jquerytables_path") . '" rel="stylesheet" type="text/css">';
} else {
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/jquery.dataTables.min.css") . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/dataTables.bootstrap.css") . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/dataTables.colReorder.min.css") . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/dataTables.fixedHeader.min.css") . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/dataTables.responsive.css") . '" rel="stylesheet" type="text/css">';
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/css/dataTables.tableTools.min.css") . '" rel="stylesheet" type="text/css">';
}
if (str_contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
    echo '<link href="' . config("sirgrimorum.crudgenerator.confirm_path") . '" rel="stylesheet" type="text/css">';
} else {
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/css/jquery-confirm.min.css") . '" rel="stylesheet" type="text/css">';
}
if (config("sirgrimorum.crudgenerator.css_section") != "") {
    ?>
    @stop
    <?php
}
if (config("sirgrimorum.crudgenerator.js_section") != "") {
    ?>
    @section(config("sirgrimorum.crudgenerator.js_section"))
    @parent
    <?php
}
if (str_contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    //echo '<script src="' . config("sirgrimorum.crudgenerator.jquerytables_path") . '"></script>';
} else {
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/jquery.dataTables.min.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/dataTables.bootstrap.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/dataTables.colReorder.min.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/dataTables.fixedHeader.min.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/dataTables.responsive.min.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/js/dataTables.tableTools.min.js") . '"></script>';
}
if (str_contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
    echo '<script src="' . config("sirgrimorum.crudgenerator.confirm_path") . '"></script>';
    echo '<script src="' . asset("vendor/sirgrimorum/confirm/js/rails.js") . '"></script>';
} else {
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/jquery-confirm.min.js") . '"></script>';
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/rails.js") . '"></script>';
}
?>
<script>
    $(document).ready(function() {
    var lista_{{ $tabla }} = $('#list_{{ $tablaid }}').DataTable({
            responsive: true,
            dom: 'Rlfrtip',
            tableTools: {
            sSwfPath: "/swf/copy_csv_xls_pdf.swf"
            },
            @if (isset($config['orden']))
            order : {{ json_encode($config['orden']) }},
            @endif
    });
    //new $.fn.dataTable.FixedHeader(lista_{{ $tabla }});
    });
</script>
<?php
if (config("sirgrimorum.crudgenerator.js_section") != "") {
    ?>
    @stop
    <?php
}
?>
