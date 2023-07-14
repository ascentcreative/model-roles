<?php

namespace AscentCreative\ModelRoles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelUserRole extends Model
{

    use HasFactory;

    protected $table = 'model_user_roles';
    protected $fillable = ['model_type', 'model_id', 'user_id', 'role'];
    

}
 