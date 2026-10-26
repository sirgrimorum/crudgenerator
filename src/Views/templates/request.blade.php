{?php}

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use {{$config['modelo']}};

class {Model}Request extends FormRequest {

    /**
     * The configuration array for the model.
     *
     * @var array
     */
    private $config;

    /**
     * The array of rules.
     *
     * @var array
     */
    private $rules;

    /**
     * The array of custom error messages.
     *
     * @var array
     */
    private $error_messages;

    /**
     * The array of custom attributes names.
     *
     * @var array
     */
    private $customAttributes;

    /**
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->config = CrudGenerator::getConfigWithParametros('{model}');
        [$this->rules, $this->error_messages, $this->customAttributes] = CrudGenerator::getRulesWithRelationShips($this->config);
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true; // The authorization is managed in the Policy created by CrudGenerator
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        switch($this->route()->getName()){
            case "{model}.store":
            case "{model}.update":
                return $this->rules;
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages() {
        return $this->error_messages;
    }
    
    /**
     * Get the custom attributes names for the defined validation rules.
     *
     * @return array
     */
    public function attributes() {
        return $this->customAttributes;
    }

}
