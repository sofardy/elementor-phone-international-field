<?php

/**
 * Plugin Name: Elementor Phone International By Sofard
 * Description: Adds a phone field with country selection, masking, and validation for Elementor Forms
 * Version: 1.0.0
 * Author: Sofard
 * Text Domain: elementor-phone-international-by-sofard
 * GitHub Plugin URI: sofardy/elementor-phone-international-field
 * Primary Branch: main
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ELEMENTOR_PHONE_FIELD_URL', plugin_dir_url(__FILE__));
define('ELEMENTOR_PHONE_FIELD_PATH', plugin_dir_path(__FILE__));

// Подключаем основной класс плагина
add_action('plugins_loaded', function () {
    if (! did_action('elementor/loaded')) {
        return;
    }

    require_once ELEMENTOR_PHONE_FIELD_PATH . 'includes/class-phone-field.php';

    // Регистрируем новое поле
    add_action('elementor_pro/forms/fields/register', function ($form_fields_registrar) {
        $form_fields_registrar->register(new \ElementorPhoneField\Phone_Field());
    });
});

// Подключаем скрипты и стили
add_action('wp_enqueue_scripts', function () {
    wp_register_style(
        'intl-tel-input',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css',
        [],
        '18.2.1'
    );

    wp_register_script(
        'intl-tel-input',
        'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js',
        [],
        '18.2.1',
        true
    );

    wp_register_script(
        'imask',
        'https://unpkg.com/imask',
        [],
        'latest',
        true
    );

    wp_register_script(
        'elementor-phone-international-field',
        ELEMENTOR_PHONE_FIELD_URL . 'assets/js/phone-field.js',
        ['jquery', 'intl-tel-input', 'imask'],
        '1.0.0',
        true
    );

    wp_register_style(
        'elementor-phone-international-field',
        ELEMENTOR_PHONE_FIELD_URL . 'assets/css/phone-field.css',
        ['intl-tel-input'],
        '1.0.0'
    );
});

// ===== СИСТЕМА АВТОМАТИЧЕСКИХ ОБНОВЛЕНИЙ ЧЕРЕЗ GITHUB =====

class ElementorPhoneFieldUpdater
{

    private $plugin_slug;
    private $plugin_path;
    private $current_version;
    private $github_username;
    private $github_repo;
    private $github_token; // Для приватных репозиториев

    public function __construct()
    {
        $this->plugin_slug = 'elementor-phone-international-field/elementor-phone-international-field.php';
        $this->plugin_path = plugin_basename(__FILE__);
        $this->current_version = '1.0.0';
        $this->github_username = 'sofardy'; // Замените на ваш GitHub username
        $this->github_repo = 'elementor-phone-international-field'; // Замените на название вашего репозитория
        $this->github_token = ''; // Если репозиторий приватный, добавьте токен

        add_filter('site_transient_update_plugins', [$this, 'check_for_updates']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);
    }

    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Проверяем обновления максимум раз в 12 часов
        $last_checked = get_transient('elementor_phone_field_last_checked');
        if ($last_checked && (time() - $last_checked) < 12 * HOUR_IN_SECONDS) {
            return $transient;
        }

        $latest_version = $this->get_latest_version();

        if ($latest_version && version_compare($this->current_version, $latest_version, '<')) {
            $transient->response[$this->plugin_path] = (object)[
                'slug'        => dirname($this->plugin_path),
                'plugin'      => $this->plugin_path,
                'new_version' => $latest_version,
                'package'     => $this->get_download_url($latest_version),
                'url'         => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'tested'      => get_bloginfo('version'),
                'compatibility' => new stdClass(),
            ];
        }

        set_transient('elementor_phone_field_last_checked', time(), 12 * HOUR_IN_SECONDS);

        return $transient;
    }

    public function plugin_info($res, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $res;
        }

        if ($args->slug !== dirname($this->plugin_path)) {
            return $res;
        }

        $latest_version = $this->get_latest_version();
        $changelog = $this->get_changelog();

        $res = (object)[
            'name'          => 'Elementor Phone International By Sofard',
            'slug'          => dirname($this->plugin_path),
            'version'       => $latest_version ?: $this->current_version,
            'author'        => 'Sofard',
            'homepage'      => "https://github.com/{$this->github_username}/{$this->github_repo}",
            'short_description' => 'Adds a phone field with country selection, masking, and validation for Elementor Forms',
            'sections'      => [
                'description' => 'Professional phone field for Elementor Forms with international country selection, input masking, and validation.',
                'changelog'   => $changelog,
            ],
            'download_link' => $this->get_download_url($latest_version),
            'tested'        => get_bloginfo('version'),
            'requires'      => '5.0',
            'requires_php'  => '7.4',
        ];

        return $res;
    }

    public function post_install($response, $hook_extra, $result)
    {
        global $wp_filesystem;

        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_path) {
            return $response;
        }

        // Исправляем структуру папок после установки
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->plugin_path);

        if (is_dir($plugin_folder)) {
            $wp_filesystem->move($result['destination'], $plugin_folder);
            $result['destination'] = $plugin_folder;
        }

        return $response;
    }

    private function get_latest_version()
    {
        $url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        $headers = ['User-Agent' => 'WordPress Plugin Updater'];

        if (!empty($this->github_token)) {
            $headers['Authorization'] = "token {$this->github_token}";
        }

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('ElementorPhoneField Updater: Failed to check for updates - ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['tag_name'])) {
            return false;
        }

        return ltrim($body['tag_name'], 'v');
    }

    private function get_download_url($version = null)
    {
        if (!$version) {
            $version = $this->get_latest_version();
        }

        if (!$version) {
            return false;
        }

        // Для публичных репозиториев используем zipball_url
        if (empty($this->github_token)) {
            return "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/v{$version}.zip";
        }

        // Для приватных репозиториев нужен другой подход
        return "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/zipball/v{$version}";
    }

    private function get_changelog()
    {
        $url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases";
        $headers = ['User-Agent' => 'WordPress Plugin Updater'];

        if (!empty($this->github_token)) {
            $headers['Authorization'] = "token {$this->github_token}";
        }

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return 'Changelog not available.';
        }

        $releases = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($releases)) {
            return 'Changelog not available.';
        }

        $changelog = '';
        foreach (array_slice($releases, 0, 5) as $release) {
            $changelog .= "<h4>Version {$release['tag_name']}</h4>";
            $changelog .= "<p><strong>Released:</strong> " . date('F j, Y', strtotime($release['published_at'])) . "</p>";
            $changelog .= "<p>" . (empty($release['body']) ? 'No changelog provided.' : nl2br($release['body'])) . "</p>";
        }

        return $changelog;
    }
}

// Инициализируем систему обновлений
new ElementorPhoneFieldUpdater();

// Добавляем информацию о версии в админке
add_action('admin_init', function () {
    add_filter('plugin_row_meta', function ($plugin_meta, $plugin_file) {
        if ($plugin_file === plugin_basename(__FILE__)) {
            $plugin_meta[] = '<a href="https://github.com/sofardy/elementor-phone-international-field" target="_blank">GitHub</a>';
            $plugin_meta[] = '<a href="https://github.com/sofardy/elementor-phone-international-field/releases" target="_blank">Releases</a>';
        }
        return $plugin_meta;
    }, 10, 2);
});

// Добавляем уведомление о необходимости настройки GitHub
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Показываем уведомление только на странице плагинов
    $screen = get_current_screen();
    if ($screen && $screen->id !== 'plugins') {
        return;
    }

    // Проверяем, настроен ли GitHub
    $updater = new ElementorPhoneFieldUpdater();
    $reflection = new ReflectionClass($updater);
    $github_username = $reflection->getProperty('github_username');
    $github_username->setAccessible(true);
});
