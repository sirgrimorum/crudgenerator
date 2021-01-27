<?php

return [
    "labels" => [
        "catchedError" => "Error",
        "catchedErrors" => "Errors",
        "plural" => "Errors",
        "singular" => "Error",
        "type" => "Type",
        "url" => "Url",
        "exception" => "Type of Exception",
        "file" => "File",
        "line" => "Line",
        "message" => "Error",
        "occurrences" => "Variants",
        "trace" => "Trace",
        "request" => "Request",
        "reportar" => "Status",
        "create" => "Do not report a type of error",
        "show" => "Show",
        "edit" => "Edit",
        "remove" => "Delete",
    ],
    "placeholders" => [
        "exception" => "Exception",
        "file" => "Path of the file",
        "line" => "222",
        "message" => "Something very bad",
        "url" => "Where",
        "reportar" => "Choose one...",
    ],
    "descriptions" => [
        "type" => "Type of error",
        "url" => "Original url called when the error occur",
        "exception" => "Class of the Exception object",
        "file" => "Path to the place where the error occur",
        "line" => "Line where the error occur",
        "message" => "Message as reported by the system",
        "occurrences" => "Differences in other circumpstances that this error occur",
        "trace" => "Trace",
        "request" => "Data captured at the moment of the error",
        "reportar" => "Wether this kind of error is reported or not",
    ],
    "selects" => [
        "type" => [
            "val" => "Validation",
            "aut" => "Authentication",
            "rou" => "Http call",
            "api" => "API",
            "job" => "Job",
            "web" => "Web",
        ],
        "reportar" => [
            "0" => "Not report this kind of errors anymore",
            "1" => "Report this kind of errors every time they occur"
        ]
    ],
    "titulos" => [
        "index" => "Catched Errors",
        "create" => "Create a report of an error",
        "edit" => "Edit error",
        "show" => "Show error",
        "remove" => "Remove errors",
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

