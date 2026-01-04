<?php

namespace Modules\Branch\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{

public function apply(Builder $builder, Model $model)
{


    $builder->where($model->getTable() . '.branch_id', current_guard_user()->branch_id);

    

}  



}
