<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\LibraryDocument;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §3.11 - public items readable by anyone (no
 * permission, enforced by the public route only ever querying
 * visibility=public); internal items + write restricted to Super
 * Admin/Sekretaris/Multimedia/Editor.
 */
class LibraryDocumentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('library-documents.view');
    }

    public function view(User $user, ?LibraryDocument $document = null): bool
    {
        return $user->can('library-documents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('library-documents.create');
    }

    public function update(User $user, ?LibraryDocument $document = null): bool
    {
        return $user->can('library-documents.update');
    }

    public function delete(User $user, ?LibraryDocument $document = null): bool
    {
        return $user->can('library-documents.delete');
    }

    public function restore(User $user, ?LibraryDocument $document = null): bool
    {
        return $user->can('library-documents.restore');
    }
}
