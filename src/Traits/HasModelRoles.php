<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

use Illuminate\Support\Facades\Cache;


// Trait to apply to the User model
// Gives access to shortcut methods to check if a user has roles against models.
trait HasModelRoles {


    public function hasModelRole($roles, $model) {

        if(!is_array($roles)) {
            $roles = [$roles];
        }

        // cache key:
        $key = 'hasmodelrole_' . $this->id . '_' . get_class($model) . '_' . $model->id . '_' . join('_', $roles);
    
        $check = Cache::remember($key, 10, function() use ($model, $roles) {
            // dump('storing');
            $q = $this->modelRoles()
                ->where('model_type', get_class($model))
                ->where('model_id', $model->id);

            if($roles[0] != '*') { 
                // If roles is *, we don't need this - just search for the existence of any link.
                // However, if anything else, search for it.
                $q->whereIn('role', $roles);
            }

            return $q->exists();
            // return 'a';
        });

        return $check; //$q->exists();   

    }

    public function modelRoles() {
        return $this->hasMany(ModelUserRole::class);
    }

    public function models($class, $roles=[]) {

        $q = $this->belongsToMany($class, ModelUserRole::class, 'user_id', 'model_id')
            ->where('model_type', $class);

        if (count($roles) > 0) {
            $q->whereIn('model_user_roles.role', $roles);
        }

        return $q;

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