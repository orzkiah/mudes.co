<?php

declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\Models\Article;
use App\Domain\Models\User;

/**
 * PROJECT_SPECIFICATION.md §3.9 - view all + public (published only); write
 * Editor/Humas/Super Admin; publish approval Ketua/Super Admin
 * (BACKEND_ARCHITECTURE.md §11 - permission-based, one enforcement path).
 */
class ArticlePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('articles.view');
    }

    public function view(User $user, ?Article $article = null): bool
    {
        return $user->can('articles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, ?Article $article = null): bool
    {
        return $user->can('articles.update');
    }

    public function delete(User $user, ?Article $article = null): bool
    {
        return $user->can('articles.delete');
    }

    public function restore(User $user, ?Article $article = null): bool
    {
        return $user->can('articles.restore');
    }
}
