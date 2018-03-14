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
$modelo = strtolower(class_basename($config["modelo"]));
$modeloUCF = ucfirst(strtolower(class_basename($config["modelo"])));
$base_url = route('sirgrimorum_home', App::getLocale());
$plural = $modelo . 's';
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.plural")) {
    $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
} else {
    $plurales = ucfirst($plural);
}
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = ucfirst($modelo);
}
$tabla = $config['tabla'];
$tablaid = $tabla . "_" . str_random(5);
$campos = $config['campos'];
if (isset($config['botones'])) {
    if ($config['botones'] != "") {
        if (is_array($config['botones'])) {
            $botones = $config['botones'];
        } else {
            $botones = [$config['botones']];
        }
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
    <thead class="thead-dark">
        <tr>
            @foreach($campos as $columna => $datos)
            @if (CrudGenerator::inside_array($datos,"hide","list")===false)
            <th>{{ ucfirst($datos['label'])}}</th>
            @endif
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $key => $value)
        <tr id = "{{ $tablaid }}__{{ $value->{$config['id']} }}|{!! $value->{$config['nombre']} !!}">
            @foreach($campos as $columna => $datos)
            @if (CrudGenerator::inside_array($datos,"hide","list")===false)
            <td>
                @if (isset($datos["pre"]))
                {!! $datos["pre"] !!}
                @endif
                @if ($datos['tipo']=="relationship")
                @if (CrudGenerator::hasRelation($value,$columna))
                @if(array_key_exists('enlace',$datos))
                <a href="{{ str_replace([":modelId", ":modelName"],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
                    @endif
                    @if($value->{$columna})
                    {!! CrudGenerator::getNombreDeLista($value->{$columna}, $datos['campo']) !!}
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
                @if (CrudGenerator::hasRelation($value, $columna))
                <ul>
                @foreach($value->{$columna}()->get() as $sub)
                <li>
                    @if(array_key_exists('enlace',$datos))
                    <a href="{{ str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],$datos['enlace'])) }}">
                        @endif
                        {!! CrudGenerator::getNombreDeLista($sub, $datos['campo']) !!}
                        @if(array_key_exists('enlace',$datos))
                    </a>
                    @endif
                </li>
                @endforeach
                </ul>
                @else
                -
                @endif
                @elseif ($datos['tipo']=="relationshipssel")
                @if (CrudGenerator::hasRelation($value, $columna))
                <ul>
                @foreach($value->{$columna}()->get() as $sub)
                <li>
                    @if(array_key_exists('enlace',$datos))
                    <a href="{{ str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],$datos['enlace'])) }}">
                        @endif
                        {!! CrudGenerator::getNombreDeLista($sub, $datos['campo']) !!}
                        @if(array_key_exists('enlace',$datos))
                    </a>
                    @endif
                        @if(array_key_exists('columnas',$datos))
                            @if(is_array($datos['columnas']))
                                @if (is_object($sub->pivot))
                                <ul>
                                @foreach($datos['columnas'] as $infoPivote)
                                    @if($infoPivote['type'] != "hidden" && $infoPivote['type'] != "label")
                                    <li>
                                        @if ($infoPivote['type'] == "number" && isset($infoPivote['format']))
                                        {!! number_format($sub->pivot->{$infoPivote['campo']},$infoPivote['format'][0],$infoPivote['format'][1],$infoPivote['format'][2]) !!}
                                        @elseif ($infoPivote['type'] == "select" && isset($infoPivote['opciones']))
                                        {!! $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}] !!}
                                        @else
                                        {!! $sub->pivot->{$infoPivote['campo']} !!},
                                        @endif
                                    </li>
                                    @endif
                                @endforeach
                                </ul>   
                                @endif
                            @endif
                        @endif
                </li>
                @endforeach
                </ul>
                @else
                -
                @endif
                @elseif ($datos['tipo']=="select")
                @if (array_key_exists($value->{$columna},$datos['opciones']))
                {!! $datos['opciones'][$value->{$columna}] !!}
                @else
                -
                @endif
                @elseif ($datos['tipo']=="checkbox" || $datos['tipo']=="radio")
                @if (is_array($datos['value']))
                @if (array_key_exists($value->{$columna},$datos['value']))
                {!! $datos['value'][$value->{$columna}] !!}
                @else
                @if($value->{$columna}===true)
                {{trans('crudgenerator::admin.layout.labels.yes')}}
                @else
                {{trans('crudgenerator::admin.layout.labels.no')}}
                @endif
                @endif
                @else
                @if ($datos['value']==$value->{$columna} && $value->{$columna} ==true)
                {{trans('crudgenerator::admin.layout.labels.yes')}}
                @elseif($value->{$columna}==$datos['value'])
                {!! $datos['value'] !!}
                @elseif ($value->{$columna}==true)
                {!! $datos['value'] !!}
                @else
                {{trans('crudgenerator::admin.layout.labels.no')}}
                @endif
                @endif
                @elseif($datos['tipo']=="date" || $datos['tipo']=="datetime" || $datos['tipo']=="time")
                <?php
                $format = "Y-m-d H:i:s";
                if ($datos['tipo'] == "date") {
                    $format = "Y-m-d";
                } elseif ($datos['tipo'] == "time") {
                    $format = "H:i:s";
                }
                if (isset($datos["format"]["carbon"])) {
                    $format = $datos["format"]["carbon"];
                } elseif (isset(trans("crudgenerator::admin.formats.carbon")[$datos['tipo']])) {
                    $format = trans("crudgenerator::admin.formats.carbon." . $datos['tipo']);
                }
                $dato = $value->{$columna};

                if ($dato != "") {
                    if (isset($datos["timezone"])) {
                        $timezone = $datos["timezone"];
                    } else {
                        $timezone = config("app.timezone");
                    }
                    $date = new \Carbon\Carbon($dato, $timezone);
                    $dato = $date->format($format);
                }
                echo $dato;
                ?>
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
                @elseif ($datos['tipo']=="article" && class_exists(config('sirgrimorum.transarticles.default_articles_model')))
                @transarticles($value->{$columna})
                @elseif ($datos['tipo']=="url")
                <a href='{{ $value->{$columna} }}' target='_blank'><i class="mt-2 fa fa-link fa-lg" aria-hidden="true"></i></a>
                @elseif ($datos['tipo']=="file")
                @if ($value->{$columna} == "" )
                -
                @else
                <?php
                $auxprevio = $value->{$columna};
                $filename = str_start($auxprevio, str_finish($datos['path'], '\\'));
                $tipoFile = CrudGenerator::filenameIs($auxprevio, $datos);
                $auxprevioName = substr($auxprevio, stripos($auxprevio, '__') + 2, stripos($auxprevio, '.', stripos($auxprevio, '__')) - (stripos($auxprevio, '__') + 2));
                ?>
                @if($tipoFile == 'image')
                <figure class="figure">
                    <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >                   
                        <img src="{{asset($filename)}}" class="figure-img img-fluid rounded" alt="{{$auxprevioName}}">
                        <figcaption class="figure-caption">{{$auxprevioName}}</figcaption>
                    </a>
                </figure>
                @else
                <ul class="fa-ul">
                    <li class="pl-2">
                        @switch($tipoFile)
                        @case('image')
                        <i class="fa fa-lg fa-li" aria-hidden="true"><img class="w-75 rounded" style="cursor: pointer;" src="{{asset($filename)}}"></i>
                        @break
                        @case('video')
                        <i class="fa fa-film fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('audio')
                        <i class="fa fa-file-audio-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('pdf')
                        <i class="fa fa-file-pdf-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('text')
                        <i class="fa fa-file-text-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('office')
                        <i class="fa fa-file-word-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('compressed')
                        <i class="fa fa-file-archive-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('other')
                        <i class="fa fa-file-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @endswitch
                    <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                        {{$auxprevioName}}
                    </a>
                    </li>
                </ul>
                @endif
                @endif
                @elseif ($datos['tipo']=="files")
                <?php
                try {
                    $auxprevios = json_decode($value->{$columna});
                    if (!is_array($auxprevios)){
                        $auxprevios = [];
                    }
                } catch (Exception $ex) {
                    $auxprevios = [];
                }
                ?>
                @if (count($auxprevios)==0)
                -
                @else
                <ul class="fa-ul">
                @foreach($auxprevios as $datoReg)
                @if(is_object($datoReg))
                <?php
                $filename = str_start($datoReg->file, str_finish($datos['path'], '\\'));
                $tipoFile =CrudGenerator::filenameIs($datoReg->file,$datos);
                ?>
                    <li class="pl-2">
                        @switch($tipoFile)
                        @case('image')
                        <i class="fa fa-lg fa-li" aria-hidden="true"><img class="w-75 rounded" style="cursor: pointer;" src="{{asset($filename)}}"></i>
                        @break
                        @case('video')
                        <i class="fa fa-film fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('audio')
                        <i class="fa fa-file-audio-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('pdf')
                        <i class="fa fa-file-pdf-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('text')
                        <i class="fa fa-file-text-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('office')
                        <i class="fa fa-file-word-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('compressed')
                        <i class="fa fa-file-archive-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @case('other')
                        <i class="fa fa-file-o fa-lg fa-li" aria-hidden="true"></i>
                        @break
                        @endswitch
                    <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                        {{$datoReg->name}}
                    </a>
                    </li>
                @endif
                @endforeach
                </ul>
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
            @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

