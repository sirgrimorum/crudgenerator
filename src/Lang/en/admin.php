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
        'editar' => 'Edit'
    ],
    'layout' => [
        'title' => 'Manager',
        'toggle_navigation' => 'Toogle navigation',
        'admin' => 'Manager',
        'crear' => 'Create',
        'ver' => 'Show',
        'editar' => 'Editar',
        'borrar' => 'Borrar',
        'administrador' => 'Administrator',
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
            'en' => 'English',
            'es' => 'Español',
            'close' => 'Close',
            'create' => 'Create',
            'yes' => 'Yes',
            'no' => 'No',
        ],
    ],
    'privado' => [
        'titulo' => 'Manager',
        'ingresarfb' => 'Log in with Facebook'
    ],
    'show' => [
    ],
    'metodosadicionales' => [
        'propietarios' => 'propietary',
        'nopropietarios' => 'no propietary',
    ],
    'formats'=>[ //carbon and moment formats results must coincide
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
    'messages'=>[
        'permission' => "You don't have permission to execute this action",
        'confirm_destroy' => 'Are you sure to delete ":modelName"?',
        'destroy_success' => '<strong>Great!</strong> ":modelName" has been deleted',
        'update_success' => '<strong>Great!</strong> All changes in ":modelName" has been saved',
        'store_success' => '<strong>Great!</strong> ":modelName" has been created',
        '200' => 'OK',
        '422' => 'Validation error',
        '404' => 'Page not found',
        'na' => 'This request doesn\'t make sens',
    ],
    'error_messages' => [
        'unique_composite' => 'Already exists a row with the same combination of :fields',
    ],
        ]
?>