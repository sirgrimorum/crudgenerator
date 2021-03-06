<?php

return [
    'create' => [
        'titulo' => 'Create'
    ],
    'edit' => [
        'titulo' => 'Edit'
    ],
    'index' => [
        'titulo' => 'Index',
        'eliminar' => 'Delete',
        'ver' => 'Show',
        'editar' => 'Edit',
        'prefiltros' => 'Table configuration',
    ],
    'layout' => [
        'title' => 'Manager',
        'toggle_navigation' => 'Toogle navigation',
        'admin' => 'Manager',
        'all' => 'All',
        'administrador' => 'Manager',
        'idioma' => 'Language',
        'hola' => 'Welcome',
        'metadata' => [
            'author' => "Sir Grimorum",
            'title' => "SirGrimorum/CMS Manager",
            'description' => "Manager del CMS para Laravel hecho por Grimorum",
            'keywords' => "CMS, manager, administrator"
        ],
        'labels' => [
            'home' => 'Home',
            'home_admin' => 'Manager',
            'en' => 'English',
            'es' => 'Spanish',
            'close' => 'Close',
            'create' => 'Create',
            'edit' => 'Edit',
            'show' => 'Show',
            'remove' => 'Remove',
            'add' => 'Add',
            'info' => 'Info',
            'data' => 'Data',
            'yes' => 'Yes',
            'no' => 'No',
            'and' => 'and',
            'confirm_title' => '',
            'error_json_title' => 'Syntax error in the Json',
            'name' => 'Name',
            'file' => 'File',
            'new_file' => 'New File',
            'choose_file' => 'Choose File',
            'pretty_print' => 'Pretty Print',
            'seleccione' => "Select...",
            'todos' => "All",
            "de" => "from",
        ],
    ],
    'privado' => [
        'titulo' => 'Manager',
        'ingresarfb' => 'Log in with Facebook'
    ],
    'show' => [],
    'metodosadicionales' => [
        'propietarios' => 'propietary',
        'nopropietarios' => 'no propietary',
    ],
    'formats' => [ //carbon and moment formats results must coincide
        'carbon' => [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
            'timestamp' => 'Y-m-d H:i:s',
        ],
        'moment' => [
            'date' => 'YYYY-MM-DD',
            'datetime' => 'YYYY-MM-DD HH:mm:ss',
            'time' => 'HH:mm:ss',
            'timestamp' => 'YYYY-MM-DD HH:mm:ss',
        ]
    ],
    'messages' => [
        'permission' => "You don't have permission to execute this action",
        'not_found' => 'Registry ":modelId" not found',
        'error_json' => '',
        'confirm_destroy' => 'Are you sure to delete ":modelName"?',
        'destroy_success' => '<strong>Great!</strong> ":modelName" has been deleted',
        'destroy_error' => '<strong>Sorry!</strong> ":modelName" was not deleted',
        'confirm_removepivot' => 'Are you sure to remove the reference to ":modelName"?',
        'confirm_removefile' => 'Are you sure to remove and delete the ":modelName" file?',
        'pivot_exists_title' => 'Exists!',
        'pivot_exists_message' => 'You already have a reference to this item',
        'pivot_justone_title' => 'Just one!',
        'pivot_justone_message' => 'You could only choose one.<br><br>If you want to change it, remove the other one (red button next to the title).',
        'update_success' => '<strong>Great!</strong> All changes in ":modelName" has been saved',
        'update_error' => '<strong>Sorry!</strong> The changes in ":modelName" were not saved',
        'store_success' => '<strong>Great!</strong> ":modelName" has been created',
        'store_error' => '<strong>Sorry!</strong> ":modelName" was not created',
        '200' => 'OK',
        '422' => 'Validation error',
        '404' => 'Page not found',
        'na' => 'This request doesn\'t make sens',
        'no_result_query' => 'No result for "{{query}}"',
        'no_model_class' => 'There is no Model class for the model named ":Modelo" in CrudGenerator::getConfig(":modelo")',
        'smart_config_error' => 'The SmartMerge of the config for the model ":Modelo" went wrong',
        'no_table_for_model' => 'There is no valid table for the model named ":Modelo" in CrudGenerator::getConfig(":modelo")',
        'error_preparing_file_for_model' => 'Error preparing the file for the model ":Modelo"',
        'error_preparing_file' => 'Error preparing the file',
        'no_stream_for_reading' => 'Could not open stream for reading ":modelo"',
        'no_value_in_celda' => 'Could not set the value of the field ":campo" in ":modelos"'
    ],
    'error_messages' => [
        'unique_composite' => 'Already exists a row with the same combination of :fields',
        'with_articles' => 'Is required to set a version in :langs for the field ":attribute"',
        'older_than' => 'The value for the field ":attribute" must be over :min_age years old',
    ],
];