@if ($modales)
@if (config("sirgrimorum.crudgenerator.modal_section") != "")
@push(config("sirgrimorum.crudgenerator.modal_section"))
@endif
<!-- Modal Comodín -->
<div class="modal fade" id="modal_{{ $tablaid }}" tabindex="-1" role="dialog" aria-labelledby="modal_{{ $tablaid }}_Label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_{{ $tablaid }}_Label">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal_{{ $tablaid }}_body">

            </div>
            <div class="modal-footer" id="modal_{{ $tablaid }}_footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
@if(old("_action")=="create" || old("_action")=="edit")
<!-- Modal for old input -->
@include('sirgrimorum::admin.' . old("_action") . ".modal", ["modelo" => $modeloUCF,"base_url" => $base_url, "plural" => $plural,"config" => $config,"registro"=>old("_registro")])
@endif
@if (config("sirgrimorum.crudgenerator.modal_section") != "")
@endpush
@endif
@endif
<?php if ($css_section != "") { ?>
    @push($css_section)

    <?php
}
if (str_contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    echo '<link href="' . config("sirgrimorum.crudgenerator.jquerytables_path") . '" rel="stylesheet" type="text/css">';
} else {
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datatables.min.css") . '" rel="stylesheet" type="text/css">';
}
if (str_contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
    echo '<link href="' . config("sirgrimorum.crudgenerator.confirm_path") . '" rel="stylesheet" type="text/css">';
} else {
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/css/jquery-confirm.min.css") . '" rel="stylesheet" type="text/css">';
}
if ($css_section != "") {
    ?>
    @endpush
    <?php
}
if ($js_section != "") {
    ?>
    @push($js_section)

    <?php
}
if (str_contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    //echo '<script src="' . config("sirgrimorum.crudgenerator.jquerytables_path") . '"></script>';
} else {
    echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datatables.min.js") . '"></script>';
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
    @if (old("_action") == "create" || old("_action") == "edit")
            $("#{{$modeloUCF}}_{{old('_action')}}_modal").modal("show");
    @endif
            var lista_{{ $tabla }} = $('#list_{{ $tablaid }}').DataTable({
    responsive: true,
            dom: 'Bfrtip',
            select: true,
            colReorder: true,
            fixedHeader: true,
            buttons: [
            {
            extend: 'colvis',
                    titleAttr: '{!! trans("crudgenerator::datatables.buttons.t_colvis") !!}'
            },
            {
            extend: 'selectAll',
                    titleAttr: '{!! trans("crudgenerator::datatables.buttons.t_selectAll") !!}'
            },
            {
            extend: 'selectNone',
                    titleAttr: '{!! trans("crudgenerator::datatables.buttons.t_selectNone") !!}'
            },
            {
            extend: 'collection',
                    text: '{!! trans("crudgenerator::datatables.buttons.export") !!}',
                    titleAttr: '{!! trans("crudgenerator::datatables.buttons.t_export") !!}',
                    buttons:[ 'copy', 'excel', 'pdf', 'print']
            },
<?php
if (is_array($botones)) {
    foreach ($botones as $butName => $boton) {
        $buttonClass = "";
        if (is_string($butName)) {
            $textBoton = $butName;
            $titleBoton = $butName;
            $confirmTheme = config('sirgrimorum.crudgenerator.confirm_theme');
            $confirmIcon = config('sirgrimorum.crudgenerator.confirm_icon');
            if (($confirmContent = trans('crudgenerator::' . strtolower($modelo) . '.messages.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.messages.confirm_destroy') {
                $confirmContent = trans('crudgenerator::admin.messages.confirm_destroy');
            }
            $confirmYes = trans('crudgenerator::admin.layout.labels.yes');
            $confirmNo = trans('crudgenerator::admin.layout.labels.no');
            $confirmTitle = '';
            if (stripos($boton, "<a") >= 0) {
                try {
                    $nodes = CrudGenerator::extract_tags($boton, "a");
                    if (isset($nodes[0]['attributes']['href'])) {
                        $urlBoton = $nodes[0]['attributes']['href'];
                    } else {
                        $urlBoton = "Dice que no tiene";
                    }
                    $textBoton = $nodes[0]['contents'];
                    if (!isset($nodes[0]['attributes']['title'])) {
                        $titleBoton = $plurales . " - " . $textBoton;
                    } else {
                        $titleBoton = $nodes[0]['attributes']['title'];
                    }
                    if (isset($nodes[0]['attributes']['data-confirmtheme'])) {
                        $confirmTheme = $nodes[0]['attributes']['data-confirmtheme'];
                    }
                    if (isset($nodes[0]['attributes']['data-confirmicon'])) {
                        $confirmIcon = $nodes[0]['attributes']['data-confirmicon'];
                    }
                    if (isset($nodes[0]['attributes']['data-yes'])) {
                        $confirmYes = $nodes[0]['attributes']['data-yes'];
                    }
                    if (isset($nodes[0]['attributes']['data-no'])) {
                        $confirmNo = $nodes[0]['attributes']['data-no'];
                    }
                    if (isset($nodes[0]['attributes']['data-confirm'])) {
                        $confirmContent = $nodes[0]['attributes']['data-confirm'];
                    }
                    if (isset($nodes[0]['attributes']['data-confirmtitle'])) {
                        $confirmTitle = $nodes[0]['attributes']['data-confirmtitle'];
                    }
                    if (isset($nodes[0]['attributes']['class'])) {
                        $buttonClass = $nodes[0]['attributes']['class'];
                    }
                } catch (Exception $exc) {
                    $urlBoton = $boton;
                    //$urlBoton = "errorrrrrr" . $exc->getMessage();
                }
            } else {
                $urlBoton = $boton;
            }
            if ($textBoton == $butName) {
                if (Lang::has("crudgenerator::datatables.buttons." . $butName)) {
                    $textBoton = trans("crudgenerator::datatables.buttons." . $butName);
                    $titleBoton = $plurales . " - " . trans("crudgenerator::datatables.buttons.t_" . $butName);
                }
            }
            $extend = true;
            $typeAjax = "get";
            $method = "get";
            $data = "{'__parametros':'" . $config['parametros'] . "'}";
            $returnStr = "simple";
            switch ($butName) {
                case 'create':
                    $extend = false;
                    break;
                case 'remove':
                    $typeAjax = "post";
                    $data = "{'_method':'delete','_token':'" . csrf_token() . "','__parametros':'" . $config['parametros'] . "'}";
                    $method = "delete";
                    $returnStr = "pureJson";
                    break;
            }
            ?>
                        {
                        @if ($extend)
                                extend:'selected',
                                @endif
                                text:'{!! $textBoton !!}',
                                titleAttr:'{!! $titleBoton !!}',
                                className:'{!! $buttonClass !!}',
                                action: function(){
                                var url = '{!! $urlBoton !!}';
                                var datos = lista_{{ $tabla }}.rows({selected:true}).ids().toArray();
                                if (datos.length == 0){
                                var idSelected = 0;
                                var nameSelected = "";
                                } else{
                                var idSelected = datos[0].substr(datos[0].indexOf('__') + 2, datos[0].indexOf('|') - (datos[0].indexOf('__') + 2));
                                var nameSelected = datos[0].substr(datos[0].indexOf('|') + 1, datos[0].length - (datos[0].indexOf('|') + 1));
                                }
                                url = url.replace(":modelId", idSelected).replace(":modelName", nameSelected);
                                @if ($butName === 'remove')
                                        console.log('{{$butName}}', '{{($butName == 'remove')}}');
                                confirmTitle = '{{$confirmTitle}}';
                                confirmTitle = confirmTitle.replace(":modelId", idSelected).replace(":modelName", nameSelected);
                                confirmContent = '{!!$confirmContent!!}';
                                confirmContent = confirmContent.replace(":modelId", idSelected).replace(":modelName", nameSelected);
                                $.confirm({
                                theme: '{{$confirmTheme}}',
                                        icon: '{!!$confirmIcon!!}',
                                        title: confirmTitle,
                                        content: confirmContent,
                                        buttons: {
                                        ['{{$confirmYes}}']: function () {
                                        @endif
                                                @if ($modales)
                                                $.ajax({
                                                type: '{{$typeAjax}}',
                                                        dataType: 'json',
                                                        url:url + '?_return={{$returnStr}}',
                                                        data:{!! $data !!},
                                                        success:function(data){
                                                        if (data.status == 200){
                                                        @if ($butName == 'remove')
                                                                lista_{{ $tabla }}.rows({selected:true}, 0).every(function (rowIdx, tableLoop, rowLoop) {
                                                        if (rowLoop == 0){
                                                        lista_{{ $tabla }}.rows(rowIdx).remove().draw();
                                                        }
                                                        console.log("loop", rowIdx, tableLoop, rowLoop);
                                                        }); //.remove().draw();
                                                        @endif
                                                                if ($.type(data.result) == "object"){
                                                        $.alert({
                                                        theme: '{!!config("sirgrimorum.crudgenerator.success_theme")!!}',
                                                                icon: '{!!config("sirgrimorum.crudgenerator.success_icon")!!}',
                                                                title: data.title + ' - ' + data.statusText,
                                                                content: data.result.{{config("sirgrimorum.crudgenerator.status_messages_key")}},
                                                        });
                                                        } else{
                                                        $('#modal_{{ $tablaid }}_Label').html(data.title);
                                                        $('#modal_{{ $tablaid }}_body').html(data.result);
                                                        $('#modal_{{ $tablaid }}_footer').hide();
                                                        $('#modal_{{ $tablaid }}').modal('toggle');
                                                        }
                                                        } else{
                                                        $.alert({
                                                        theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                                                icon: '{!!config("sirgrimorum.crudgenerator.error_icon")!!}',
                                                                title: data.title,
                                                                content: data.statusText,
                                                        });
                                                        console.log(data);
                                                        }
                                                        },
                                                        error:function(jqXHR, textStatus, errorThrown){
                                                        var content = errorThrown;
                                                        var title = textStatus;
                                                        if (jqXHR.responseJSON){
                                                        if (jqXHR.responseJSON.statusText){
                                                        content = jqXHR.responseJSON.statusText;
                                                        }
                                                        if (jqXHR.responseJSON.title){
                                                        title = jqXHR.responseJSON.title;
                                                        }
                                                        }
                                                        $.alert({
                                                        theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                                                icon: '{!!config("sirgrimorum.crudgenerator.error_icon")!!}',
                                                                title: title,
                                                                content: content,
                                                        });
                                                        console.log(jqXHR);
                                                        }
                                                });
                                        @else
                                                form_string = "<form method=\"{{strtoupper($typeAjax)}}\" action=\"" + url + "\" accept-charset=\"UTF-8\">"
                                                var datos = {!! $data !!};
                                        $.each({!! $data !!}, function(key, value){
                                        form_string = form_string + "<input name=\"" + key + "\" type=\"hidden\" value=\"" + value + "\">";
                                        });
                                        form_string = form_string + "</form>";
                                        form = $(form_string)
                                                form.appendTo('body');
                                        form.submit();
                                        @endif
                                                @if ($butName === 'remove')

                                        },
                                        ['{{$confirmNo}}']: function () {

                                        },
                                        }
                                });
                                @endif
                                }
                        },
            <?php
        }
    }
}
?>
            ],
            language: {!! json_encode(trans("crudgenerator::datatables")) !!},
            keys: false,
            autoFill: false,
            @if (isset($config['orden']))
            order : {{ json_encode($config['orden']) }},
            @endif
    });
    //new $.fn.dataTable.FixedHeader(lista_{{ $tabla }});
    });
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>
