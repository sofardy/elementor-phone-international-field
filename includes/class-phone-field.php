<?php

namespace ElementorPhoneField;

if (! defined('ABSPATH')) {
    exit;
}

class Phone_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public function get_type()
    {
        return 'phone_intl';
    }

    public function get_name()
    {
        return esc_html__('Phone with Country', 'elementor-phone-field');
    }

    public function render($item, $item_index, $form)
    {
        $form->add_render_attribute(
            'input' . $item_index,
            [
                'type' => 'tel',
                'class' => 'elementor-field-textual elementor-phone-intl',
                'placeholder' => $item['placeholder'],
                'required' => $item['required'] ? 'required' : '',
                'id' => 'form-field-' . $item['custom_id'],
                'data-preferred-countries' => isset($item['preferred_countries']) ? $item['preferred_countries'] : 'ua,us,gb',
                'data-initial-country' => isset($item['initial_country']) ? $item['initial_country'] : 'ua',
            ]
        );

        $form->add_render_attribute(
            'input_full' . $item_index,
            [
                'type' => 'hidden',
                'name' => 'form_fields[' . $item['custom_id'] . '_full]',
                'class' => 'phone-full-number',
            ]
        );

        $form->add_render_attribute(
            'input_country' . $item_index,
            [
                'type' => 'hidden',
                'name' => 'form_fields[' . $item['custom_id'] . '_country]',
                'class' => 'phone-country-code',
            ]
        );

        $form->add_render_attribute('field-group' . $item_index, 'class', 'elementor-phone-field-wrapper');

        echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';
        echo '<input ' . $form->get_render_attribute_string('input_full' . $item_index) . '>';
        echo '<input ' . $form->get_render_attribute_string('input_country' . $item_index) . '>';
    }

    public function update_controls($widget)
    {
        $elementor = \Elementor\Plugin::$instance;
        $control_data = $elementor->controls_manager->get_control_from_stack(
            $widget->get_unique_name(),
            'form_fields'
        );

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'preferred_countries' => [
                'name' => 'preferred_countries',
                'label' => esc_html__('Preferred Countries', 'elementor-phone-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ua,us,gb',
                'description' => esc_html__('Comma separated country codes', 'elementor-phone-field'),
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'initial_country' => [
                'name' => 'initial_country',
                'label' => esc_html__('Initial Country', 'elementor-phone-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ua',
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    public function validation($field, $record, $ajax_handler)
    {
        // Логирование для отладки
        \error_log('Phone field validation called for field: ' . $field['_id']);
        \error_log('Field type: ' . ($field['field_type'] ?? 'unknown'));
        \error_log('POST data: ' . print_r($_POST, true));

        // Получаем данные из POST
        $posted_data = $_POST['form_fields'] ?? [];
        $field_id = $field['_id'];
        $full_phone_field_name = $field_id . '_full';

        \error_log('Looking for field: ' . $full_phone_field_name);
        \error_log('Posted data keys: ' . implode(', ', array_keys($posted_data)));

        // Проверяем, есть ли полный номер телефона
        if (isset($posted_data[$full_phone_field_name]) && !empty($posted_data[$full_phone_field_name])) {
            // Используем полный номер телефона вместо маскированного
            $full_phone_value = \sanitize_text_field($posted_data[$full_phone_field_name]);

            \error_log('Full phone value found: ' . $full_phone_value);

            // Обновляем значение поля в записи
            $record->update_field($field_id, 'value', $full_phone_value);

            \error_log('Field updated successfully');

            // Также сохраняем код страны для дополнительной информации
            $country_field_name = $field_id . '_country';
            if (isset($posted_data[$country_field_name]) && !empty($posted_data[$country_field_name])) {
                $country_value = \sanitize_text_field($posted_data[$country_field_name]);
                \error_log('Country code found: ' . $country_value);
                // Примечание: код страны доступен в POST данных, но не добавляется в запись
                // так как метод add_field() не существует в Form_Record
            }
        } else {
            \error_log('Full phone field not found or empty');
            \error_log('Available fields: ' . implode(', ', array_keys($posted_data)));
        }

        // Возвращаем без ошибок валидации
        return;
    }

    public function __construct()
    {
        parent::__construct();
        \add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_assets']);

        // Добавляем универсальный хук для обработки всех форм
        \add_action('elementor_pro/forms/validation', [$this, 'process_all_phone_fields'], 10, 2);

        // Дополнительно пробуем через другой хук
        \add_action('elementor_pro/forms/process', [$this, 'process_phone_fields_on_submit'], 10, 2);
    }

    public function process_phone_fields_on_submit($record, $ajax_handler)
    {
        // Логирование для отладки
        \error_log('Processing phone fields on submit...');

        // Получаем данные из POST
        $posted_data = $_POST['form_fields'] ?? [];

        // Ищем все поля с суффиксом _full (полный номер телефона)
        foreach ($posted_data as $field_name => $field_value) {
            if (strpos($field_name, '_full') !== false) {
                // Получаем ID основного поля (убираем _full)
                $main_field_id = str_replace('_full', '', $field_name);

                \error_log('Found full phone field: ' . $field_name . ' with value: ' . $field_value);

                if (!empty($field_value)) {
                    // Используем полный номер телефона вместо маскированного
                    $full_phone_value = \sanitize_text_field($field_value);

                    // Обновляем значение основного поля в записи
                    $record->update_field($main_field_id, 'value', $full_phone_value);

                    \error_log('Updated field ' . $main_field_id . ' with value: ' . $full_phone_value);
                }
            }
        }
    }

    public function process_all_phone_fields($record, $ajax_handler)
    {
        // Логирование для отладки
        \error_log('Processing all phone fields...');
        \error_log('POST data: ' . print_r($_POST, true));

        // Получаем данные из POST
        $posted_data = $_POST['form_fields'] ?? [];

        // Ищем все поля с суффиксом _full (полный номер телефона)
        foreach ($posted_data as $field_name => $field_value) {
            if (strpos($field_name, '_full') !== false) {
                // Получаем ID основного поля (убираем _full)
                $main_field_id = str_replace('_full', '', $field_name);

                \error_log('Found full phone field: ' . $field_name . ' with value: ' . $field_value);
                \error_log('Main field ID: ' . $main_field_id);

                if (!empty($field_value)) {
                    // Используем полный номер телефона вместо маскированного
                    $full_phone_value = \sanitize_text_field($field_value);

                    // Обновляем значение основного поля в записи
                    $record->update_field($main_field_id, 'value', $full_phone_value);

                    \error_log('Updated field ' . $main_field_id . ' with value: ' . $full_phone_value);

                    // Также сохраняем код страны для дополнительной информации
                    $country_field_name = $main_field_id . '_country';
                    if (isset($posted_data[$country_field_name]) && !empty($posted_data[$country_field_name])) {
                        $country_value = \sanitize_text_field($posted_data[$country_field_name]);
                        \error_log('Country code found: ' . $country_value);
                        // Примечание: код страны доступен в POST данных, но не добавляется в запись
                        // так как метод add_field() не существует в Form_Record
                    }
                }
            }
        }
    }

    public function enqueue_assets()
    {
        // Регистрация внешних скриптов и стилей
        \wp_register_style('intl-tel-input', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css');
        \wp_register_script('intl-tel-input', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js', ['jquery'], null, true);
        \wp_register_script('imask', 'https://cdnjs.cloudflare.com/ajax/libs/imask/6.2.2/imask.min.js', [], null, true);

        // Подключение стилей и скриптов плагина
        // Используем \plugins_url и правильный путь от файла плагина
        $plugin_dir_url = \plugin_dir_url(__DIR__); // URL до родительской директории (корня плагина)
        \wp_enqueue_style('elementor-phone-field', $plugin_dir_url . 'assets/css/phone-field.css');
        \wp_enqueue_script('elementor-phone-field', $plugin_dir_url . 'assets/js/phone-field.js', ['jquery', 'intl-tel-input', 'imask'], '1.0.1', true);

        // Подключение внешних зависимостей
        \wp_enqueue_style('intl-tel-input');
        \wp_enqueue_script('intl-tel-input');
        \wp_enqueue_script('imask');
    }
}
