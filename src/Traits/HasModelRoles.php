<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

// Trait to apply to the User model
// Gives access to shortcut methods to check if a user has roles against models.
trait HasModelRoles {

    public function hasModelRole($roles, $model) {

        if(!is_array($roles)) {
            $roles = [$roles];
        }

        $q = $this->modelRoles()
                    ->where('model_type', get_class($model))
                    ->where('model_id', $model->id);

        if($roles[0] != '*') { 
            // If roles is *, we don't need this - just search for the existence of any link.
            // However, if anything else, search for it.
            $q->whereIn('role', $roles);
        }
        
        return $q->exists();   
    }

    

    public function modelRoles() {
        return $this->hasMany(ModelUserRole::class);
    }

    public function grantModelRole($model, $role) {

        $mr = ModelUserRole::updateOrCreate([
            'user_id'=>$this->id,
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ], [
            'role'=>$role,
        ]);

    }


}