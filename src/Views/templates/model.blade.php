{?php}

namespace {!! $config['nameSpace'] !!};

use Illuminate\Database\Eloquent\Model;

class {Model} extends Model {

    public $rules = [//The validation rules
    ];
    public $error_messages = []; //The validation error messages

    public function _construct() {
        $this->error_messages = [
        ];
    }
    
    @if (isset($config['hasmany']))
    @foreach ($config['hasmany'] as $relacion)
    public function {{$relacion['cliente']}}() {
        return $this->hasMany('{{$relacion['cliente_model']}}','{{$relacion['cliente_col']}}','{{$relacion['patron_col']}}');
    }
    @endforeach
    @endif
    
    @if (isset($config['belongsto']))
    @foreach ($config['belongsto'] as $relacion)
    public function {{$relacion['patron_model_name_single']}}() {
        return $this->belongsTo('{{$relacion['patron_model']}}','{{$relacion['cliente_col']}}','{{$relacion['patron_col']}}');
    }
    @endforeach
    @endif
    
    @if (isset($config['manytomany']))
    @foreach ($config['manytomany'] as $relacion)
    <?php
    $pivot = "";
    $prefijo = "->withPivot(";
    foreach($relacion['pivotColumns'] as $pivotColumn => $datos){
        $pivot .= $prefijo . "'$pivotColumn'";
        $prefijo = ", ";
    }
    if ($pivot!=""){
        $pivot.=")";
    }
    ?>
    public function {{$relacion['otro']}}() {
        return $this->belongsToMany('{{$relacion['otro_model']}}','{{$relacion['intermedia']}}','{{$relacion['col_intermediaMia']}}','{{$relacion['col_intermediaOtro']}}'){!! $pivot !!};
    }
    @endforeach
    @endif
}
