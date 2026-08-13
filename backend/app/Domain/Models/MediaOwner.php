<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Lightweight model that acts as the Spatie Media Library owner for
 * standalone uploads (POST /admin/media).
 *
 * Context and rationale
 * ─────────────────────
 * Every entity in this project stores media references via a plain FK column
 * (e.g. `cover_media_id`, `photo_media_id`) rather than the Spatie polymorphic
 * relationship. The FK points to a row in the `media` table created by a prior
 * upload. That upload needs *some* model that implements HasMedia so Spatie can
 * run its pipeline (MIME sniffing, conversions, path generation) and persist
 * the row.
 *
 * This model fills that role. It has no backing table of its own — the `id`
 * generated here is never stored anywhere; it is only used as the polymorphic
 * `model_id` in the `media` row. Once the frontend receives the media UUID from
 * the upload response and attaches it to an entity (e.g. Member.photo_media_id),
 * the `model_type`/`model_id` columns on the media row become a secondary index
 * rather than a canonical FK — intentionally, per DATABASE_SPECIFICATION.md §2.8
 * which already notes this table uses a non-enforced polymorphic relationship.
 *
 * Collections and conversions are defined here so Spatie's pipeline can apply
 * them consistently regardless of which entity will eventually reference the
 * uploaded file.
 */
class MediaOwner extends Model implements HasMedia
{
    use HasUuid;
    use InteractsWithMedia;

    /**
     * This model has no backing table — no queries are issued against it.
     * The property must be set to avoid Eloquent trying to resolve a table name.
     */
    protected $table = 'media_owners';

    /**
     * We set $exists = true so Spatie's FileAdder treats this as a
     * "already persisted" owner and uses the proper $model->media()->save($media)
     * path — which correctly populates model_type and model_id on the media row.
     *
     * The UUID id is pre-assigned in MediaController before calling addMedia(),
     * so Spatie's PathGenerator gets a non-null getKey() value for the media path.
     *
     * save() is intentionally overridden to be a no-op — this model has no
     * backing table and must never attempt a database write for itself.
     */
    public $exists = true;

    /**
     * No-op: MediaOwner has no backing table and must never attempt a
     * database write for itself. Returning true satisfies callers that
     * check the return value of save().
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        return true;
    }

    public function registerMediaCollections(): void
    {
        // Images — single-file collections; replacing an existing file is handled
        // at the entity level (the old media row is simply orphaned or soft-deleted
        // separately; the upload endpoint always creates a fresh row).
        $this->addMediaCollection('member-photo')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('article-cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('activity-cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery-photo')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/bmp',
                'image/heic',
                'image/heif',
                'image/tiff',
                'image/pjpeg',
                'image/x-png',
                'video/mp4',
                'video/webm',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'video/mpeg',
                'video/3gpp',
                'video/3gpp2',
                'video/x-m4v',
                'video/avi',
                'video/msvideo',
            ]);

        // Library files — PDF, audio, and video.
        $this->addMediaCollection('library-file')
            ->acceptsMimeTypes([
                'application/pdf',
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/ogg',
                'video/mp4',
                'video/webm',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        if ($media && str_starts_with($media->mime_type ?? '', 'image/')) {
            $this->addMediaConversion('thumb')
                ->width(200)
                ->height(200)
                ->nonQueued();

            $this->addMediaConversion('medium')
                ->width(800)
                ->nonQueued();
        }
    }
}
