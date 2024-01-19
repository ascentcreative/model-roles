<?php

namespace AscentCreative\ModelRoles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class ModelUserRole extends Model
{

    use HasFactory, \Staudenmeir\EloquentHasManyDeep\HasTableAlias;

    protected $table = 'model_user_roles';
    protected $fillable = ['model_type', 'model_id', 'user_id', 'role'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function model() {
        return $this->morphTo();
    }

}
 