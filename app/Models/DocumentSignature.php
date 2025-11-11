<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'signature_type',
        'signature_data',
        'ip_address',
        'user_agent',
        'signed_at',
        'verification_hash',
        'is_verified',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate verification hash
     */
    public function generateVerificationHash(): string
    {
        return hash('sha256', $this->document_id . $this->user_id . $this->signed_at . config('app.key'));
    }

    /**
     * Verify signature
     */
    public function verify(): bool
    {
        $expectedHash = $this->generateVerificationHash();
        return $this->verification_hash === $expectedHash;
    }
}
