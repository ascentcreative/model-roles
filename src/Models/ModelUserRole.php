<?php

namespace AscentCreative\ModelRoles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use AscentCreative\ModelRoles\Events\ModelUserRoleEvent;

class ModelUserRole extends Model
{

    use HasFactory, \Staudenmeir\EloquentHasManyDeep\HasTableAlias;

    protected $table = 'model_user_roles';
    protected $fillable = ['model_type', 'model_id', 'user_id', 'role'];

    // Boot events etc
   public static function booted() {

        static::created(function($model) {
            // fire event
            ModelUserRoleEvent::dispatch($model, ModelUserRoleEvent::MODELUSERROLE_GRANTED);
        });

        // static::deleted(function($model) {
        //     ModelUserRoleEvent::dispatch($model, ModelUserRoleEvent::MODELUSERROLE_REVOKED);
        // });

    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function model() {
        return $this->morphTo();
    }

}
 