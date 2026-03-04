# CrudGenerator

![Latest Version on Packagist](https://img.shields.io/packagist/v/sirgrimorum/crudgenerator.svg?style=flat-square)
![PHP Version](https://img.shields.io/packagist/php-v/sirgrimorum/crudgenerator.svg?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/sirgrimorum/crudgenerator.svg?style=flat-square)
![License](https://img.shields.io/packagist/l/sirgrimorum/crudgenerator.svg?style=flat-square)

A config-driven CRUD framework for Laravel. Describe your model's fields in a PHP array and get a fully functional admin interface — create/edit forms, show views, searchable DataTables lists, file uploads, relationship selects, validation, and REST endpoints — without writing controllers, views, or JS glue code.

## Features

- **20+ field types** — text, email, password, number, textarea, HTML (CKEditor), date/datetime/time, select, checkbox, radio, relationship (BelongsTo), relationships (ManyToMany), files (single and multiple), color picker, range slider, JSON editor, computed functions, and more
- **Auto-generated admin** — create, show, edit, list, and delete views from a single config array
- **DataTables integration** — server-side or client-side paging, search, multi-select, export (Excel, PDF, copy)
- **Validation** — reads `$rules` from the model; custom validators for composite unique, older-than age, unique-with
- **File uploads** — single or multiple files, automatic image resizing/thumbnailing, disk/path configurable
- **Relationship fields** — `BelongsTo` selects, `ManyToMany` with pivot columns, typeahead search
- **Access control** — permission closure in config; per-action permission checks
- **Dynamic value prefixes** — `__route__`, `__trans__`, `__asset__`, `__config__`, `__view__`, and more evaluated at render time
- **Modal support** — create/edit forms can be embedded in Bootstrap modals
- **Artisan code generation** — scaffold model config, language file, Eloquent model class, and getter methods
- **TransArticles integration** — `article` field type stores rich content in the articles table

## Requirements

- PHP >= 8.2
- Laravel >= 9.0
- intervention/image ^3.0
- doctrine/dbal ^3.0|^4.0

## Installation

```bash
composer require sirgrimorum/crudgenerator
```

### Run migrations

```bash
php artisan migrate
```

### Publish configuration

```bash
php artisan vendor:publish --provider="Sirgrimorum\CrudGenerator\CrudGeneratorServiceProvider" --tag=config
```

Publishes `config/sirgrimorum/crudgenerator.php`.

### Publish language files

```bash
php artisan vendor:publish --provider="Sirgrimorum\CrudGenerator\CrudGeneratorServiceProvider" --tag=lang
```

### Publish views (optional)

```bash
php artisan vendor:publish --provider="Sirgrimorum\CrudGenerator\CrudGeneratorServiceProvider" --tag=views
```

### Publish front-end assets

```bash
php artisan crudgen:resources
```

Publishes DataTables, CKEditor, Select2, DateTimePicker, and other JavaScript dependencies to `public/vendor/sirgrimorum/`.

## Quick Start

### 1. Generate a config file for your model

```bash
php artisan crudgen:createconfig Post
```

This creates `config/sirgrimorum/models/post.php` with all columns pre-filled from the database schema.

### 2. Register the model in the main config

```php
// config/sirgrimorum/crudgenerator.php
'admin_routes' => [
    'post' => 'config/sirgrimorum/models/post',
],
```

### 3. Access the admin interface

Navigate to `/{locale}/crud/post` to see the list, create, edit, and delete interface.

## Model Configuration

Every feature of the CRUD admin is controlled by a config array. Store it in `config/sirgrimorum/models/modelname.php`.

### Minimal example

```php
use App\Models\Post;

return [
    'modelo' => Post::class,
    'tabla'  => 'posts',
    'nombre' => 'title',    // Field used as the display name
    'id'     => 'id',
    'campos' => [
        'title' => [
            'tipo'  => 'text',
            'label' => 'Title',
        ],
        'body' => [
            'tipo'  => 'html',
            'label' => 'Body',
        ],
        'published_at' => [
            'tipo'  => 'datetime',
            'label' => 'Published At',
        ],
    ],
];
```

### Field type reference

| Type | Description |
|------|-------------|
| `text` | Single-line text input |
| `email` | Email input with validation |
| `url` | URL input |
| `password` | Password input (hashed on save) |
| `number` | Numeric input with optional format |
| `textarea` | Multi-line plain text |
| `html` | Rich text via CKEditor |
| `article` | Rich text stored in TransArticles table |
| `date` | Date picker |
| `datetime` | Date + time picker |
| `time` | Time picker |
| `select` | Dropdown from `opciones` array or callable |
| `checkbox` | Checkbox group |
| `radio` | Radio button group |
| `relationship` | BelongsTo — single related model select |
| `relationships` | HasMany/ManyToMany — multi-select list |
| `relationshipssel` | ManyToMany with pivot columns and typeahead |
| `file` | Single file upload |
| `files` | Multiple file upload (JSON array in DB) |
| `color` | Color picker |
| `slider` | Range slider |
| `json` | JSON editor (JSON Editor library) |
| `hidden` | Hidden input |
| `function` | Read-only computed value (calls model method) |

### Complete field options

```php
'status' => [
    'tipo'          => 'select',
    'label'         => 'Status',
    'placeholder'   => 'Choose a status',
    'description'   => 'Shown below the label',
    'help'          => 'Shown below the input',
    'valor'         => 'draft',              // Default value (or callable)
    'opciones'      => [                     // Options for select/checkbox/radio
        'draft'     => 'Draft',
        'published' => 'Published',
        'archived'  => 'Archived',
    ],
    'hide'          => ['list'],             // Hide in these views: create, edit, show, list
    'conditional'   => ['type' => 'post'],  // Only show when 'type' field = 'post'
    'enlace'        => "__route__posts.show,:modelId",  // Wrap list value in a link
    'truncate'      => 100,                  // Truncate text in list view
    'extraClassDiv'   => 'col-md-6',
    'extraClassInput' => 'form-control-sm',
    'tipos_temporales' => [
        'create' => 'hidden',                // Different type when creating
    ],
],

'author_id' => [
    'tipo'     => 'relationship',
    'label'    => 'Author',
    'modelo'   => App\Models\User::class,
    'campo'    => 'name',                   // Display field on related model
    'id'       => 'id',
    'nombre'   => 'name',
    'todos'    => 'all',                    // 'all', callable, or query builder
],

'photo' => [
    'tipo'   => 'file',
    'label'  => 'Photo',
    'path'   => 'uploads/posts/',
    'disk'   => 'public',
    'resize' => [
        'width'   => 800,
        'height'  => 600,
        'path'    => 'uploads/posts/thumbs/',
        'quality' => 85,
    ],
],

'metadata' => [
    'tipo'  => 'json',
    'label' => 'Metadata',
],
```

## Rendering Admin Views

Use the static methods anywhere in your own controllers or views:

```blade
{{-- List with DataTables --}}
{!! CrudGenerator::lists($config, true) !!}

{{-- Create form --}}
{!! CrudGenerator::create($config) !!}

{{-- Edit form --}}
{!! CrudGenerator::edit($config, $id) !!}

{{-- Show (read-only) --}}
{!! CrudGenerator::show($config, $id) !!}
```

Or use the built-in routes at `/{locale}/{admin_prefix}/{model}` (index / create / show / edit / destroy).

## Validation

CrudGenerator reads `$rules` from your model automatically:

```php
class Post extends Model
{
    public static $rules = [
        'title' => 'required|string|max:255',
        'body'  => 'required',
        'slug'  => 'required|unique:posts,slug',
    ];
}
```

Custom validation messages via `$error_messages`:

```php
public static $error_messages = [
    'title.required' => 'The post title cannot be blank.',
];
```

### Custom validators provided by this package

| Rule | Description |
|------|-------------|
| `unique_composite:table,col1,col2` | Unique across multiple columns |
| `unique_with:table,col1,col2` | Alias for composite unique |
| `older_than:18` | Minimum age validation for date fields |
| `with_articles:locale1,locale2` | All article locales must be filled |

## Artisan Commands

| Command | Description |
|---------|-------------|
| `crudgen:createconfig {Model}` | Generate a model config file from DB schema |
| `crudgen:createlang {Model}` | Generate a language file for the model |
| `crudgen:createmodel {Model}` | Generate an Eloquent model class stub |
| `crudgen:addget {Model}` | Add `get($field)` accessor method to model |
| `crudgen:resources` | Publish front-end assets (JS/CSS libraries) |
| `crudgen:registererror` | Set up error tracking model |
| `crudgen:registermiddleware` | Register admin middleware |
| `crudgen:sendalert {title} {message}` | Broadcast an alert to the admin |

## API Reference

### `CrudGenerator::lists()`

```php
CrudGenerator::lists(
    array $config,
    bool  $modales   = false,   // Enable Bootstrap modal buttons
    bool  $simple    = false,   // Minimal render (no JS/CSS includes)
    mixed $registros = null     // Custom Eloquent collection override
): string
```

### `CrudGenerator::create()`

```php
CrudGenerator::create(array $config, bool $simple = false, bool $botonModal = false): string
```

### `CrudGenerator::edit()`

```php
CrudGenerator::edit(
    array $config,
    mixed $id        = null,
    bool  $simple    = false,
    mixed $registro  = null,
    bool  $botonModal = false
): string
```

### `CrudGenerator::show()`

```php
CrudGenerator::show(array $config, mixed $id = null, bool $simple = false, mixed $registro = null): string
```

### `CrudGenerator::validateModel()`

```php
CrudGenerator::validateModel(array $config, \Illuminate\Http\Request $request): \Illuminate\Validation\Validator|false
```

### `CrudGenerator::saveObjeto()`

```php
CrudGenerator::saveObjeto(array $config, \Illuminate\Http\Request $request, mixed $registro = null): Model
```

Saves (creates or updates) a model instance, handles file uploads, and syncs relationships.

### `CrudGenerator::getConfigWithParametros()`

```php
CrudGenerator::getConfigWithParametros(string $modelo): array
```

Loads the model config, evaluates all `__prefix__` values, and merges request parameters.

## Dynamic Value Prefixes

Use these in any config value to have it evaluated at render time:

| Prefix | Resolves to |
|--------|-------------|
| `__route__name` | `route('name')` |
| `__url__/path` | `url('/path')` |
| `__trans__key` | `trans('key')` |
| `__asset__path` | `asset('path')` |
| `__getLocale__` | `App::getLocale()` |
| `__config__key` | `config('key')` |
| `__view__template` | `view('template')->render()` |
| `__transarticle__scope.key` | TransArticles content |

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
