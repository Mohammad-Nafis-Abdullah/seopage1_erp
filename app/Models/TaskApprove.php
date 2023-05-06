<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskApprove extends Model
{
    use HasFactory;
    protected $table = 'task_approves';

    public function task()
    {
        return $this->belongsTo(task::class, 'task_id');
    }

}
