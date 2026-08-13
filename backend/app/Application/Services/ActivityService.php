<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\Activity;
use App\Infrastructure\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityService extends AbstractContentService
{
    public function __construct(private readonly ActivityRepositoryInterface $activityRepository)
    {
        parent::__construct($activityRepository);
    }

    public function findBySlug(string $slug): ?Activity
    {
        return $this->activityRepository->findBySlug($slug);
    }
}
