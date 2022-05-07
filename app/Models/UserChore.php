<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChore extends Model
{
    use HasFactory;

    protected $table = 'user_chore';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class, 'chore_id', 'id');
    }
}
