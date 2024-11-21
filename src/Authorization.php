<?php

declare(strict_types=1);

namespace Yard\Brave\Hooks;

use Yard\Hook\Action;
use Yard\Hook\Filter;

class Authorization
{
    #[Filter('map_meta_cap', 1)]
    public function allowUnfilteredHtml(array $caps, string $cap, int $userId): array
    {
        if (! is_multisite()) {
            return $caps;
        }

        if ('unfiltered_html' === $cap && (user_can($userId, 'yard_unfiltered_html_multisite'))) {
            $caps = ['unfiltered_html'];
        }

        return $caps;
    }

    #[Filter('show_admin_bar')]
    public function hideAdminBar(bool $show): bool
    {
        if (current_user_can('administrator')) {
            return $show;
        }

        if (current_user_can('yard_hide_admin_bar')) {
            return false;
        }

        return $show;
    }

    #[Action('wp_login')]
    public function redirectHomeAfterLogin(string $userLogin, \WP_User $user): void
    {
        if (user_can($user, 'administrator')) {
            return;
        }

        if (user_can($user, 'yard_redirect_home_after_login')) {
            wp_redirect(home_url());
            exit;
        }
    }

    #[Action('admin_init')]
    public function preventAdminAccess(): void
    {
        if (current_user_can('administrator')) {
            return;
        }

        if (current_user_can('yard_prevent_admin_access')) {
            wp_logout();
            wp_redirect(admin_url());
            exit;
        }
    }
}
