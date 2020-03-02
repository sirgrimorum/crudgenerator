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
     * @var {Model}[]
     */
    protected ${{str_plural($modelo)}};
    
    /**
     * The {model} name.
     *
     * @var string
     */
    protected $modelName = '{model}';
    
    /**
     * The {model} config array.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct({Model}Repository ${{str_plural($modelo)}}) {
        $this->middleware('auth');
        $this->{{str_plural($modelo)}} = ${{str_plural($modelo)}};
        $this->config = CrudGenerator::getConfig($this->modelName);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $this->authorize('index', {Model}::class);
        return view('models.{model}.index', [
            'user' => $request->user(),
            'config'=>$this->config
        ]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        $this->authorize('create', {Model}::class);
        $config = $this->config;
        $config['url']= route('{model}.store');
        $config['botones']=trans("crudgenerator::{model}.labels.create");
        return view('models.{model}.create', [
            'user' => $request->user(),
            'config'=>$config
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\{Model}Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store({Model}Request $request) {
        $this->authorize('store', {Model}::class);
        $objeto = CrudGenerator::saveObjeto($this->config, $request);
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        if ($objeto){
            $mensaje = str_replace([":modelName", ":modelId"], [$objeto->{$this->config['nombre']}, $objeto->{$this->config['id']}], $mensajes["store_success"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensaje);
        }else{
            $mensaje = str_replace([":modelName", ":modelId"], [$request->get($this->config['nombre'],"-"), 0], $mensajes["store_error"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.error_messages_key"), $mensaje);
        }
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\{Model}  ${model}
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show({Model} ${model}, Request $request) {
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit({Model} ${model}, Request $request) {
        $this->authorize('edit', ${model});
        $config = $this->config;
        $config['url']= route('{model}.update',[${model}->getKey()]);
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
     * @param  \App\Http\Requests\{Model}Request  $request
     * @param  \App\{Model}  ${model}
     * @return \Illuminate\Http\Response
     */
    public function update({Model} ${model}, {Model}Request $request) {
        $this->authorize('update', ${model});
        $objeto = CrudGenerator::saveObjeto($this->config, $request, ${model});
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        if ($objeto){
            $mensaje = str_replace([":modelName", ":modelId"], [$objeto->{$this->config['nombre']}, $objeto->{$this->config['id']}], $mensajes["update_success"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensaje);
        }else{
            $mensaje = str_replace([":modelName", ":modelId"], [$objeto->{$this->config['nombre']}, $objeto->{$this->config['id']}], $mensajes["update_error"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.error_messages_key"), $mensaje);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\{Model}  ${model}
     * @param  \App\Http\Requests\{Model}Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy({Model} ${model}, {Model}Request $request){
        $this->authorize('destroy',${model});
        $datos = [
            'id' => ${model}->{$this->config['id']},
            'nombre' => ${model}->{$this->config['nombre']}
        ];
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::{model}.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::{model}.messages"));
        }
        if (${model}->delete()){
            $mensaje = str_replace([":modelName", ":modelId"], [$datos['nombre'], $datos['id'] ], $mensajes["destroy_success"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensaje);
        }else{
            $mensaje = str_replace([":modelName", ":modelId"], [$datos['nombre'], $datos['id'] ], $mensajes["destroy_error"]);
            return redirect(route('{model}.index'))->with(config("sirgrimorum.crudgenerator.error_messages_key"), $mensaje);
        }
    }

}
