<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Chore extends Model
{
    use HasFactory;


    public $timestamps = true;
    protected $guarded = ['id', 'created_at', 'updated_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'cost',
        'user_id',
        'approval_requested',
        'approval_request_date',
        'approval_status',
        'approval_date',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserChore::class, 'user_id');
    }

    public function transactions(): MorphToMany
    {
        return $this->morphToMany(Transaction::class, 'transactionable');
    }
}
