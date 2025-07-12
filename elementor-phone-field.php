<?php

/**
 * Plugin Name: Elementor Phone Field
 * Description: Добавляет поле телефона с выбором страны, маской и валидацией для Elementor Forms
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: elementor-phone-field
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
        'elementor-phone-field',
        ELEMENTOR_PHONE_FIELD_URL . 'assets/js/phone-field.js',
        ['jquery', 'intl-tel-input', 'imask'],
        '1.0.0',
        true
    );

    wp_register_style(
        'elementor-phone-field',
        ELEMENTOR_PHONE_FIELD_URL . 'assets/css/phone-field.css',
        ['intl-tel-input'],
        '1.0.0'
    );
});
