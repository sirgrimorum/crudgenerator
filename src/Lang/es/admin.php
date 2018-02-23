<?php

return [
    'create' => [
        'titulo' => 'Crear'
    ],
    'edit' => [
        'titulo' => 'Editar'
    ],
    'index' => [
        'titulo' => 'Lista',
        'eliminar' => 'Eliminar',
        'ver' => 'Ver',
        'editar' => 'Editar'
    ],
    'layout' => [
        'title' => 'Administrador',
        'toggle_navigation' => 'Alternar navegación',
        'admin' => 'Administrador',
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
        'titulo' => 'Administrador',
        'ingresarfb' => 'Iniciar sessión con Facebook'
    ],
    'show' => [
    ],
    'metodosadicionales' => [
        'propietarios' => 'propietary',
        'nopropietarios' => 'no propietary',
    ],
    'formats' => [ // los formatos de carbon y moment deben dar el mismo resultado
        'carbon' => [
            'date' => 'd-m-Y',
            'datetime' => 'd-m-Y H:i:s',
            'time' => 'H:i:s',
            'timestamp' => 'd-m-Y H:i:s',
        ],
        'moment' => [
            'date' => 'DD-MM-YYYY',
            'datetime' => 'DD-MM-YYYY HH:mm:ss',
            'time' => 'HH:mm:ss',
            'timestamp' => 'DD-MM-YYYY HH:mm:ss',
        ]
    ],
    'messages' => [
        'permission' => 'No tiene permiso para ejecutar esta acción',
        'confirm_destroy' => '¿Está seguro que desea eliminar el registro ":modelName"?',
        'destroy_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido eliminado',
        'update_success' => '<strong>Listo!</strong> El se han guardado los cambios en el registro ":modelName"',
        'store_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido creado',
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