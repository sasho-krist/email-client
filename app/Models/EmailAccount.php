<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAccount extends Model
{
    protected $fillable = [
        'user_id',
        'profile_name',
        'account_color',
        'email',
        'mailbox_password',
        'display_name',
        'reply_to',
        'organization',
        'imap_host',
        'imap_port',
        'imap_security',
        'imap_auth',
        'smtp_host',
        'smtp_port',
        'smtp_security',
        'smtp_auth',
        'check_on_startup',
        'check_interval_minutes',
        'use_idle',
        'delete_behavior',
        'folder_inbox',
        'folder_sent',
        'folder_spam',
        'folder_trash',
        'signature_html',
        'signature_use_html',
    ];

    protected function casts(): array
    {
        return [
            'mailbox_password' => 'encrypted',
            'check_on_startup' => 'boolean',
            'check_interval_minutes' => 'integer',
            'use_idle' => 'boolean',
            'signature_use_html' => 'boolean',
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return $this->profile_name ?: $this->email;
    }
}
