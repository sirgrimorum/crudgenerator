{?php}

return [
    "labels" => [
        "{model}" => "{Model}",
        "{{\Illuminate\Support\Str::plural($modelo)}}" => "{{ucfirst(\Illuminate\Support\Str::plural($modelo))}}",
        "plural" => "{{ucfirst(\Illuminate\Support\Str::plural($modelo))}}",
        "singular" => "{Model}",
        @foreach($config['campos'] as $campo=>$datos)
        @if ($campo == "created_at")
        "{{ $campo }}" => "Creado el",
        @elseif($campo == "updated_at")
        "{{ $campo }}" => "Actualizado el",
        @elseif($campo == "first_name")
        "{{ $campo }}" => "Nombre",
        @elseif($campo == "articles")
        "{{ $campo }}" => "Artículos",
        @elseif($campo == "users")
        "{{ $campo }}" => "Usuarios",
        @elseif($campo == "phone")
        "{{ $campo }}" => "Teléfono",
        @elseif($campo == "last_name")
        "{{ $campo }}" => "Apellido",
        @elseif($campo == "password")
        "{{ $campo }}" => "Clave",
        @elseif($campo == "remember_token")
        "{{ $campo }}" => "Token de clave",
        @else
        "{{ $campo }}" => "{{ ucfirst(str_replace(["_","tion","image","file", "name", "title", "text", "code", "content", "comment", "document", "picture", "type", "state", "order"],[" ","ción","imagen", "archivo", "nombre", "título", "texto", "código", "contenido", "comentario", "documento","foto", "tipo", "estado", "orden"],$campo)) }}",
        @endif
        @endforeach
        "edit" => "Guardar cambios",
        "create" => "Crear {model}",
        "show" => "Ver",
        "remove" => "Eliminar",
    ],
    "placeholders" => [
        @foreach($config['campos'] as $campo=>$datos)
        @if ($campo == "created_at")
        "{{ $campo }}" => "Creado el",
        @elseif($campo == "updated_at")
        "{{ $campo }}" => "Actualizado el",
        @elseif($campo == "first_name")
        "{{ $campo }}" => "Nombre",
        @elseif($campo == "articles")
        "{{ $campo }}" => "Artículos",
        @elseif($campo == "users")
        "{{ $campo }}" => "Usuarios",
        @elseif($campo == "phone")
        "{{ $campo }}" => "Teléfono",
        @elseif($campo == "last_name")
        "{{ $campo }}" => "Apellido",
        @elseif($campo == "password")
        "{{ $campo }}" => "Clave",
        @elseif($campo == "remember_token")
        "{{ $campo }}" => "Token de clave",
        @else
        "{{ $campo }}" => "{{ ucfirst(str_replace(["_","tion","image","file", "name", "title", "text", "code", "content", "comment", "document", "picture", "type", "state", "order"],[" ","ción","imagen", "archivo", "nombre", "título", "texto", "código", "contenido", "comentario", "documento","foto", "tipo", "estado", "orden"],$campo)) }}",
        @endif
        @endforeach
    ],
    "descriptions" => [
        @foreach($config['campos'] as $campo=>$datos)
        @if ($campo == "created_at")
        "{{ $campo }}" => "Creado el",
        @elseif($campo == "updated_at")
        "{{ $campo }}" => "Actualizado el",
        @elseif($campo == "first_name")
        "{{ $campo }}" => "Nombre",
        @elseif($campo == "articles")
        "{{ $campo }}" => "Artículos",
        @elseif($campo == "users")
        "{{ $campo }}" => "Usuarios",
        @elseif($campo == "phone")
        "{{ $campo }}" => "Teléfono",
        @elseif($campo == "last_name")
        "{{ $campo }}" => "Apellido",
        @elseif($campo == "password")
        "{{ $campo }}" => "Clave",
        @elseif($campo == "remember_token")
        "{{ $campo }}" => "Token de clave",
        @else
        "{{ $campo }}" => "{{ ucfirst(str_replace(["_","tion","image","file", "name", "title", "text", "code", "content", "comment", "document", "picture", "type", "state", "order"],[" ","ción","imagen", "archivo", "nombre", "título", "texto", "código", "contenido", "comentario", "documento","foto", "tipo", "estado", "orden"],$campo)) }}",
        @endif
        @endforeach
    ],
    "selects" => [
        "{field}" => [
            "{value1}" => "{option1}",
            "{value2}" => "{option2}",
        ],
    ],
    "titulos" => [
        "index" => "{{ucfirst(\Illuminate\Support\Str::plural($modelo))}}",
        "create" => "Crear {model}",
        "edit" => "Editar {model}",
        "show" => "Ver {model}",
    ],
    "messages" => [
        'confirm_destroy' => '¿Está seguro que quiere eliminar el {Model} ":modelName"?',
        'destroy_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} El {Model} ":modelName" ha sido eliminado',
        'destroy_error' => '{!!"<strong>"!!}Lo sentimos!{!!"</strong>"!!} El {Model} ":modelName" NO ha sido eliminado',
        'update_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} Todos los cambios en el {Model} ":modelName" han sido guardados',
        'update_error' => '{!!"<strong>"!!}Lo sentimos!{!!"</strong>"!!} Los cambios en el {Model} ":modelName" NO han sido guardados',
        'store_success' => '{!!"<strong>"!!}Listo!{!!"</strong>"!!} El {Model} ":modelName" ha sido creado',
        'store_error' => '{!!"<strong>"!!}Lo sentimos!{!!"</strong>"!!} El {Model} ":modelName" NO ha sido creado',
    ],
        ];

