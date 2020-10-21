@if (Session::has(config("sirgrimorum.crudgenerator.status_messages_key")))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    {!! Session::pull(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
</div>
@endif
@if (Session::has(config("sirgrimorum.crudgenerator.error_messages_key")))
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    {!! Session::pull(config("sirgrimorum.crudgenerator.error_messages_key")) !!}
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
$tablaid = $tabla . "_" . \Illuminate\Support\Str::random(5);
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
$siOld = false;
if (old("__parametros","") != ""){
    $parametrosOld = json_decode(old("__parametros"), true);
    if (strtolower(class_basename($parametrosOld["modelo"])) == $modelo){
        $siOld = true;
    }
}
?>
@if ($usarAjax && $tienePrefiltro)
<div class="card border-dark mb-3">
    <div class="card-header">
        {{ trans("crudgenerator::admin.index.prefiltros") }}
    </div>
    <div class="card-body">
        <?php
        $configPrefiltro = CrudGenerator::justWithValor($config,'datatables','prefiltro');
        $action = "create";
        //echo "<p>Datos Todos</p><pre>" . print_r([CrudGenerator::hasTipo($configPrefiltro, ['date', 'datetime', 'time']), $configPrefiltro], true) . "</pre>";
        ?>
        @include("sirgrimorum::crudgen.includes", [
            'config' => $configPrefiltro,
            'tieneHtml' => CrudGenerator::hasTipo($configPrefiltro, ['html', 'article']),
            'tieneDate' => CrudGenerator::hasTipo($configPrefiltro, ['date', 'datetime', 'time']),
            'tieneSlider' => CrudGenerator::hasTipo($configPrefiltro, 'slider'),
            'tieneSelect' => CrudGenerator::hasTipo($configPrefiltro, ['select', 'relationship', 'relationships']),
            'tieneSearch' => CrudGenerator::hasTipo($configPrefiltro, ['relationshipssel']),
            'tieneColor' => CrudGenerator::hasTipo($config, ['color']),
            'tieneFile' => CrudGenerator::hasTipo($configPrefiltro, ['file', 'files']),
            'tieneJson' => CrudGenerator::hasTipo($configPrefiltro, ['json']),
            'js_section' => $js_section,
            'css_section' => $css_section,
            'modelo' => $modelo
        ])
        <?php
        foreach ($configPrefiltro['campos'] as $columna => $configCampo) {
            $config['extraId'] = "{$tablaid}_prefiltro_$columna";
            $config = CrudGenerator::loadDefaultClasses($config);
            $errores = false;
            $configCampo = CrudGenerator::loadTodosForField($configCampo, $columna, $configPrefiltro);
            if ($configCampo['tipo'] == 'relationship'){
                $configCampo['tipo'] = 'relationships';
            }elseif($configCampo['tipo'] == 'select'){
                $configCampo['multiple'] = 'multiple';
            }
            if (View::exists("sirgrimorum::crudgen.templates." . $configCampo['tipo'])) {
                ?>
                @include("sirgrimorum::crudgen.templates." . $configCampo['tipo'], ['datos'=>$configCampo,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo, 'action'=>$action])
                <?php
            } else {
                ?>
                @include("sirgrimorum::crudgen.templates.text", ['datos'=>$configCampo,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo])
                <?php
            }
        }
        ?>
        <div class="text-right">
            <button class="{{ trans("crudgenerator::datatables.buttons.c_cargar") }}" onclick="{{ $tablaid }}ReloadData();" title="{!! trans("crudgenerator::datatables.buttons.t_cargar") !!}">{!! trans("crudgenerator::datatables.buttons.cargar") !!}</button>
        </div>
    </div>
</div>
@endif
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
    @if (!$usarAjax)
    <tbody>
        @if (is_array($registros))
        @include("sirgrimorum::crudgen.list_inner",[
            "registros" => $registros,
            "config" => $config,
            "tablaid" => $tablaid,
            "campos" => $campos,
        ])
        @else
        @include("sirgrimorum::crudgen.list_inner_completo",[
            "registros" => $registros,
            "config" => $config,
            "tablaid" => $tablaid,
            "campos" => $campos,
        ])
        @endif
    </tbody>
    @endif
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
@if((old("_action")=="create" || old("_action")=="edit") && $siOld)
<!-- Modal for old input -->
@include('sirgrimorum::admin.' . old("_action") . ".modal", ["modelo" => $modeloUCF,"base_url" => $base_url, "plural" => $plural,"config" => $config,"registro"=>old("_registro")])
@endif
@if (config("sirgrimorum.crudgenerator.modal_section") != "")
@endpush
@endif
@endif
<?php if ($css_section != "") { ?>
    @push($css_section)

    <style>
        .btn-group > .btn:not(:last-child).dropdown-toggle{
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
    </style>
    <?php
}
if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    echo '<link href="' . config("sirgrimorum.crudgenerator.jquerytables_path") . '" rel="stylesheet" type="text/css">';
} else {
    echo '<link href="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datatables.min.css") . '" rel="stylesheet" type="text/css">';
}
if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
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

$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader");
if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.jquerytables_path"), ['http', '://'])) {
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js',false);
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.jquerytables_path") ,false);
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml('https://cdn.datatables.net/plug-ins/1.10.21/sorting/datetime-moment.js',false);
} else {
    //echo '<script src="' . asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datatables.min.js") . '"></script>';
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/moment.min.js"),false);
    ?>
    <script>
        var {{ $tablaid }}MomentEjecutado = false;
        function {{ $tablaid }}MomentLoader(){
            if (!{{ $tablaid }}MomentEjecutado){
                {{ $nameScriptLoader }}('{{ asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datatables.min.js") }}',false,"");
            }
            {{ $tablaid }}Ejecutado = true;
        }
        window.addEventListener('load', function() {
            {{ $tablaid }}MomentLoader();
        });
        {{ $nameScriptLoader. "Creator" }}('moment_min_js',"{{ $tablaid }}MomentLoader();");
        
        var {{ $tablaid }}DataTablesEjecutado = false;
        function {{ $tablaid }}DataTablesLoader(){
            if (!{{ $tablaid }}DataTablesEjecutado){
                {{ $nameScriptLoader }}('{{ asset(config("sirgrimorum.crudgenerator.jquerytables_path") . "/datetime-moment.js") }}',false,"");
            }
            {{ $tablaid }}Ejecutado = true;
        }
        window.addEventListener('load', function() {
            {{ $tablaid }}DataTablesLoader();
        });
        {{ $nameScriptLoader. "Creator" }}('datatables_min_js',"{{ $tablaid }}DataTablesLoader();");
    </script>
    <?php
}
if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
    //echo '<script src="' . config("sirgrimorum.crudgenerator.confirm_path") . '"></script>';
    //echo '<script src="' . asset("vendor/sirgrimorum/confirm/js/rails.js") . '"></script>';
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml( config("sirgrimorum.crudgenerator.confirm_path"),false);
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset("vendor/sirgrimorum/confirm/js/rails.js") ,false);
} else {
    //echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/jquery-confirm.min.js") . '"></script>';
    //echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/rails.js") . '"></script>';
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/jquery-confirm.min.js"),false);
    echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/rails.js"),false);
}
?>
<script id="{{ $tablaid }}_datatables_block">
    var {{ $tablaid }}DataTablesCargado = false;
    
    @if($usarAjax)
    var {{ $tablaid }}Initialized = false;
    function {{ $tablaid }}DataSourceFunction (d) {
        return new Promise(function(resolve, reject) {
            if (!{{ $tablaid }}Initialized) {
                resolve({
                    data: {},
                });   
            }else{
                d._token = $('meta[name="csrf-token"]').attr('content');
                d._return = "datatablesjson";
                @if ($usarAjax && $tienePrefiltro)
                d._or = false;
                @foreach ($configPrefiltro['campos'] as $columna => $configCampo)
                d.{{ $columna }} = $('#{{ "{$tabla}_{$tablaid}_prefiltro_$columna" }}').val();
                @endforeach
                @endif
                console.log('Loading data',d);
                $.ajax({
                    url: "{{ route('sirgrimorum_modelos::index',['modelo'=> $modelo ]) }}",
                    dataType: "json",
                    type: "POST",
                    data: d,
                    success: function(json) {
                        console.log("devuelve", json);
                        resolve({
                            data: json.data,
                        });
                    },
                });
            }
        });
    }
    function {{ $tablaid }}ReloadData(){
        console.log("recargando");
        lista_{{ $tabla }}.ajax.reload();
    }
    @endif

    var lista_{{ $tabla }};
    window.addEventListener('load', function() {
        if (!{{ $tablaid }}DataTablesCargado){
            @if ((old("_action")=="create" || old("_action")=="edit") && $siOld)
                    $("#{{$modeloUCF}}_{{old('_action')}}_modal").modal("show");
            @endif
            <?php
            $formatosTiempo = [];
            CrudGenerator::forValor($config,'tipo',['date', 'time', 'datetime'], function($campo, $configCampo) use (&$formatosTiempo){
                if (Illuminate\Support\Arr::has($configCampo,'format.moment')){
                    if (!in_array(Illuminate\Support\Arr::get($configCampo,'format.moment'),$formatosTiempo)){
                        echo "$.fn.dataTable.moment('" . Illuminate\Support\Arr::get($configCampo,'format.moment') . "');";
                        $formatosTiempo[] = Illuminate\Support\Arr::get($configCampo,'format.moment');
                    }
                }
            });
            ?>
            lista_{{ $tabla }} = $('#list_{{ $tablaid }}').DataTable({
                processing: true,
                @if($serverSide)
                serverSide: true,
                @else
                serverSide: false,
                @endif
                searchPanes:{
                    viewTotal: true,
                    cascadePanes: true,
                },
                @if($usarAjax)
                ajax: function (data, callback, settings) {
                    {{ $tablaid }}DataSourceFunction(data).then(function (_data) {
                        callback(_data);
                    });
                },
                initComplete: function () {
                    {{ $tablaid }}Initialized = true;
                },
                @endif
                columns: [
                    @foreach($campos as $columna => $datos)
                    @if (CrudGenerator::inside_array($datos,"hide","list")===false)
                    { data : "{{ $columna }}" },
                    @endif
                    @endforeach
                ],
                rowId: '{{ $config['id'] }}',
                responsive: false,
                stateSave: true,
                dom: 'Bfrtip',
                select: true,
                colReorder: true,
                fixedHeader: false,
                scrollY: "60vh",
                sScrollX: "100%",
                scrollCollapse: true,
                deferRender: true,
                scroller: {
                    loadingIndicator: true,
                },
                orderCellsTop: true,
                paging: true,
                @if (isset($config['orden']))
                order : {{ json_encode($config['orden']) }},
                @endif
                @include("sirgrimorum::crudgen.list_botones",[
                    "botones" => $botones,
                    "config" => $config,
                    "tablaid" => $tablaid,
                    "tabla" => $tabla,
                    "tienePrefiltro" => $tienePrefiltro,
                    'usarAjax' => $usarAjax,
                    'serverSide' => $serverSide,
                ])
                language: {!! json_encode(trans("crudgenerator::datatables")) !!},
                keys: false,
                autoFill: false,
            });
            //new $.fn.dataTable.FixedHeader(lista_{{ $tabla }});
        }
        {{ $tablaid }}DataTablesCargado = true;
    });
</script>
@loadScript('',true,"{$tablaid}_datatables_block")
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>
