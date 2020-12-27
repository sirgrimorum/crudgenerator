buttons: [
    @if ($usarAjax && !$tienePrefiltro)
    {
        text:'{!! trans("crudgenerator::datatables.buttons.cargar") !!}',
        titleAttr:'{!! trans("crudgenerator::datatables.buttons.t_cargar") !!}',
        action: function(){
            {{ $tablaid }}ReloadData();
        },
        className:{{ trans("crudgenerator::datatables.buttons.c_cargar") }},
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
                            if (datos[0].indexOf('__') > 0 && datos[0].indexOf('|') > 0 ){
                                var idSelected = datos[0].substr(datos[0].indexOf('__') + 2, datos[0].indexOf('|') - (datos[0].indexOf('__') + 2));
                                var nameSelected = datos[0].substr(datos[0].indexOf('|') + 1, datos[0].length - (datos[0].indexOf('|') + 1));
                            }else{
                                var idSelected = datos[0];
                                var nameSelected = "";
                            }
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
                                                        icon: '{!!config("sirgrimorum.crudgenerator.icons.success")!!}',
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
                                                        icon: '{!!config("sirgrimorum.crudgenerator.icons.error")!!}',
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
                                                        icon: '{!!config("sirgrimorum.crudgenerator.icons.error")!!}',
                                                        title: title,
                                                        content: content,
                                                });
                                                console.log(jqXHR);
                                                }
                                        });
                                @else
                                        form_string = "<form method=\"{{strtoupper($typeAjax)}}\" action=\"" + url + "\" accept-charset=\"UTF-8\">"
                                        var datos = {!! $data !!};
                                $.each(datos, function(key, value){
                                form_string = form_string + "<input name='" + key + "' type='hidden' value='" + value + "'>";
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