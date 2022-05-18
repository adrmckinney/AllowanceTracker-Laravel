<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $timestamps = true;

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'transactionable');
    }

    public function chores(): MorphToMany
    {
        return $this->morphedByMany(Chore::class, 'transactionable');
    }
}
