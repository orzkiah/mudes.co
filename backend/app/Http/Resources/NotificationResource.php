<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Models\Notification;
use Illuminate\Http\Request;

/**
 * @property Notification $resource
 */
class NotificationResource extends BaseApiResource
{
    /**
     * API-facing type translated from the internal Notification class name
     * (API_SPECIFICATION.md §9.21 - "not a 1:1 passthrough" so the internal
     * implementation can change without breaking clients).
     *
     * @var array<string, string>
     */
    private const TYPE_MAP = [
        'NewAnnouncement' => 'announcement',
        'AttendanceReminder' => 'attendance_reminder',
        'StudyReminder' => 'study_reminder',
        'ContentApproval' => 'content_approval',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Notification $notification */
        $notification = $this->resource;

        $shortClassName = class_basename($notification->type);

        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => self::TYPE_MAP[$shortClassName] ?? 'system',
            'data' => [
                'message' => $data['message'] ?? null,
                'resourceType' => $data['resourceType'] ?? null,
                'resourceId' => $data['resourceId'] ?? null,
                'actionUrl' => $data['actionUrl'] ?? null,
            ],
            'isRead' => $notification->read_at !== null,
            'readAt' => $notification->read_at?->toIso8601String(),
            'createdAt' => $notification->created_at?->toIso8601String(),
        ];
    }
}
