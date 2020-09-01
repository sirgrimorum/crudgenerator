<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
?>
<script id="{{ $tabla . '_' . $columna }}_typeahead_block" type="text/html">
    function jquery_typeahead_min_js(){
    
    $.typeahead({
        input: '#{{ $tabla . '_' . $columna }}_search',
        minLength: 1,
        maxItem: 15,
        order: "asc",
        accent: true,
        searchOnFocus: true,
        //cache: true,
        @if(is_array($datos['campo']))
            <?php
            $auxTexto = "";
            $prefijoAuxTexto = "[";
            foreach($datos['campo'] as $auxTextocampo){
                $auxTexto .= $prefijoAuxTexto . "'" . $auxTextocampo . "'";
                $prefijoAuxTexto = ", ";
            }
            if ($auxTexto!=""){
                $auxTexto .= "]";
            }
            ?>
            display: {!!$auxTexto!!},
        @else
            display: ["{!!$datos['campo']!!}.value"],
        @endif
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
        maxItemPerGroup: 4,
        backdrop: {
            "background-color": "#fff"
        },
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
                        console.log('llega ajax',data);
                        if (data.status == 200){
                            if ($("#{{$columna . "_"}}" + item.id +"_principal").length == 0){
                                console.log('pegando a',  $("#{{ $tabla . '_' . $columna }}_container").find('div[data-pivote="principal"]').last());
                                $("#{{ $tabla . '_' . $columna }}_container").find('div[data-pivote="principal"]').last().after(data.result);
                            }else{
                                $.alert({
                                    theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                    icon: '{!!config("sirgrimorum.crudgenerator.error_icon")!!}',
                                    title: '{!!trans('crudgenerator::admin.messages.pivot_exists_title')!!}',
                                    content: '{!!trans('crudgenerator::admin.messages.pivot_exists_message')!!}',
                                });
                            }
                        } else{
                            $.alert({
                                theme: '{!!config("sirgrimorum.crudgenerator.error_theme")!!}',
                                icon: '{!!config("sirgrimorum.crudgenerator.error_icon")!!}',
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
                            icon: '{!!config("sirgrimorum.crudgenerator.error_icon")!!}',
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
    window.addEventListener('load', function() {
        jquery_typeahead_min_js();
    });
    
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
</script>
@loadScript('',true,"{$tabla}_{$columna}_typeahead_block")
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>