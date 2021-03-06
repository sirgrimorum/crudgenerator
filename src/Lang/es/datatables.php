<?php

return [
    "sProcessing" => "Procesando...",
    "sLengthMenu" => "Mostrar _MENU_ registros",
    "sZeroRecords" => "No se encontraron resultados",
    "sEmptyTable" => "Ningún dato disponible en esta tabla",
    "sInfo" => "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "sInfoEmpty" => "Mostrando registros del 0 al 0 de un total de 0 registros",
    "sInfoFiltered" => "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix" => "",
    "sSearch" => "Buscar:",
    "sSearchPlaceholder" => "Comience a teclear...",
    "sUrl" => "",
    "sInfoThousands" => ",",
    "sLoadingRecords" => "Cargando...",
    "sProcessing" => "Procesando...",
    "sloadingRecords" => '&nbsp;',
    "oPaginate" => [
        "sFirst" => "Primero",
        "sLast" => "Último",
        "sNext" => "Siguiente",
        "sPrevious" => "Anterior"
    ],
    "oAria" => [
        "sSortAscending" => ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending" => ": Activar para ordenar la columna de manera descendente"
    ],
    "searchBuilder" => [
        "add"=> '+',
        "condition"=> 'Condición',
        "clearAll"=> 'Eliminar todas',
        "deleteTitle"=> 'Eliminar',
        "data"=> 'Columna',
        "leftTitle"=> 'Desagrupar',
        "logicAnd"=> 'And',
        "logicOr"=> 'Or',
        "rightTitle"=> 'Agrupar',
        "button"=> [
            "0"=> '<i class="fa fa-filter fa-lg"></i>',
            "_"=> '<i class="fa fa-filter fa-lg"></i> (%d)'
        ],
        "title"=> [
            "0"=> 'Condiciones',
            "_"=> 'Condiciones (%d)'
        ],
        "value"=> 'Opción',
        "conditions" => [
            "date" => [
                "after" => 'Después de',
                "before" => 'Antes de',
                "between" => 'Entre',
                "empty" => 'Vacío',
                "equals" => 'Igual a',
                "not" => 'Diferente a',
                "notBetween" => 'No entre',
                "notEmpty" => 'Lleno',
            ],
            "moment" => [
                "after" => 'Después de',
                "before" => 'Antes de',
                "between" => 'Entre',
                "empty" => 'Vacío',
                "equals" => 'Igual a',
                "not" => 'Diferente a',
                "notBetween" => 'No entre',
                "notEmpty" => 'Lleno',
            ],
            "number" => [
                "between" => 'Entre',
                "empty" => 'Vacío',
                "equals" => 'Igual a',
                "gt" => 'Mayor que',
                "gte" => 'Mayor o igual a',
                "lt" => 'Menor que',
                "lte" => 'Menor o igual a',
                "not" => 'Diferente a',
                "notBetween" => 'No entre',
                "notEmpty" => 'Lleno',
            ],
            "string" => [
                "contains" => 'Contiene',
                "empty" => 'Vacío',
                "endsWith" => 'Termina con',
                "equals" => 'Igual a',
                "not" => 'Diferente a',
                "notEmpty" => 'Lleno',
                "startsWith" => 'Empieza con',
            ],
            "array" => [
                "contains" => 'Contiene',
                "empty" => 'Vacío',
                "equals" => 'Igual a',
                "not" => 'Diferente a',
                "notEmpty" => 'Lleno',
                "without" => 'No incluye',
            ]
        ]
    ],
    "searchPanes" => [
        "clearMessage" => "Clear All",
        "emptyPanes" => null, // No mostrar el panel
        "loadMessage" => "Loading filtering options...",
        "count" => "{total}",
        "countFiltered" => "{shown} / {total}",
        "collapse" => [
            "0" => '<i class="fa fa-search fa-lg"></i>',
            "_" => '<i class="fa fa-search fa-lg"></i> (%d)',
        ],
        "title"=> [
            "0"=> 'Sin Filtros',
            "_"=> 'Filtros Activos - %d'
        ],
    ],
    "buttons"=>[
        'cargar' => '<i class="fa fa-refresh fa-lg" aria-hidden="true"></i> Cargar datos', // <i class="fa fa-refresh fa-lg" aria-hidden="true"></i> for font-awsemo
        't_cargar' => 'Cargar los datos de la tabla', //title Attribute
        'c_cargar' => 'btn btn-dark', // class for the Cargar button
        'copy' => '<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i> Copiar', //<i class="fa fa-clipboard" aria-hidden="true"></i> for font-awsome
        't_copy' => 'Copiar', //title Attribute
        'excel' => '<i class="fa fa-file-excel-o fa-lg" aria-hidden="true"></i> Excel', //<i class="fa fa-file-excel-o" aria-hidden="true"></i> for font-awsome
        't_excel' => 'Excel', //title Attribute
        'colvis' => '<i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i>', //<i class="fa fa-eye-slash" aria-hidden="true"></i> for font-awsome
        't_colvis' => 'Mostrar columnas', //title Attribute
        'pdf' => '<i class="fa fa-file-pdf-o fa-lg" aria-hidden="true"></i> Pdf', //<i class="fa fa-file-pdf-o" aria-hidden="true"></i> for font-awsome
        't_pdf' => 'Pdf', //title Attribute
        'print' => '<i class="fa fa-print fa-lg" aria-hidden="true"></i> Imprimir', //<i class="fa fa-print" aria-hidden="true"></i> for font-awsome
        't_print' => 'Imprimir', //title Attribute
        'selectAll' => '<i class="fa fa-check-square-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-check-square-o" aria-hidden="true"></i> for font-awsome
        't_selectAll' => 'Seleccionar todo', //title Attribute
        'selectNone' => '<i class="fa fa-square-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-square-o" aria-hidden="true"></i> for font-awsome
        't_selectNone' => 'Deseleccionar todo', //title Attribute
        'export' => '<i class="fa fa-external-link fa-lg" aria-hidden="true"></i>', //<i class="fa fa-external-link" aria-hidden="true"></i> for font-awsome
        't_export' => 'Exportar', //title Attribute
        'create' => '<i class="fa fa-plus fa-lg" aria-hidden="true"></i>', //<i class="fa fa-plus" aria-hidden="true"></i> for font-awsome
        't_create' => 'Crear', //title Attribute
        'edition' => '<i class="fa fa-pencil fa-lg" aria-hidden="true"></i>', //<i class="fa fa-pencil" aria-hidden="true"></i> for font-awsome
        't_edition' => 'Edición', //title Attribute
        'edit' => '<i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-pencil-square-o-o" aria-hidden="true"></i> for font-awsome
        't_edit' => 'Editar', //title Attribute
        'show' => '<i class="fa fa-info fa-lg" aria-hidden="true"></i>', //<i class="fa fa-info" aria-hidden="true"></i> for font-awsome
        't_show' => 'Ver', //title Attribute
        'remove' => '<i class="fa fa-trash-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-trash-o" aria-hidden="true"></i> for font-awsome
        't_remove' => 'Eliminar', //title Attribute
    ],
    "select"=>[
        "rows"=>[
            "_"=>"Ha seleccionado %d registros",
            "0"=>"Haga clic en una fila para seleccionar el registro",
            "1"=>"Ha seleccionado 1 registro",
        ]
    ]
];
