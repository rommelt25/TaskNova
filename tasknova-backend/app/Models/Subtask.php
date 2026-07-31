<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    use HasFactory;

    protected $attributes = [
        'completed' => false,
        'position' => 0,
    ];

    protected $fillable = [
        'title',
        'description',
        'completed',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
