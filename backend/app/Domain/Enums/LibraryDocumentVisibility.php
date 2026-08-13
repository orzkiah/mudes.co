<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * DATABASE_SPECIFICATION.md §5 - library_documents.visibility.
 */
enum LibraryDocumentVisibility: string
{
    case PublicVisibility = 'public';
    case Internal = 'internal';
}
