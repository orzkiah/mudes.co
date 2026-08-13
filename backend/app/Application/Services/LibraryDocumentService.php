<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Models\LibraryDocument;
use App\Infrastructure\Repositories\Contracts\LibraryDocumentRepositoryInterface;

class LibraryDocumentService extends AbstractContentService
{
    public function __construct(LibraryDocumentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function find(string $id): LibraryDocument
    {
        /** @var LibraryDocument */
        return parent::find($id);
    }

    public function incrementDownloadCount(LibraryDocument $document): void
    {
        $document->increment('download_count');
    }
}
