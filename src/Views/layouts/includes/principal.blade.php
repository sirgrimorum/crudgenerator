<!DOCTYPE html>
<html lang='es'>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <!-- Titulo del sitio -->
        <title>{{ trans('crudgenerator::admin.layout.tittle') }}</title>
        <!-- Icono del sitio -->
        <link rel="shortcut icon" href="{{asset('vendor/sirtrimorum/images/favicon.ico')}}">
        <!-- Metas adicionales -->
        <meta name="author" content="{{ trans('crudgenerator::admin.layout.metadata.author') }}">
        <meta name="title" content="{{ trans('crudgenerator::admin.layout.metadata.title') }}">
        <meta name="description" content="{{ trans('crudgenerator::admin.layout.metadata.description') }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <!-- Incluye los correspondientes -->
        @include("sirgrimorum::layouts.includes.mainstyle")
        <!-- Campo definido para incluir estilos especificos en las vistas que lo requieran -->
        @yield("selfcss")
    </head>

    <body>
        @include("sirgrimorum::layouts.includes.menu")
        <!-- Contenido de la pagina -->
        <div class="container" style='margin-top: 50px;'>
            <div id="main">
                @yield("contenido")
            </div>
            @include("sirgrimorum::layouts.includes.footer")
        </div>


        <!-- Incluye los javascript correspondientes -->
        @include("sirgrimorum::layouts.includes.mainjs")
        <!-- Campo definido para incluir los javascript especificos en las vistas que lo requieran -->
        @yield("selfjs")    
        @yield("modales")
    </body>
</html>
