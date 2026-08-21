<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'childs';

    protected $fillable = [
        'name',
        'age_range',
        'gender',
        'message',
        'guardian_id',
    ];

    protected $casts = [
        'guardian_id' => 'integer',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }
}
