<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SellerDocument extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'store_id',
        'ktp_status',
        'npwp_status',
        'nib_status',
        'submission_status',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function registerMediaCollections(): void
    {
        // Required documents
        $this->addMediaCollection('ktp')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('npwp')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('nib')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('company_statement')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        // Optional supporting documents (multiple)
        $this->addMediaCollection('supporting_documents')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }

    public function getKtpUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('ktp');
        return $media ? '/storage/' . $media->id . '/' . $media->file_name : null;
    }

    public function getNpwpUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('npwp');
        return $media ? '/storage/' . $media->id . '/' . $media->file_name : null;
    }

    public function getNibUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('nib');
        return $media ? '/storage/' . $media->id . '/' . $media->file_name : null;
    }

    public function getCompanyStatementUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('company_statement');
        return $media ? '/storage/' . $media->id . '/' . $media->file_name : null;
    }

    public function getSupportingDocumentsUrlsAttribute(): array
    {
        return $this->getMedia('supporting_documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => '/storage/' . $media->id . '/' . $media->file_name,
            ];
        })->toArray();
    }

    public function isComplete(): bool
    {
        return $this->getFirstMedia('ktp')
            && $this->getFirstMedia('npwp')
            && $this->getFirstMedia('nib')
            && $this->store()->value('name')
            && $this->store()->value('type');
    }

    public function isPending(): bool
    {
        return $this->submission_status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->submission_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->submission_status === 'rejected';
    }
}
