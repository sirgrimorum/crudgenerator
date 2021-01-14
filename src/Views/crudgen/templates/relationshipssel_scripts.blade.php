<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
?>
<script>
    var {{ $tabla . "_" . $extraId }}Ejecutado = false;
    function {{ $tabla . "_" . $extraId }}Loader(){
        if (!{{ $tabla . "_" . $extraId }}Ejecutado){
            $.typeahead({
                input: '#{{ $tabla . '_' . $extraId }}_search',
                minLength: {{ \Illuminate\Support\Arr::get($datos, 'minLength', 1) }},
                maxItem: {{ \Illuminate\Support\Arr::get($datos, 'maxItem', 15) }},
                order: "asc",
                accent: true,
                searchOnFocus: true,
                cancelButton: false,
                //cache: true,
                <?php
                $backdrop = \Illuminate\Support\Arr::get($datos, 'backdrop', null);
                if (is_array($backdrop)){
                    $backdrop = json_encode($backdrop);
                }elseif($backdrop === true){
                    $backdrop = "true";
                }elseif($backdrop === false){
                    $backdrop = "false";
                }elseif($backdrop === null){
                    $backdrop = "true";
                }
                $datoCampo = CrudGenerator::getCamposDeReplacementString($datos['campo']);
                $auxTexto = "";
                $prefijoAuxTexto = "[";
                foreach($datoCampo as $auxTextocampo){
                    $auxTexto .= "{$prefijoAuxTexto}'{$auxTextocampo}.value'";
                    $prefijoAuxTexto = ", ";
                }
                if ($auxTexto!=""){
                    $auxTexto .= "]";
                }
                ?>
                display: {!!$auxTexto!!},
                hint: true,
                @if(isset($datos['groupby']))
                group: {
                    template: "@{{group}}"
                },
                dropdownFilter: "{{ $langGroup }}",
                //href: "/beers/@{{group|slugify}}/@{{display|slugify}}/",
                @else
                //href: "/beers/@{{group|slugify}}/@{{display|slugify}}/",
                @endif
                maxItemPerGroup: {{ \Illuminate\Support\Arr::get($datos, 'maxItemPerGroup', 4) }},
                backdrop: {!! $backdrop !!},
                emptyTemplate: '{{ trans("crudgenerator::admin.messages.no_result_query") }}',
                source: {
                    @if (isset($datos['groupby']))
                    @if($listaOpciones)
                    @foreach($listaOpciones as $indice=>$opcionGroup)
                    "{{CrudGenerator::getNombreDeLista('',$listaTransOpciones[$indice])}}": {
                        ajax: {
                            url: "{!! route('sirgrimorum_modelos::index',['modelo'=>$modeloOtro]) !!}?_return=pureJson&_q={{CrudGenerator::getNombreDeLista('',$opcionGroup,'|')}}&_a={{CrudGenerator::getNombreDeLista('',$datos['groupby'],'|')}}&_aByA=1&_or=false",
                            path: "result"
                        }
                    },               
                        @endforeach
                    @else
                        ajax: {
                            //url: "{!! route('sirgrimorum_modelos::index',['modelo'=>$modeloOtro]) !!}?_return=pureJson&_q=@{{query}}*%&_a={{$camposQuery}}",
                            url: "{!! route('sirgrimorum_modelos::index',['modelo'=>$modeloOtro]) !!}?_return=pureJson",
                            path: "result"
                        }
                    @endif
                    @else
                        ajax: {
                            //url: "{!! route('sirgrimorum_modelos::index',['modelo'=>$modeloOtro]) !!}?_return=pureJson&_q=@{{query}}*%&_a={{$camposQuery}}",
                            url: "{!! route('sirgrimorum_modelos::index',['modelo'=>$modeloOtro]) !!}?_return=pureJson",
                            path: "result"
                        }
                    @endif
                },
                callback: {
                    onClickAfter: function (node, a, item) {
                        $.ajax({
                            type: 'get',
                            dataType: 'json',
                            url:'{!! route('sirgrimorum_modelos::create',['modelo'=>$modeloMio]) !!}?_return=simple&_itemRelSel={!!$columna!!}|' + item.id,
                            data:'',
                            success:function(data){
                                if (data.status == 200){
                                    if ($("#{{$extraId . "_"}}" + item.id +"_principal").length == 0){
                                        $("#{{ $tabla . '_' . $extraId }}_container").find('div[data-pivote="principal"]').last().after(data.result);
                                    }else{
                                        $.alert({
                                            theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                            icon: '{!!config("sirgrimorum.crudgenerator.icons.error")!!}',
                                            title: '{!!trans('crudgenerator::admin.messages.pivot_exists_title')!!}',
                                            content: '{!!trans('crudgenerator::admin.messages.pivot_exists_message')!!}',
                                        });
                                    }
                                } else{
                                    $.alert({
                                        theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                        icon: '{!!config("sirgrimorum.crudgenerator.icons.error")!!}',
                                        title: data.title,
                                        content: data.statusText,
                                    });
                                    console.log('error simple ajax', data);
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
                                console.log('error grave ajax',qXHR);
                            }
                        });
                        console.log(item);
                    }
                },
                debug: true
            });
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    
    function quitarPivote(idSelected,nameSelected){
        console.log('quitar',idSelected,nameSelected);
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
                        $("#"+idSelected).remove();
                    },
                    ['{{$confirmNo}}']: function () {

                    },
                }
            });
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('jquery_typeahead_min_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>