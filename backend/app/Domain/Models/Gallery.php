<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Infrastructure\Observers\AuditObserver;
use App\Shared\Traits\HasUuid;
use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DATABASE_SPECIFICATION.md §4.17.
 */
#[ObservedBy(AuditObserver::class)]
class Gallery extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'gallery_category_id',
        'activity_id',
        'title',
        'description',
        'cover_photo_media_id',
    ];

    protected static function newFactory(): GalleryFactory
    {
        return GalleryFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_photo_media_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('display_order');
    }
}
