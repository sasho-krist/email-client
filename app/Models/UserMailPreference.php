<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMailPreference extends Model
{
    protected $fillable = [
        'user_id',
        'inbox_group_by',
        'reply_include_quote',
        'reply_top_posting',
    ];

    protected function casts(): array
    {
        return [
            'reply_include_quote' => 'boolean',
            'reply_top_posting' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
