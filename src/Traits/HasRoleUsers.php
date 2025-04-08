<?php

namespace AscentCreative\ModelRoles\Traits;

use AscentCreative\ModelRoles\Models\ModelUserRole;

use Staudenmeir\EloquentHasManyDeep\HasRelationships; 
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

use Illuminate\Support\Facades\Cache;

use App\Models\User;
use AscentCreative\CMS\Traits\Extender;

use Illuminate\Support\Str;

// Trait to apply to the target Model
// Gives access to shortcut methods to see which users have roles etc
trait HasRoleUsers {

    use HasRelationships, Extender;

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


    /**
     * Retired version of the relationship - realised morphToMany is better (has a sync())
     */
    // public function old_users($roles=null) {
    //     $q = $this->hasManyDeep(
    //         User::class,
    //         [ModelUserRole::class],
    //         [
    //             ['model_type', 'model_id'],
    //             'id'
    //         ],
    //         [
    //             null,
    //             'user_id'
    //         ]

    //     );

    //     if(!is_null($roles)) {

    //         if(!is_array($roles)) {
    //             $roles = [$roles];
    //         }

    //         $q->whereIn('model_user_roles.role', $roles);
    //     }

    //     return $q;
    // }


    /**
     * Updated version using morphToMany()
     */
    public function users($roles = null) {

        $q = $this->morphToMany(User::class, 'model', 'model_user_roles')
                        ->withTimestamps();

        if(!is_null($roles)) {

            if(!is_array($roles)) {
                $roles = [$roles];
            }
            $q->wherePivotIn('role', $roles);
            
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
            'role'=>$role,
        ], [
           
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

        if($mr)
            $mr->delete();

    }


    public function userRoles(User $user = null) {

        if(!$user) {
            $user = auth()->user();
        }

        return $this->modelRoles()->where('user_id', $user->id);

    }

    public function userRoleNames(User $user = null) {
        return $this->userRoles($user)->pluck('role');
    }

    public function toggleUserRole(User $user=null, string $role) {

        if(!$user) {
            $user = auth()->user();
        }

        // dd($this);
        // dump($this->userRoleNames($user)->search($role));

        if($this->userHasRole(null, $role)) {
            // dump('revoke');
            $this->revokeUserRole($user, $role);
            return false;
        } else {
            // dump('grant');
            $this->grantUserRole($user, $role);
            return true;
        }
    }

    public function userHasRole($user, $role) {

        if(!$user) {
            $user = auth()->user();
        }

        return $this->userRoleNames($user)->search($role) !== false;

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



    public function scopeUserHasRole($q, $role, $user=null) {

        if(!$user) {
            $user = auth()->user();
        }

        $q->whereHas('modelRoles', function($q) use ($user, $role) {
            $q->where('role', $role)
                ->where('user_id', $user->id);
        });

    }


    // Extender:
    public function initializeHasRoleUsers() {
        if(is_array($this->_modelRoles)) {
            foreach($this->_modelRoles as $role) {
                $this->addCapturable(Str::plural($role), false, 'saveRoleUsers');
            }
        }
    }


    public function saveRoleUsers($key, $value) {
        $this->$key()->sync($value ?? []);
    }


}