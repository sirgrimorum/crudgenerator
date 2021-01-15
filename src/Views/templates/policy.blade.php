{?php}

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\User;
@if ($modelo != "user")
use {{$config['modelo']}};
@endif
use Sirgrimorum\CrudGenerator\CrudGenerator;

class {Model}Policy
{
    use HandlesAuthorization;

    /**
     * The configuration array for the model.
     *
     * @var array
     */
    private $config;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config = CrudGenerator::getConfig('{model}');
    }
    
    public function index(?User $user){
        return CrudGenerator::checkPermission($this->config);
    }
    
    public function create(?User $user){
        return CrudGenerator::checkPermission($this->config);
    }
    
    public function store(?User $user){
        return CrudGenerator::checkPermission($this->config);
    }
    
    public function show(?User $user, {Model} ${model}){
        return CrudGenerator::checkPermission($this->config, ${model}->getKey());
    }
    
    public function edit(?User $user, {Model} ${model}){
        return CrudGenerator::checkPermission($this->config, ${model}->getKey());
    }
    
    public function update(?User $user, {Model} ${model}){
        return CrudGenerator::checkPermission($this->config, ${model}->getKey());
    }
    
    public function destroy(?User $user, {Model} ${model}){
        return CrudGenerator::checkPermission($this->config, ${model}->getKey());
    }
    
}
