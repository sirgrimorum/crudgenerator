{?php}

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Sirgrimorum\CrudGenerator\CrudGenerator;
use {{$config['modelo']}};

class {Model}Request extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
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
                $config = CrudGenerator::getConfig('{model}');
                if (isset($config['rules'])){
                    return $config['rules'];
                }else{
                    return [];
                }
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
        $config = CrudGenerator::getConfig('{model}');
        $error_messages = [];
            if (isset($config['error_messages'])) {
                if (is_array($config['error_messages'])) {
                    $error_messages = $config['error_messages'];
                }
            }
            if (count($error_messages) == 0) {
                $objModelo = new $config['modelo'];
                if (isset($objModelo->error_messages)) {
                    if (is_array($objModelo->error_messages)) {
                        $error_messages = $objModelo->error_messages;
                    }
                }
            }
            $error_messages = array_merge(trans("crudgenerator::admin.error_messages"), $error_messages);
        return $error_messages;
    }
    
    /**
     * Get the custom attributes names for the defined validation rules.
     *
     * @return array
     */
    public function attributes() {
        $config = CrudGenerator::getConfig('{model}');
        $customAttributes = [];
            foreach ($config['rules'] as $field => $datos) {
                if (array_has($config, "campos." . $field . ".label")) {
                    $customAttributes[$field] = array_get($config, "campos." . $field . ".label");
                }
            }
        return $customAttributes;
    }

}
