<?php

return [
    "sEmptyTable" => "No data available in table",
    "sInfo" => "Showing _START_ to _END_ of _TOTAL_ entries",
    "sInfoEmpty" => "Showing 0 to 0 of 0 entries",
    "sInfoFiltered" => "(filtered from _MAX_ total entries)",
    "sInfoPostFix" => "",
    "sInfoThousands" => ",",
    "sLengthMenu" => "Show _MENU_ entries",
    "sLoadingRecords" => "Loading...",
    "sProcessing" => "Processing...",
    "sloadingRecords" => '&nbsp;',
    "sSearch" => "Search:",
    "sSearchPlaceholder" => "Start typing...",
    "sZeroRecords" => "No matching records found",
    "oPaginate" => [
        "sFirst" => "First",
        "sLast" => "Last",
        "sNext" => "Next",
        "sPrevious" => "Previous"
    ],
    "oAria" => [
        "sSortAscending" => ": activate to sort column ascending",
        "sSortDescending" => ": activate to sort column descending"
    ],
    "searchBuilder" => [
        "add" => 'Add Condition',
        "condition" => 'Condition',
        "clearAll" => 'Clear All',
        "deleteTitle" => 'Delete filtering rule',
        "data" => 'Column',
        "leftTitle" => 'Outdent criteria',
        "logicAnd" => 'And',
        "logicOr" => 'Or',
        "rightTitle" => 'Indent criteria',
        "button" => [
            "0" => '<i class="fa fa-filter fa-lg"></i>',
            "_" => '<i class="fa fa-filter fa-lg"></i> (%d)'
        ],
        "title" => [
            "0" => 'Conditions',
            "_" => 'Conditions (%d)'
        ],
        "value" => 'Value',
        "conditions" => [
            "date" => [
                "after" => 'After',
                "before" => 'Before',
                "between" => 'Between',
                "empty" => 'Empty',
                "equals" => 'Equals',
                "not" => 'Not',
                "notBetween" => 'Not Between',
                "notEmpty" => 'Not Empty',
            ],
            "moment" => [
                "after" => 'After',
                "before" => 'Before',
                "between" => 'Between',
                "empty" => 'Empty',
                "equals" => 'Equals',
                "not" => 'Not',
                "notBetween" => 'Not Between',
                "notEmpty" => 'Not Empty',
            ],
            "number" => [
                "between" => 'Between',
                "empty" => 'Empty',
                "equals" => 'Equals',
                "gt" => 'Greater Than',
                "gte" => 'Greater Than Equal To',
                "lt" => 'Less Than',
                "lte" => 'Less Than Equal To',
                "not" => 'Not',
                "notBetween" => 'Not Between',
                "notEmpty" => 'Not Empty',
            ],
            "string" => [
                "contains" => 'Contains',
                "empty" => 'Empty',
                "endsWith" => 'Ends With',
                "equals" => 'Equals',
                "not" => 'Not',
                "notEmpty" => 'Not Empty',
                "startsWith" => 'Starts With',
            ],
            "array" => [
                "contains" => 'Contains',
                "empty" => 'Empty',
                "equals" => 'Equals',
                "not" => 'Not',
                "notEmpty" => 'Not Empty',
                "without" => 'Without',
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
        "title" => [
            "0" => 'No Filters',
            "_" => 'Filters Active - %d'
        ],
    ],
    "buttons" => [
        'cargar' => '<i class="fa fa-refresh fa-lg" aria-hidden="true"></i> Load data', // <i class="fa fa-refresh fa-lg" aria-hidden="true"></i> for font-awsemo
        't_cargar' => 'Load data to the table', //title Attribute
        'c_cargar' => 'btn btn-dark', // class for the Cargar button
        'copy' => '<i class="fa fa-clipboard fa-lg" aria-hidden="true"></i> Copy', //<i class="fa fa-clipboard" aria-hidden="true"></i> for font-awsome
        't_copy' => 'Copy', //title Attribute
        'excel' => '<i class="fa fa-file-excel-o fa-lg" aria-hidden="true"></i> Excel', //<i class="fa fa-file-excel-o" aria-hidden="true"></i> for font-awsome
        't_excel' => 'Excel', //title Attribute
        'colvis' => '<i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i>', //<i class="fa fa-eye-slash" aria-hidden="true"></i> for font-awsome
        't_colvis' => 'Column Visibility', //title Attribute
        'pdf' => '<i class="fa fa-file-pdf-o fa-lg" aria-hidden="true"></i> Pdf', //<i class="fa fa-file-pdf-o" aria-hidden="true"></i> for font-awsome
        't_pdf' => 'Pdf', //title Attribute
        'print' => '<i class="fa fa-print fa-lg" aria-hidden="true"></i> Print', //<i class="fa fa-print" aria-hidden="true"></i> for font-awsome
        't_print' => 'Print', //title Attribute
        'selectAll' => '<i class="fa fa-check-square-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-check-square-o" aria-hidden="true"></i> for font-awsome
        't_selectAll' => 'Select all', //title Attribute
        'selectNone' => '<i class="fa fa-square-o fa-lg" aria-hidden="true"></i> ', //<i class="fa fa-square-o" aria-hidden="true"></i> for font-awsome
        't_selectNone' => 'Deselect all', //title Attribute
        'export' => '<i class="fa fa-external-link fa-lg" aria-hidden="true"></i>', //<i class="fa fa-external-link" aria-hidden="true"></i> for font-awsome
        't_export' => 'Export', //title Attribute
        'create' => '<i class="fa fa-plus fa-lg" aria-hidden="true"></i>', //<i class="fa fa-plus" aria-hidden="true"></i> for font-awsome
        't_create' => 'Create', //title Attribute
        'edition' => '<i class="fa fa-pencil fa-lg" aria-hidden="true"></i>', //<i class="fa fa-pencil" aria-hidden="true"></i> for font-awsome
        't_edition' => 'Edition', //title Attribute
        'edit' => '<i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-pencil-square-o" aria-hidden="true"></i> for font-awsome
        't_edit' => 'Edit', //title Attribute
        'show' => '<i class="fa fa-info fa-lg" aria-hidden="true"></i>', //<i class="fa fa-info" aria-hidden="true"></i> for font-awsome
        't_show' => 'Show', //title Attribute
        'remove' => '<i class="fa fa-trash-o fa-lg" aria-hidden="true"></i>', //<i class="fa fa-trash-o" aria-hidden="true"></i> for font-awsome
        't_remove' => 'Remove', //title Attribute
    ],
    "select" => [
        "rows" => [
            "_" => "You have selected %d rows",
            "0" => "Click a row to select it",
            "1" => "Only 1 row selected",
        ]
    ]
];
