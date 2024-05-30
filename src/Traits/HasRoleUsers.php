<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

use Staudenmeir\EloquentHasManyDeep\HasRelationships; 
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

use Illuminate\Support\Facades\Cache;

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

        if(!is_array($roles)) {
            $roles = [$roles];
        }

        // cache key:
        $key = 'roleusers_' . get_class($this) . '_' . $this->id . '_' . join('_', $roles);
        // dump($key);

        // Cache::forget($key);

        $check = Cache::remember($key, 10, function() use ($roles) {
            // dump('getting users for ' . join(', ', $roles));
            return $this->roleUsers($roles)->get();
        });

        return $check; //$q->exists();   
       
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


    public function users($roles=null) {
        $q = $this->hasManyDeep(
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

        if(!is_null($roles)) {

            if(!is_array($roles)) {
                $roles = [$roles];
            }

            $q->whereIn('model_user_roles.role', $roles);
        }

        return $q;
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

    public function revokeUserRole($user, $role) {

        if(is_numeric($user)) {
            $user_id = $user;
        } else {
            $user_id = $user->id;
        }

        $mr = ModelUserRole::where('user_id', $user_id)
                        ->where('model_type', get_class($this))
                        ->where('model_id', $this->id)
                        ->where('role', $role)->first();

        $mr->delete();

    }


    public function getUserRole(User $user = null) {

        if(!$user) {
            $user = auth()->user();
        }
        
        $modelRole = $this->modelRoles()->where('user_id', $user->id)->first();

        if($modelRole) {
            return $modelRole->role;
        }

        return null;

    }


    public function syncRoleUsers($role, $users) {

        // get all the stored users for the given role:
        $stored = $this->users($role)->get()->pluck('id')->toArray();

        foreach($users as $user) {

            if(is_numeric($user)) {
                $user_id = $user;
            } else {
                $user_id = $user->id;
            }

            $idx = \array_search($user_id, $stored);
            if($idx === false) {
                $this->grantUserRole($user_id, $role);
            } else {
                unset($stored[$idx]);
            }
        }
        
        // anything remaining in stored should now be removed:
        foreach($stored as $revoke_id) {
            $this->revokeUserRole($revoke_id, $role);
        }
    
    }


}