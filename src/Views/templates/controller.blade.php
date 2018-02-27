{?php}

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\{Model}Request;
use App\User;
use {{$config['modelo']}};
use App\Repositories\{Model}Repository;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class {Model}Controller extends Controller {

    /**
     * The {model} repository instance.
     *
     * @var {Model}Repository
     */
    protected ${model}s;
    
    /**
     * The {model} name.
     *
     * @var {Model}Repository
     */
    protected $modelName = '{model}';
    
    /**
     * The {model} config array.
     *
     * @var {Model}Repository
     */
    protected $config;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct({Model}Repository ${model}s) {
        $this->middleware('auth');
        $this->{model}s = ${model}s;
        $this->config = CrudGenerator::getConfig($this->modelName);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function index($localecode, Request $request) {
    @else
    public function index(Request $request) {
    @endif
        $this->authorize('index', {Model}::class);
        return view('models.{model}.index', [
            'user' => $request->user(),
            'config'=>$this->config
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function create($localecode, Request $request) {
    @else
    public function create(Request $request) {
    @endif
        $this->authorize('create', {Model}::class);
        $config = $this->config;
        @if($localized)
        $config['url']= route('{model}.store',['localecode'=>$localecode]);
        @else
        $config['url']= route('{model}.store');
        @endif
        $config['botones']=trans("crudgenerator::{model}.labels.create");
        return view('models.{model}.create', [
            'user' => $request->user(),
            'config'=>$config
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function store($localecode, {Model}Request $request) {
    @else
    public function store({Model}Request $request) {
    @endif
        $this->authorize('store', {Model}::class);
        $objeto = CrudGenerator::saveObjeto($this->config, $request);
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        @if($localized)
        return redirect(route('{model}.index',\App::getLocale()))
        @else
        return redirect(route('{model}.index'))
        @endif
                ->with(config("sirgrimorum.crudgenerator.status_messages_key"),str_replace([":modelName", ":modelId"], [$objeto->{$this->config['nombre']}, $objeto->{$this->config['id']}], $mensajes["store_success"]) );
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\{Model}  ${model}
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function show($localecode, {Model} ${model}, Request $request) {
    @else
    public function show({Model} ${model}, Request $request) {
    @endif
        $this->authorize('show', ${model});
        return view('models.{model}.show', [
            'user' => $request->user(),
            '{model}' => ${model},
            'config' => $this->config
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\{Model}  ${model}
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function edit($localecode, {Model} ${model}, Request $request) {
    @else
    public function edit({Model} ${model}, Request $request) {
    @endif
        $this->authorize('edit', ${model});
        $config = $this->config;
        @if($localized)
        $config['url']= route('{model}.update',['localecode'=>$localecode,${model}->getKey()]);
        @else
        $config['url']= route('{model}.update',[${model}->getKey()]);
        @endif
        $config['botones']=trans("crudgenerator::{model}.labels.edit");
        return view('models.{model}.edit', [
            'user' => $request->user(),
            '{model}' => ${model},
            'config' => $config
        ]);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\{Model}  ${model}
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function update($localecode, {Model} ${model}, {Model}Request $request) {
    @else
    public function update({Model} ${model}, {Model}Request $request) {
    @endif
        $this->authorize('update', ${model});
        $objeto = CrudGenerator::saveObjeto($this->config, $request, ${model});
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        @if($localized)
        return redirect(route('{model}.index',\App::getLocale()))
        @else
        return redirect(route('{model}.index'))
        @endif
                ->with(config("sirgrimorum.crudgenerator.status_messages_key"), str_replace([":modelName", ":modelId"], [$objeto->{$this->config['nombre']}, $objeto->{$this->config['id']}], $mensajes["update_success"]) );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\{Model}  ${model}
     * @return \Illuminate\Http\Response
     */
    @if($localized)
    public function destroy($localecode, {Model} ${model}, {Model}Request $request){
    @else
    public function destroy({Model} ${model}, {Model}Request $request){
    @endif
        $this->authorize('destroy',${model});
        $datos = [
            'id' => ${model}->{$this->config['id']},
            'nombre' => ${model}->{$this->config['nombre']}
        ];
        ${model}->delete();
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        @if($localized)
        return redirect(route('{model}.index',\App::getLocale()))
        @else
        return redirect(route('{model}.index'))
        @endif
                ->with(config("sirgrimorum.crudgenerator.status_messages_key"),str_replace([":modelName", ":modelId"], [$datos['nombre'], $datos['id'] ], $mensajes["destroy_success"]) );
    }

}
