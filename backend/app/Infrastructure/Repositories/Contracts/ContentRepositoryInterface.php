<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared contract for every Standard CRUD Contract content module
 * (API_SPECIFICATION.md §8.1) that needs soft-delete restore - Activities,
 * Articles, Galleries, Digital Library, Announcements. Each concrete
 * module's own interface extends this and narrows find/findOrFail to its
 * own Model.
 */
interface ContentRepositoryInterface extends RepositoryInterface
{
    public function findTrashedOrFail(string $id): Model;
}
