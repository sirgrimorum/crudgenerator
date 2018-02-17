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
        'eliminar' => 'Eliminar',
        'ver' => 'Ver',
        'editar' => 'Editar'
    ],
    'layout' => [
        'title' => 'Manager',
        'toggle_navigation' => 'Alternar navegación',
        'admin' => 'Manager',
        'crear' => 'Crear',
        'ver' => 'Ver',
        'administrador' => 'Administrador',
        'idioma' => 'Idioma',
        'hola' => 'Bienvenido',
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
    'formats'=>[
        'date'=>'YYYY-MM-DD',
        'datetime'=>'YYYY-MM-DD HH:mm:ss',
        'time'=>'HH:mm:ss',
        'timestamp'=>'YYYY-MM-DD HH:mm:ss',
    ],
    'messages'=>[
        'permission' => 'No tiene permiso para ejecutar esta acción',
        'confirm_destroy' => '¿Está seguro que desea eliminar el registro ":modelName"?',
        'destroy_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido eliminado',
        'update_success' => '<strong>Listo!</strong> El se han guardado los cambios en el registro ":modelName"',
        'store_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido creado',
        '200' => 'OK',
        '422' => 'Validation error',
        '404' => 'Page not found',
        'na' => 'This request doesn\'t make sens',
    ],
    'error_messages' => [
        'unique_composite' => 'Already exists a row with the same combination of :composite',
    ],
        ]
?>