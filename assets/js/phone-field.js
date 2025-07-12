(function ($) {
    'use strict';

    class ElementorPhoneField {
        constructor() {
            this.initFields();
            this.bindEvents();
        }

        initFields() {
            $('.elementor-phone-intl').each((index, element) => {
                if (element.hasAttribute('data-initialized')) {
                    return;
                }

                const $field = $(element);
                const $wrapper = $field.closest('.elementor-phone-field-wrapper');
                const $form = $field.closest('form');

                // Получаем настройки из data-атрибутов
                const preferredCountries = $field.data('preferred-countries')
                    ? $field.data('preferred-countries').split(',')
                    : ['ua', 'us', 'gb'];
                const initialCountry = $field.data('initial-country') || 'ua';

                // Инициализация intl-tel-input
                const iti = window.intlTelInput(element, {
                    preferredCountries: preferredCountries,
                    initialCountry: initialCountry,
                    separateDialCode: true,
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
                    customPlaceholder: function (selectedCountryPlaceholder, selectedCountryData) {
                        return selectedCountryPlaceholder;
                    }
                });

                // Сохраняем instance
                element.iti = iti;
                element.setAttribute('data-initialized', 'true');

                // Применяем маску только после обновления placeholder
                const applyMaskOnPlaceholder = () => {
                    this.initMask(element, iti);
                    observer.disconnect();
                };
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'placeholder') {
                            applyMaskOnPlaceholder();
                        }
                    });
                });
                observer.observe(element, { attributes: true });

                // Если placeholder уже установлен, применяем маску сразу
                if (element.placeholder) {
                    applyMaskOnPlaceholder();
                }

                // Обновляем скрытые поля
                this.updateHiddenFields(element, iti);

                // Обработчик изменения страны
                element.addEventListener('countrychange', () => {
                    this.initMask(element, iti);
                    this.updateHiddenFields(element, iti);
                });

                // Обработчик ввода
                element.addEventListener('input', () => {
                    this.updateHiddenFields(element, iti);
                });

                // Обработчик изменения (для вставки текста)
                element.addEventListener('change', () => {
                    this.updateHiddenFields(element, iti);
                });

                // Обработчик потери фокуса
                element.addEventListener('blur', () => {
                    this.updateHiddenFields(element, iti);
                });                // Валидация при отправке формы
                $form.on('submit', (e) => {
                    // Обновляем скрытые поля перед отправкой
                    this.updateHiddenFields(element, iti);

                    // Проверяем только если поле заполнено
                    if (element.value && element.value.trim() !== '') {
                        if (!iti.isValidNumber()) {
                            e.preventDefault();
                            $field.addClass('elementor-field-invalid');
                            this.showError($field, 'Введите корректный номер телефона');
                            return false;
                        }
                    }
                });

                // Дополнительный обработчик для элементор форм
                $form.on('elementor_pro/forms/form_submitted', (e) => {
                    this.updateHiddenFields(element, iti);
                });
            });
        }

        initMask(element, iti) {
            // Удаляем предыдущую маску
            if (element.imask) {
                element.imask.destroy();
            }

            const countryData = iti.getSelectedCountryData();
            const placeholder = element.placeholder || '';

            // Создаем паттерн маски на основе placeholder
            const maskPattern = placeholder.replace(/[0-9]/g, '0').replace(/\s/g, ' ');

            if (maskPattern && maskPattern !== placeholder) {
                element.imask = IMask(element, {
                    mask: maskPattern,
                    lazy: false,
                    placeholderChar: '_'
                });
            }
        }

        updateHiddenFields(element, iti) {
            const $element = $(element);
            const $fieldWrapper = $element.closest('.elementor-field-group, .elementor-form-fields-wrapper');

            const $fullNumber = $fieldWrapper.find('.phone-full-number');
            const $countryCode = $fieldWrapper.find('.phone-country-code');

            if (element.value && element.value.trim() !== '') {
                try {
                    const fullNumber = iti.getNumber();
                    const countryData = iti.getSelectedCountryData();

                    $fullNumber.val(fullNumber || element.value);
                    $countryCode.val(countryData.iso2 || '');

                    if (iti.isValidNumber()) {
                        $element.removeClass('elementor-field-invalid');
                    } else {
                        $element.addClass('elementor-field-invalid');
                    }
                } catch (e) {
                    $fullNumber.val(element.value);
                    $countryCode.val('');
                }
            } else {
                $fullNumber.val('');
                $countryCode.val('');
                $element.removeClass('elementor-field-invalid');
            }
        }

        showError($field, message) {
            const $wrapper = $field.closest('.elementor-field-group');
            let $error = $wrapper.find('.elementor-phone-error');

            if (!$error.length) {
                $error = $('<span class="elementor-phone-error elementor-message elementor-message-danger"></span>');
                $wrapper.append($error);
            }

            $error.text(message).show();

            setTimeout(() => {
                $error.fadeOut();
            }, 5000);
        }

        // Метод для принудительного обновления всех телефонных полей
        updateAllPhoneFields() {
            $('.elementor-phone-intl').each((index, element) => {
                if (element.iti) {
                    this.updateHiddenFields(element, element.iti);
                }
            });
        }

        bindEvents() {
            // Реинициализация при AJAX загрузке
            $(document).on('elementor/frontend/init', () => {
                this.initFields();
            });

            // Для попапов Elementor
            $(document).on('elementor/popup/show', () => {
                setTimeout(() => {
                    this.initFields();
                }, 100);
            });

            // Для динамического контента
            $(document).on('elementor/background/onInit', () => {
                this.initFields();
            });

            // Обработчик перед отправкой формы Elementor
            $(document).on('elementor_pro/forms/form_submitted', (e) => {
                $('.elementor-phone-intl').each((index, element) => {
                    if (element.iti) {
                        this.updateHiddenFields(element, element.iti);
                    }
                });
            });

            // Переинициализация при изменении DOM
            const observer = new MutationObserver((mutations) => {
                let shouldReinit = false;
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1 && (
                                node.matches('.elementor-phone-intl') ||
                                node.querySelector('.elementor-phone-intl')
                            )) {
                                shouldReinit = true;
                            }
                        });
                    }
                });
                if (shouldReinit) {
                    setTimeout(() => this.initFields(), 100);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // Инициализация
    $(document).ready(() => {
        new ElementorPhoneField();
    });

    // Дополнительная инициализация для Elementor
    $(window).on('elementor/frontend/init', () => {
        setTimeout(() => {
            new ElementorPhoneField();
        }, 500);
    });

    // Обработчик для событий Elementor Pro Forms
    $(document).on('submit', '.elementor-form', function (e) {
        $('.elementor-phone-intl', this).each(function () {
            if (this.iti) {
                const $element = $(this);
                const $form = $element.closest('form');
                const fieldId = this.id.replace('form-field-', '');
                const $fullNumber = $form.find(`input[name="form_fields[${fieldId}_full]"]`);
                const $countryCode = $form.find(`input[name="form_fields[${fieldId}_country]"]`);

                if (this.value && this.value.trim() !== '') {
                    try {
                        const fullNumber = this.iti.getNumber();
                        const countryData = this.iti.getSelectedCountryData();
                        $fullNumber.val(fullNumber || this.value);
                        $countryCode.val(countryData.iso2 || '');

                        // Заменяем значение основного поля на полный номер
                        if (this.iti.isValidNumber()) {
                            $element.val(fullNumber);
                        }

                    } catch (e) {
                        $fullNumber.val(this.value);
                        $countryCode.val('');
                    }
                }
            }
        });
    });

})(jQuery);
