@if (is_array($botones))
<script id="{{ $tablaid }}_datatables_buttons_scripts_block">
<?php
foreach (\Illuminate\Support\Arr::only($botones, ['create', 'show', 'edit', 'remove']) as $butName => $boton) {
if (is_string($butName)) {
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
        } catch (Exception $exc) {
            $urlBoton = $boton;
            //$urlBoton = "errorrrrrr" . $exc->getMessage();
        }
    } else {
        $urlBoton = $boton;
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
function {{ $tablaid }}{{ $butName }}Pressed (
    @if ($butName != 'create')
    idSelected, nameSelected
    @if ($butName == 'remove')
    , rowSelected
    @endif
    @endif
    ) {
    var url = '{!! $urlBoton !!}';
    @if ($butName != 'create')
    url = url.replace(":modelId", idSelected).replace(":modelName", nameSelected);
    @endif
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
                @if ($modales || ($butName == "remove" && \Illuminate\Support\Arr::get($config,"multiRemove", true)))
                $.ajax({
                    type: '{{$typeAjax}}',
                    dataType: 'json',
                    url:url + '?_return={{$returnStr}}',
                    data:{!! $data !!},
                    success:function(data){
                        if (data.status == 200){
                        @if ($butName == 'remove')
                        lista_{{ $tabla }}.rows('#'+rowSelected).remove().draw(); 
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
                form = $(form_string);
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
<?php
}
}
?>
</script>
@foreach(data_get(\Illuminate\Support\Arr::except($botones, ['create', 'show', 'edit', 'remove']),'*.script') as $urlScript)
@if ($urlScript !== null && is_string($urlScript))
<script>scriptLoader('{{ $urlScript }}',true,"");</script>
@endif
@endforeach
@loadScript('',true,"{$tablaid}_datatables_buttons_scripts_block")
@endif