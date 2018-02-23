<?php

return [
    "modelo" => "\Sirgrimorum\TransArticles\Models\Article", // el modelo
    "tabla" => "articles",
    "nombre" => "name",
    "id" => "id",
    "url" => "Sirgrimorum_CrudAdministrator", //change if you want to handle it diferently
    "botones" => "__trans__crudgenerator::article.labels.create", //Overriden if url is Sirgrimorum_CrudAdministrator
    "files" => true,
    "campos" => [
        "lang" => [
            "tipo" => "select",
            "label" => "__trans__crudgenerator::article.labels.lang",
            "opciones" => "__trans__crudgenerator::article.selects.lang"
        ],
        "scope" => [
            "tipo" => "text",
            "label" => "__trans__crudgenerator::article.labels.scope",
            "placeholder" => "__trans__crudgenerator::article.placeholders.scope",
        ],
        "nickname" => [
            "tipo" => "text",
            "label" => "__trans__crudgenerator::article.labels.nickname",
            "placeholder" => "__trans__crudgenerator::article.placeholders.nickname",
        ],
        "activated" => [
            "tipo" => "checkbox",
            "label" => "__trans__crudgenerator::article.labels.activated",
            "description" => "__trans__crudgenerator::article.descriptions.activated",
            //"valor" => true,
            "value" => true
        ],
        "content" => [
            "tipo" => "html",
            "label" => "__trans__crudgenerator::article.labels.content",
            "description" => "__trans__crudgenerator::article.descriptions.content",
        ],
        "user" => [
            "label" => "__trans__crudgenerator::article.labels.user_id",
            "tipo" => "relationship",
            "modelo" => "App\User",
            "id" => "id",
            "campo" => "name",
            "todos" => "",
        //"enlace" => URL::to(Lang::get("principal.menu.links.usuario", array("{ID}"),
        ],
    ],
    "rules" => [ //the validation rules. If not here, it whill search for them in the model, public property with the same name
        'nickname' => 'bail|required|max:50|unique_composite:articles,scope,lang',
        'scope' => 'bail|required|max:50',
        'lang' => 'bail|required|max:10',
        'content' => 'required',
        'user' => 'bail|required|integer|min:0|exists:users,id',
    ],
    "error_messages" => [ //the validation error messages. If not here, it whill search for them in the model, public property with the same name
        /**
         *  'nickname'=>[
         *      'required'=>'Es obligatorio indicar el nombre del artículo',
         *  ],
         */
    ],
    "permissions" => [ //the permissions to validate before doing an action, if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
        "default" => function() {
            return true;
        }, // the default permission to validate if others not present, false send back to the 'sirgrimorum_cms::login_path' 
    /* "index" => [closure that return true or false], // permission for the index action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
      "create" => [closure that return true or false], // permission for the create action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "show" => [closure($object) that return true or false], // permission for the show action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "edit" => [closure($object) that return true or false], // permission for the edit action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "update" => [closure($object) that return true or false], // permission for the update action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "destroy" => [closure($object) that return true or false], // permission for the delete action of Crud, false send back to the 'sirgrimorum_cms::login_path' */
    ],
];

