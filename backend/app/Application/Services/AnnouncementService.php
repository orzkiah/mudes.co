<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Repositories\Contracts\AnnouncementRepositoryInterface;

class AnnouncementService extends AbstractContentService
{
    public function __construct(AnnouncementRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
