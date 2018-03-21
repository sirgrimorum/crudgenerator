{?php}

return [
    "labels" => [
        "{model}" => "{Model}",
        "{model}s" => "{Model}s",
        "plural" => "{Model}s",
        "singular" => "{Model}s",
        @foreach($config['campos'] as $campo=>$datos)
        "{{ $campo }}" => "{{ str_replace(["_"],[" "],ucfirst($campo)) }}",
        @endforeach
        "edit" => "Guardar cambios",
        "create" => "Crear {model}",
        "show" => "Ver",
        "remove" => "Eliminar",
    ],
    "placeholders" => [
        @foreach($config['campos'] as $campo=>$datos)
        "{{ $campo }}" => "{{ str_replace(["_"],[" "],ucfirst($campo)) }}",
        @endforeach
    ],
    "descriptions" => [
        @foreach($config['campos'] as $campo=>$datos)
        "{{ $campo }}" => "{{ str_replace(["_"],[" "],ucfirst($campo)) }}",
        @endforeach
    ],
    "selects" => [
        "{field}" => [
            "{value1}" => "{option1}",
            "{value2}" => "{option2}",
        ],
    ],
    "titulos" => [
        "index" => "{Model}s",
        "create" => "Crear {model}",
        "edit" => "Editar {model}",
        "show" => "Ver {model}",
    ],
    "messages" => [
        'confirm_destroy' => '¿Está seguro que quiere eliminar el {Model} ":modelName"?',
        'destroy_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} el {Model} ":modelName" ha sido eliminado',
        'update_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} Todos los cambios en el {Model} ":modelName" han sido guardados',
        'store_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} El {Model} ":modelName" ha sido creado',
    ],
        ];
