<!DOCTYPE html>
<html lang='es'>

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <!-- Titulo del sitio -->
    <title>{{ trans('crudgenerator::admin.layout.title') }}</title>
    <!-- Icono del sitio -->
    <link rel="shortcut icon" href="{{asset('vendor/sirgrimorum/images/favicon.ico')}}">
    <!-- Metas adicionales -->
    <meta name="author" content="{{ trans('crudgenerator::admin.layout.metadata.author') }}">
    <meta name="title" content="{{ trans('crudgenerator::admin.layout.metadata.title') }}">
    <meta name="description" content="{{ trans('crudgenerator::admin.layout.metadata.description') }}">
    <meta name="keywords" content="{{ trans('crudgenerator::admin.layout.metadata.keywords') }}">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-param" content="_token">
    <style>
        /* same height */
        /* columns of same height styles */
        .container-xs-height {
            display: table;
            padding-left: 0px;
            padding-right: 0px;
        }

        .row-xs-height {
            display: table;
            width: 100%;
        }

        .col-xs-height {
            display: table-cell;
            float: none !important;
        }

        /* footer */
        footer {
            position: static;
            bottom: 0px;
            width: 100%;
        }

        .cont_footer {
            padding-top: 20px;
            padding-bottom: 20px;
            /*height: 100px;*/
            text-align: center;
        }

        .cont_footer,
        .cont_footer a {
            color: #eee;
        }

        /********************** Side bar */

        .bd-sidebar {
            order: 0;
            // background-color: #f5f2f9;
            border-bottom: 1px solid rgba(0, 0, 0, .1);
        }

        @media (min-width: 768px) {
            .bd-sidebar {
                position: sticky;
                top: 4rem;
                z-index: 1000;
                height: subtract(100vh, 4rem);
                border-right: 1px solid rgba(0,0,0,.1);
            }
        }

        @media (min-width: 1200px) {
            .bd-sidebar {
                flex: 0 1 320px;
            }
        }

        .bd-links {
            padding-top: 1rem;
            padding-bottom: 1rem;
            margin-right: -15px;
            margin-left: -15px;
        }

        @media (min-width: 768px) {
            .bd-links {
                    max-height: subtract(100vh, 9rem);
                    overflow-y: auto;
                    display: block !important;
            }
        }
        .bd-search {
            position: relative; // To contain the Algolia search
            padding: 1rem 15px;
            margin-right: -15px;
            margin-left: -15px;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
        }

        .bd-search-docs-toggle {
            color: $gray-900;
        }

        .bd-sidenav {
            display: none;
        }

        .bd-toc-link {
            display: block;
            padding: .25rem 1.5rem;
            font-weight: 600;
            color: rgba(0, 0, 0, .65);
        }
        .bd-toc-link:hover {
            color: rgba(0, 0, 0, .85);
            text-decoration: none;
        }

        .bd-toc-item.active {
                margin-bottom: 1rem;
        }
        .bd-toc-item.active:not(:first-child) {
            margin-top: 1rem;
        }
        .bd-toc-item.active>.bd-toc-link:not(.active)  {
            color: rgba(0, 0, 0, .85);
        }
        .bd-toc-item.active>.bd-toc-link:not(.active):hover  {
            background-color: transparent;
        }
        .bd-toc-item.active>.bd-sidenav {
            display: block;
        }

        // All levels of nav
        .bd-sidebar .nav>li>a {
            display: block;
            padding: .25rem 1.5rem;
            font-size: 90%;
            color: rgba(0, 0, 0, .65);
        }

        .bd-sidebar .nav>li>a:not(.active):hover {
            color: rgba(0, 0, 0, .85);
            text-decoration: none;
            background-color: transparent;
        }


        /******************/
    </style>
    <!-- Campo definido para incluir estilos especificos en las vistas que lo requieran -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    {!! JSLocalization::put(Auth::user(),"currentUser") !!}
    {!! JSLocalization::put(config("sirgrimorum.crudgenerator"),"crudgenConfig") !!}
    <!-- LinksTagLoader -->
    @addLinkTagsLoader()
    @if (config("sirgrimorum.crudgenerator.css_section") != "")
    @stack(config("sirgrimorum.crudgenerator.css_section"))
    @endif

    <!-- ScriptsLoader -->
    @addScriptsLoader()

