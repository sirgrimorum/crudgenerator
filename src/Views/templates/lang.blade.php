{?php}

return [
    "labels" => [
        "{model}" => "{Model}",
        "{{str_plural($modelo)}}" => "{{ucfirst(strstr_plural($modelo))}}",
        "plural" => "{{ucfirst(str_plural($modelo))}}",
        "singular" => "{Model}",
        @foreach($config['campos'] as $campo=>$datos)
        "{{ $campo }}" => "{{ str_replace(["_"],[" "],ucfirst($campo)) }}",
        @endforeach
        "edit" => "Save changes",
        "create" => "Create {model}",
        "show" => "View",
        "remove" => "Remove",
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
        "index" => "{{ucfirst(str_plural($modelo))}}",
        "create" => "Create {model}",
        "edit" => "Edit {model}",
        "show" => "View {model}",
    ],
    "messages" => [
        'confirm_destroy' => 'Are you sure to delete the {Model} ":modelName"?',
        'destroy_success' => '{!!"<strong>"!!}Great!{!!"</strong>"!!} the {Model} ":modelName" has been deleted',
        'update_success' => '{!!"<strong>"!!}Great!{!!"</strong>"!!} All changes in the {Model} ":modelName" has been saved',
        'store_success' => '{!!"<strong>"!!}Great!{!!"</strong>"!!} The {Model} ":modelName" has been created',
    ],
        ];

