<?php

return [
    "labels" => [
        "articulo" => "Article",
        "articulos" => "Articles",
        "plural" => "Articles",
        "singular" => "Articles",
        "lang" => "Language",
        "scope" => "Section",
        "nickname" => "Article",
        "activated" => "Activated",
        "content" => "Content",
        "user_id" => "Author",
        "edit" => "Edit article",
        "create" => "Create article",
        "show" => "Show",
        "edit" => "Edit",
        "remove" => "Delete",
    ],
    "placeholders" => [
        "scope" => "Name of the section",
        "nickname" => "Code of the article",
    ],
    "descriptions" => [
        "lang" => "Language of this version of the article",
        "scope" => "Name of the section to which this article belongs",
        "nickname" => "Name of the article inside the section, use . for anidation, do not use blanks",
        "activated" => "Whether the article is shown or not",
        "content" => "The article",
        "author" => "Author",
    ],
    "selects" => [
        "lang" => [
            "es" => "Español",
            "en" => "Inglés",
        ],
    ],
    "titulos" => [
        "index" => "Articles",
        "create" => "Create an article",
        "edit" => "Edit an article",
        "show" => "Show article",
        "remove" => "Remove articles"
    ],
    "messages" => [
        'confirm_destroy' => 'Are you sure to delete the article ":modelName"?',
        'destroy_success' => '<strong>Great!</strong> The article ":modelName" has been deleted',
        'destroy_error' => '<strong>Sorry!</strong> The article ":modelName" was not deleted',
        'update_success' => '<strong>Great!</strong> All changes in the article ":modelName" has been saved',
        'update_error' => '<strong>Sorry!</strong> The changes in the article ":modelName" were not saved',
        'store_success' => '<strong>Great!</strong> The article ":modelName" has been created',
        'store_error' => '<strong>Sorry!</strong> The article ":modelName" was not created',
    ],
];
