# CHANGELOG

## [v2.7.0] - 2026-06-10

-   Feat: translate non latin numerals
-   Chore: enable meta key registration for revisions

## [v2.6.1] - 2026-05-28

-   Fix: facetwp searchwp only highlight text nodes not html tags
-   Feat: enable gravity forms export for fields with multiple inputs

## [v2.6.0] - 2026-05-01

-   Feat: fallback to post type slug for parent page

## [v2.5.1] - 2026-04-07

-   Fix: change tag_name to class_name
-   Feat: add wp-block-group-column-count-\* class

## [v2.5.0] - 2026-04-03

-   Feat: strip new tab notice from excerpts / make new tab notice configurable
-   Feat: remove leading space from default notice
-   Feat: add early return if excerpt does not contain new tab notice
-   Feat: FacetWP collapse & expand html

## [v2.4.0] - 2026-03-30

-   Feat: add restrictBlocksForPostTypes to Gutenberg.php
-   Style: apply php-cs-fixer changes

## [v2.3.1] - 2026-03-02

-   Fix: add check for \_GET in DuplicatePost class

## [v2.3.0] - 2026-02-06

-   Feat: fix for duplicate post plugin
-   Chore: add new hook to config

## [v2.2.0] - 2026-01-21

-   Feat: add seopress menu options

## [v2.1.4] - 2025-12-19

-   A11y: wrap facetwp pager in nav element

## [v2.1.3] - 2025-12-15

-   Style: always enable gravityform default css

## [v2.1.2] - 2025-11-26

-   Fix: get_current_screen() function may not have been loaded here yet

## [v2.1.1] - 2025-11-10

-   Feat: limit proximity facet autocomplete to display the Netherlands as region

## [v2.0.1] - 2025-10-23

-   Fix: hooks registration

## [v2.0.0] - 2025-10-23

-   Feat: introduce plugin-specific hook loading via attributes and configuration
-   Fix: use correct filter to redirect users after login

## [v1.2.2] - 2025-09-17

-   Feat: change gravityforms export separator to match Dutch format

## [v1.2.1] - 2025-09-12

-   Fix: nullable parameter

## [v1.2.0] - 2025-09-12

-   Feat: always use wp-login for subsite
-   Fix: use correct reset password url
-   Fix: delete network user

## [v1.1.0] - 2025-09-09

-   Feat: a11y replace strong and em with span with class
-   Feat: hide password fields for OpenID users
-   Feat: delete network user without sites
-   Feat: add capability for editing attachments in Authorization

## [v1.0.19] - 2025-08-12

-   Fix: restore functionality of not sending user creation at all

## [v1.0.18] - 2025-07-23

-   Refactor: logging and guard clauses
-   Refactor: extract message content to method
-   Chore: adjust logging method to be more clear
-   Feat: add hooks for custom user activation email

## [v1.0.17] - 2025-06-26

-   Feat: add OpenID Connect hooks
-   Feat: disable password reset and login for openid users

## [v1.0.16] - 2025-04-10

-   Fix: remove title and description from gravityforms default attributes

## [v1.0.15] - 2025-03-25

-   Feat: add yard_edit_privacy_policy cap

## [v1.0.14] - 2025-03-24

-   Style: make gform notice less noticable
-   Feat: change gform default block attributes

## [v1.0.13] - 2025-02-14

-   Feat: elasticsearch hooks
-   Feat: wrap search param in qoutes if missing

## [v1.0.12] - 2025-02-13

-   Fix: always load acf json from parent

## [v1.0.11] - 2025-02-04

-   Fix: undefined array key in 'gform_field_validation' hook

## [v1.0.10] - 2025-01-31

-   Fix: html encoding issue

## [v1.0.9] - 2025-01-28

-   Feat: require php extensions

## [v1.0.8] - 2025-01-27

-   Feat: imagify do not optimize pdf
-   Feat: remove convert_smilies filter
-   Feat: add facetwp pager html changes
-   Feat: translate facetwp pager labels
-   Feat: add sr-only text to new tab links
-   Chore: remove direct dependency on php-cs-fixer
-   Feat: get Google Maps API key from .env
-   Feat: replace inline script with wp_print_inline_script_tag for csp compatibility

## [v1.0.7] - 2024-11-29

-   Feat: add gutenberg allowed-blocks-whitelisted-prefixes filter

## [v1.0.6] - 2024-12-05

-   Docs: update README.md
-   Feat: seperate csp headers from the rest
-   Feat: secure header hooks

## [v1.0.5] - 2024-11-28

-   Feat: add a11y toolbar classes to body class

## [v1.0.4] - 2024-11-28

-   Chore: apply php-cs-fixer changes
-   Chore: add classname to recaptcha disclaimer text
-   Fix: gravityForms default theme hook

## [v1.0.3] - 2024-08-16

-   Refactor: unnecessary condition
-   Chore: apply php-cs-fixer rules
-   Refactor: remove unnecessary isset check
-   Feat: hide admins from non admins
-   Feat: group authorization hooks

## [v1.0.2] - 2024-11-25

-   Fix: 'yard::gutenberg/allowed-core-blocks' hook

## [v1.0.1] - 2024-11-25

-   Fix: missing DOMDocument

## [v1.0.0] - 2024-08-16

-   Chore: cleanup workflows
-   Feat: copy hooks from brave
-   Chore: delete files
-   Chore: run configure script
