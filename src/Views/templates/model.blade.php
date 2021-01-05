{?php}

namespace {!! $config['nameSpace'] !!};

@if(strtolower($config["model"])=="user")
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Sirgrimorum\CrudGenerator\Traits\CrudGenForModels;
@else
use Illuminate\Database\Eloquent\Model;
use Sirgrimorum\CrudGenerator\Traits\CrudGenForModels;
@endif

@if(strtolower($config["model"])=="user")
class {Model} extends Authenticatable implements MustVerifyEmail{
@else
class {Model} extends Model {
@endif

    use CrudGenForModels;
    @if(strtolower($config['model'])=='user')
    use Notifiable;
    
    /**
     * The attributes that are mass assignable.
     *
     * {{"@"}}var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * {{"@"}}var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    @else
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    //protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * {{"@"}}var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    @endif
    
    //The validation rules
    public $rules = [
    ];
    
    //The validation error messages
    public $error_messages = [
    ]; 

    //For serialization
    protected $with = [
    @if (isset($config['hasmany']))
    @foreach ($config['hasmany'] as $relacion)
        //'{{$relacion['cliente']}}',
    @endforeach
    @endif
    @if (isset($config['belongsto']))
    @foreach ($config['belongsto'] as $relacion)
        //'{{$relacion['patron_model_name_single']}}',
    @endforeach
    @endif
    @if (isset($config['manytomany']))
    @foreach ($config['manytomany'] as $relacion)
        //'{{$relacion['otro']}}',
    @endforeach
    @endif
    ];
    
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
    
    @include('sirgrimorum::templates.getfunction')
}
