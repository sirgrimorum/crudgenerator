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
        'editar' => 'Editar',
        'prefiltros' => 'Configuración de la tabla',
    ],
    'layout' => [
        'title' => 'Administrador',
        'toggle_navigation' => 'Alternar navegación',
        'admin' => 'Administrador',
        'all' => 'Todos',
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
            'home_admin' => 'Administrador',
            'en' => 'Inglés',
            'es' => 'Español',
            'close' => 'Cerrar',
            'create' => 'Crear',
            'edit' => 'Editar',
            'show' => 'Detalles',
            'remove' => 'Quitar',
            'add' => 'Añadir',
            'info' => 'Información',
            'data' => 'Datos',
            'yes' => 'Si',
            'no' => 'No',
            'and' => 'y',
            'confirm_title' => '',
            'error_json_title' => 'Error de sintaxis en el Json',
            'name' => 'Nombre',
            'file' => 'Archivo',
            'new_file' => 'Nuevo Archivo',
            'choose_file' => 'Seleccione un archivo',
            'pretty_print' => 'Dar formato',
            'seleccione' => "Seleccione...",
            'todos' => "Todos",
            "de" => "de",
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
        'not_found' => 'No se encuentra el registro ":modelId"',
        'error_json' => '',
        'confirm_destroy' => '¿Está seguro que desea eliminar el registro ":modelName"?',
        'confirm_removepivot' => '¿Está seguro que desea quitar la referencia al registro ":modelName"?',
        'confirm_removefile' => '¿Está seguro que desea quitar y eliminar el archivo ":modelName"?',
        'pivot_exists_title' => 'Ya existe!',
        'pivot_exists_message' => 'Ya habia seleccionado este item',
        'pivot_justone_title' => 'Solo uno!',
        'pivot_justone_message' => 'Solo se puede seleccionar uno. <br><br>Si quiere cambiarlo, elimine el otro primero (botón rojo al lado del título)',
        'destroy_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido eliminado',
        'destroy_error' => '<strong>Lo sentimos!</strong> El registro ":modelName" NO ha sido eliminado',
        'update_success' => '<strong>Listo!</strong> Se han guardado los cambios en el registro ":modelName"',
        'update_error' => '<strong>Lo sentimos!</strong> No se han guardado los cambios en el registro ":modelName"',
        'store_success' => '<strong>Listo!</strong> El registro ":modelName" ha sido creado',
        'store_error' => '<strong>Lo sentimos!</strong> El registro ":modelName" NO ha sido creado',
        '200' => 'Listo',
        '422' => 'Error de validación',
        '404' => 'Página no encontrada',
        'na' => 'Esta solicitud no tiene sentido',
        'no_result_query' => 'No hay resultados para "{{query}}"',
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
        'with_articles' => 'Es obligatorio ingresar la versión en :langs del campo ":attribute"',
        'older_than' => 'El valor del campo ":attribute" debe ser mayor a :min_age años',
    ],
];
