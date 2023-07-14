<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

// Trait to apply to the target Model
// Gives access to shortcut methods to see which users have roles etc
trait HasRoleUsers {

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

    public function modelRoles() {
        return $this->morphMany(ModelUserRole::class, 'model');
    }


}