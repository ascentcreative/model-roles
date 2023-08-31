<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

use Staudenmeir\EloquentHasManyDeep\HasRelationships; 
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

use App\Models\User;

// Trait to apply to the target Model
// Gives access to shortcut methods to see which users have roles etc
trait HasRoleUsers {

    use HasRelationships;

    public function hasRoleUsers($roles) {

        return $this->roleUsers($roles)
                    ->exists();
        
    }

    public function getRoleUsers($roles) {
        return $this->roleUsers($roles)->get();
    }

    public function roleUsers($roles) {

        if(!is_array($roles)) {
            $roles = [$roles];
        }

        return $this->modelRoles()
                    ->where('model_type', get_class($this))
                    ->where('model_id', $this->id)
                    ->whereIn('role', $roles);
    }

    public function users() {
        return $this->hasManyDeep(
            User::class,
            [ModelUserRole::class],
            [
                ['model_type', 'model_id'],
                'id'
            ],
            [
                null,
                'user_id'
            ]

        );
    }

    public function modelRoles() {
        return $this->morphMany(ModelUserRole::class, 'model');
    }

    public function grantUserRole($user, $role) {

        if(is_numeric($user)) {
            $user_id = $user;
        } else {
            $user_id = $user->id;
        }

        $mr = ModelUserRole::updateOrCreate([
            'user_id'=>$user_id,
            'model_type' => get_class($this),
            'model_id' => $this->id,
        ], [
            'role'=>$role,
        ]);

    }


}