<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'guardians';

    protected $fillable = [
        'name',
        'whatsapp',
        'email',
        'status',
        'data_authorization',
        'authorization_date',
        'political_version',
    ];

    protected $casts = [
        'data_authorization' => 'boolean',
        'authorization_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($guardian) {
            if (!$guardian->isForceDeleting()) {
                $guardian->children()->delete();
            }
        });

        static::restoring(function ($guardian) {
            $guardian->children()->restore();
        });
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class, 'guardian_id');
    }
}
