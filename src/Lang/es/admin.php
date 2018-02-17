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
        'title' => 'Administrador',
        'toggle_navigation' => 'Alternar navegación',
        'admin' => 'Manager',
        'crear' => 'Crear',
        'ver' => 'Detalles',
        'editar' => 'Editar',
        'borrar' => 'Borrar',
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
            'close' => 'Cerrar',
            'create' => 'Crear',
            'yes' => 'Si',
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
    'messages' => [
        'permission' => 'No tiene permiso para ejecutar esta acción',
        'confirm_destroy' => '¿Está seguro que desea eliminar?',
        '200' => 'Listo',
        '422' => 'Error de validación',
        '404' => 'Página no encontrada',
        'na' => 'Esta solicitud no tiene sentido',
    ],
    'error_messages' => [
        'required' => 'El campo ":attribute" es obligatorio',
        'numeric' => 'El campo ":attribute" debe ser un valor numérico',
        'integer' => 'El campo ":attribute" debe ser un valor numérico sin decimales',
        'max' => [
            'string' => 'El campo ":attribute" debe tener máximo :max caracteres',
        ],
        'min' => [
            'string' => 'El campo ":attribute" debe tener mínimo :min caracteres',
            'numeric' => 'El campo ":attribute"  debe ser mayor a :min',
        ],
        'exists' => 'El valor escogido para el campo ":attribute"  no se encuentra en la base de datos',
        'unique_composite' => 'Ya existe un registro con la misma combinación de :fields',
    ],
        ]
?>