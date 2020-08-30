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
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function index(?User $user){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config);
    }
    
    public function create(?User $user){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config);
    }
    
    public function store(?User $user){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config);
    }
    
    public function show(?User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config, ${model}->getKey());
    }
    
    public function edit(?User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config, ${model}->getKey());
    }
    
    public function update(?User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config, ${model}->getKey());
    }
    
    public function destroy(?User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission($config, ${model}->getKey());
    }
    
}
