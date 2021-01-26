<?php

return [
    "labels" => [
        "catchedError" => "Error",
        "catchedErrors" => "Errors",
        "plural" => "Errors",
        "singular" => "Error",
        "type" => "Type",
        "exception" => "Type of Exception",
        "file" => "File",
        "line" => "Line",
        "message" => "Error",
        "occurrences" => "Variants",
        "trace" => "Trace",
        "request" => "Request",
        "create" => "Create error report",
        "ver" => "Show",
        "editar" => "Edit",
        "eliminar" => "Delete",
    ],
    "placeholders" => [
        "exception" => "Exception",
        "file" => "Path of the file",
        "line" => "222",
        "message" => "Something very bad",
    ],
    "descriptions" => [
        "type" => "Type of error",
        "exception" => "Class of the Exception object",
        "file" => "Path to the place where the error occur",
        "line" => "Line where the error occur",
        "message" => "Message as reported by the system",
        "occurrences" => "Differences in other circumpstances that this error occur",
        "trace" => "Trace",
        "request" => "Data captured at the moment of the error",
    ],
    "selects" => [
        "type" => [
            "val" => "Validation",
            "aut" => "Authentication",
            "rou" => "Http call",
            "api" => "API",
            "web" => "Web",
        ],
    ],
    "titulos" => [
        "index" => "Catched Errors",
        "create" => "Create a report of an error",
        "edit" => "Edit error",
        "show" => "Show error",
    ],
    "messages" => [
        'confirm_destroy' => 'Are you sure to delete the Error ":modelName"?',
        'destroy_success' => '<strong>Great!</strong> The Error ":modelName" has been deleted',
        'destroy_error' => '<strong>Sorry!</strong> The Error ":modelName" was not deleted',
        'update_success' => '<strong>Great!</strong> All changes in the Error ":modelName" has been saved',
        'update_error' => '<strong>Sorry!</strong> The changes in the Error ":modelName" were not saved',
        'store_success' => '<strong>Great!</strong> The Error ":modelName" has been created',
        'store_error' => '<strong>Sorry!</strong> The Error ":modelName" was not created',
    ],
];

