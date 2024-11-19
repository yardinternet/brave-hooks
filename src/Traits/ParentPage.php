<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks\Traits;

trait ParentPage
{
    private function getParentIds(int $postID): array
    {
        $postType = get_post_type($postID);
        $parentIds = [];

        if (is_post_type_hierarchical($postType) && has_post_parent($postID)) {
            $parentIds = get_post_ancestors($postID);
        } elseif ($this->hasParentPage($postID)) {
            $parentIds = $this->getParentPagesIds($postID);
        }

        return $parentIds;
    }

    private function hasParentPage(int|bool $postId): bool
    {
        return post_type_supports(get_post_type($postId), 'parent-page');
    }

    private function getParentPagesIds(int $postId): array
    {
        if (! $this->hasParentPage($postId)) {
            return [];
        }

        $parentPageSlug = get_all_post_type_supports(get_post_type($postId))['parent-page'][0]['slug'] ?? null;
        $parent = $parentPageSlug ? get_page_by_path($parentPageSlug) : null;
        if (! $parent) {
            return [];
        }
        $ancestors = get_post_ancestors($parent->ID);

        return [$parent->ID, ...$ancestors];
    }
}