</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ route("sirgrimorum_home",App::getLocale()) }}">
                    {{ trans("crudgenerator::admin.layout.title") }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="{{ trans("crudgenerator::admin.layout.toggle_navigation") }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li><a class="nav-link"
                                href="{{ url(config("sirgrimorum.crudgenerator.home_path")) }}">{{ trans("crudgenerator::admin.layout.labels.home") }}</a>
                        </li>
                        @stack("menuobj")
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button"
                                aria-expanded="false"
                                aria-haspopup="true">{{trans('crudgenerator::admin.layout.labels.' . App::getLocale()) }}<span
                                    class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="navbarDropdown">
                                @foreach(config("sirgrimorum.crudgenerator.list_locales") as $localeCode)
                                <li>
                                    <a class="dropdown-item" rel="alternate" hreflang="{{$localeCode}}"
                                        href="{{CrudGenerator::changeLocale($localeCode) }}">
                                        {{{ trans('crudgenerator::admin.layout.labels.'.$localeCode) }}}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        <!-- Authentication Links -->
                        @guest
                        <li><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                        @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                    Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Contenido de la pagina -->
        <main class="py-4">
            <div class="container-fluid">
                <div class="row flex-xl-nowrap">
                    <div class="col-md-2 col-xl-2 bd-sidebar" id="sidebar">
                        <div class="bd-search d-flex align-items-center d-md-none ">
                            <button class="btn bd-search-docs-toggle d-md-none p-0 ml-3 collapsed" type="button"
                                data-toggle="collapse" data-target="#bd-docs-nav" aria-controls="bd-docs-nav"
                                aria-expanded="false" aria-label="Toggle docs navigation"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"
                                    role="img" focusable="false">
                                    <title>Menu</title>
                                    <path stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10"
                                        stroke-width="2" d="M4 7h22M4 15h22M4 23h22"></path>
                                </svg></button>
                        </div>
                        <ul class="nav nav-pills flex-column flex-nowrap overflow-hidden bd-links collapse"
                            id="bd-docs-nav">
                            @foreach(config("sirgrimorum.crudgenerator.admin_routes") as $modelo => $config)
                            <?php
                                if (Lang::has("sirgrimorum_cms::" . strtolower($modelo) . ".labels.plural")) {
                                    $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
                                } else {
                                    $plurales = ucfirst($modelo) . 's';
                                }
                                ?>
                            <li class="nav-item bd-toc-item {{ (isset($modeloActual) && $modeloActual == $modelo)?"active" : "" }}">
                                <a class="bd-toc-link nav-link {{ (isset($modeloActual) && $modeloActual == $modelo)?"active" : "" }} text-truncate"
                                    href="{{ route('sirgrimorum_modelos::index',['modelo'=>strtolower($modelo)]) }}">
                                    <span>{{ $plurales }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-9 col-xl-8 py-md-3 pl-md-2 bd-content">
                        @if (session(config("sirgrimorum.crudgenerator.status_messages_key")))
                        <!--div class="container">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
                                {!! session(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
                            </div>
                        </div-->
                        @endif
                        @if (session(config("sirgrimorum.crudgenerator.error_messages_key")))
                        <div class="container">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span
                                        aria-hidden="true">&times;</span></button>
                                {!! session(config("sirgrimorum.crudgenerator.error_messages_key")) !!}
                            </div>
                        </div>
                        @endif
                        @yield("contenido")
                    </div>
                </div>
            </div>

        </main>
        <footer>
            <div class="bg-dark text-white">
                <div class="container cont_footer">
                    {{ request()->path()}}
                    @section("piedepagina")
                    {{Route::current()->getName()}}
                    @show
                </div>
            </div>
        </footer>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    @if (config("sirgrimorum.crudgenerator.js_section") != "")
    @stack(config("sirgrimorum.crudgenerator.js_section"))
    @endif
    @if (config("sirgrimorum.crudgenerator.modal_section") != "")
    @stack(config("sirgrimorum.crudgenerator.modal_section"))
    @endif
</body>

</html>
