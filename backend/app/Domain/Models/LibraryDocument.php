<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\LibraryDocumentVisibility;
use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\LibraryDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.20.
 */
#[ObservedBy(AuditObserver::class)]
class LibraryDocument extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'library_category_id',
        'title',
        'description',
        'file_media_id',
        'external_url',
        'visibility',
        'download_count',
    ];

    protected static function newFactory(): LibraryDocumentFactory
    {
        return LibraryDocumentFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => LibraryDocumentVisibility::class,
            'download_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class, 'library_category_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'file_media_id');
    }
}
