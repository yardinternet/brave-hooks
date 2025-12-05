# Brave Hooks

[![Code Style](https://github.com/yardinternet/brave-hooks/actions/workflows/format-php.yml/badge.svg?no-cache)](https://github.com/yardinternet/brave-hooks/actions/workflows/format-php.yml)

## Features

- [x] Register Hooks using php attributes
- [x] Configure Hook registration using a config file
- [x] Load plugin-specific hooks only when the plugin is active

## Installation
   
1. Install this package with Composer:

    ```sh
    composer require yard/brave-hooks
    ```

2. Run the Acorn WP-CLI command to discover this package:

    ```shell
    wp acorn package:discover
    ```

3. Publish the config file with:

   ```shell
   wp acorn vendor:publish --provider="Yard\Brave\Hooks\HookServiceProvider"
   ```

5. Register all your project hooks in the published configuration file `config/hooks.php`.

## Usage

Add your custom hook classes to the [hooks configuration file](./config/hooks.php). Any hooks defined within these
classes will be automatically registered with WordPress.

This plugin leverages [wp-hook-registrar](https://github.com/yardinternet/wp-hook-registrar) for hook registration. For
detailed information about the hook registration process, please refer to that package's documentation.

### Plugin-specific Hooks

Some classes contain hooks that should only be active when a specific WordPress plugin is active.
To achieve this, add the `#[Plugin]` attribute to the class containing hooks and provide the plugin's file path:

The plugin path should match the format required by the WordPress function [is_plugin_active()](https://developer.wordpress.org/reference/functions/is_plugin_active/)

```php
#[Plugin('advanced-custom-fields-pro/acf.php')]
class ACF
{
    ...
}
```
## About us

[![banner](https://raw.githubusercontent.com/yardinternet/.github/refs/heads/main/profile/assets/small-banner-github.svg)](https://www.yard.nl/werken-bij/)
