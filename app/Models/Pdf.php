<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
        'size_bytes',
        'content_type',
        'etag',
        'hash_sha256',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function getCdnUrlAttribute(): ?string
    {
        $base = config('cdn.base_url');
        return $base ? rtrim($base, '/') . '/' . ltrim($this->path, '/') : null;
    }

    public function getStorageUrlAttribute(): string
    {
        // Assuming Azure public container access for documents
        $account = config('azure.storage.account');
        return sprintf('https://%s.blob.core.windows.net/%s', $account, ltrim($this->path, '/'));
    }
}