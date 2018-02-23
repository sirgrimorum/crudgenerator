{?php}

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use App\User;
use {{$config['modelo']}};
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
    
    public function index(User $user){
        //return ${model}->user_id == $user->id;
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('index', $config);
    }
    
    public function create(User $user){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('create', $config);
    }
    
    public function store(User $user){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('store', $config);
    }
    
    public function show(User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('show', $config, ${model}->getKey());
    }
    
    public function edit(User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('edit', $config, ${model}->getKey());
    }
    
    public function update(User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('update', $config, ${model}->getKey());
    }
    
    public function destroy(User $user, {Model} ${model}){
        $config = CrudGenerator::getConfig('{model}');
        return CrudGenerator::checkPermission('destroy', $config, ${model}->getKey());
    }
    
}
