buttons: [
    @if ($usarAjax && !$tienePrefiltro)
    {
        text:'{!! trans("crudgenerator::datatables.buttons.cargar") !!}',
        titleAttr:'{!! trans("crudgenerator::datatables.buttons.t_cargar") !!}',
        action: function(){
            {{ $tablaid }}ReloadData();
        },
        className:'{{ trans("crudgenerator::datatables.buttons.c_cargar") }}',
    },
    @endif
    @if (Illuminate\Support\Arr::get($config,'conditions', true))
    'searchBuilder',
    @endif
    @if (Illuminate\Support\Arr::get($config,'filters', false))
    'searchPanes',
    @endif
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
if (is_string($butName)) {
    $buttonClass = "";
    $textBoton = $butName;
    $titleBoton = $butName;
    $callback = "";
    $contentCallbak = ";";
    $extend = true;
    if (is_string($boton)){
        if (stripos($boton, "<a") >= 0) {
            try {
                $nodes = CrudGenerator::extract_tags($boton, "a");
                $textBoton = $nodes[0]['contents'];
                if (!isset($nodes[0]['attributes']['title'])) {
                    $titleBoton = $plurales . " - " . $textBoton;
                } else {
                    $titleBoton = $nodes[0]['attributes']['title'];
                }
                if (isset($nodes[0]['attributes']['class'])) {
                    $buttonClass = $nodes[0]['attributes']['class'];
                }
            } catch (Exception $exc) {
            }
        }
        if ($textBoton == $butName) {
            if (Lang::has("crudgenerator::datatables.buttons." . $butName)) {
                $textBoton = trans("crudgenerator::datatables.buttons." . $butName);
                $titleBoton = $plurales . " - " . trans("crudgenerator::datatables.buttons.t_" . $butName);
            }
        }
    }elseif(is_array($boton)){
        $buttonClass = \Illuminate\Support\Arr::get($boton,"class", $buttonClass);
        $textBoton = \Illuminate\Support\Arr::get($boton,"text", $buttonClass);
        $titleBoton = \Illuminate\Support\Arr::get($boton,"title", $buttonClass);
        $callback = \Illuminate\Support\Str::before(\Illuminate\Support\Arr::get($boton,"callback", $buttonClass), '(');
        if ($callback == "function"){
            $contentCallback = \Illuminate\Support\Str::beforeLast(\Illuminate\Support\Str::after(\Illuminate\Support\Arr::get($boton,"callback", $buttonClass), '{'), '}');
        }
        $extend = \Illuminate\Support\Arr::get($boton,"extendSelected", $extend);
    }
    switch ($butName) {
        case 'create':
            $extend = false;
            break;
        case 'remove':
            break;
    }
    ?>
    {
        @if ($extend)
        extend:'selected',
        @endif
        text:'{!! str_replace(["'"],['"'],$textBoton) !!}',
        titleAttr:'{!! $titleBoton !!}',
        className:'{!! $buttonClass !!}',
        action: function(){
            @if($butName != "create")
            var rowsSelected = lista_{{ $tabla }}.rows({selected:true});
            var datos = rowsSelected.ids().toArray();
            countSelected = rowsSelected.count();
            idsSelected = [];
            namesSelected = [];
            for (let index = 0; index < countSelected; index++) {
                if (datos[index].indexOf('__') > 0 && datos[index].indexOf('|') > 0 ){
                    idsSelected.push(datos[index].substr(datos[index].indexOf('__') + 2, datos[index].indexOf('|') - (datos[index].indexOf('__') + 2)));
                    namesSelected.push(datos[index].substr(datos[index].indexOf('|') + 1, datos[index].length - (datos[index].indexOf('|') + 1)));
                }else{
                    idsSelected.push(datos[index]);
                    namesSelected.push("");
                }
                @if ($butName == "remove" && \Illuminate\Support\Arr::get($config,"multiRemove", true))
                {{ $tablaid }}{{ $butName }}Pressed (idsSelected[index], namesSelected[index], datos[index]);
                @endif
            }
            console.log("seleccionados", idsSelected, namesSelected, rowsSelected.data().toArray());
            @if($butName == "edit" || $butName == "show" || ($butName == "remove" && !\Illuminate\Support\Arr::get($config,"multiRemove", true)))
            var idSelected = 0;
            var nameSelected = "";
            var rowSelected = "";
            if (countSelected > 0){
                idSelected = idsSelected[0];
                nameSelected = namesSelected[0];
                rowSelected = datos[0];
            }
            {{ $tablaid }}{{ $butName }}Pressed (idSelected, nameSelected, rowSelected);
            @elseif($butName != "remove" && $callback != "")
                @if ($callback == "function")
                {!! $contentCallback !!}
                @else
                {{ $callback }}(idsSelected, namesSelected, rowsSelected);
                @endif
            @endif
            @else
            {{-- Es crear --}}
            {{ $tablaid }}{{ $butName }}Pressed ();
            @endif
        }
    },
    <?php
}
}
}
?>
],