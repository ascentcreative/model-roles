<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;


trait HasModelRoles {

    public function hasModelRole($role, $model) {
        return $this->modelRoles()
                    ->where('model_type', get_class($model))
                    ->where('model_id', $model->id)
                    ->where('role', $role)
                    ->exists();
    }

    public function modelRoles() {
        return $this->hasMany(ModelUserRole::class);
    }


}