<?php

namespace Sirgrimorum\CrudGenerator\Helpers;

/**
 * Drop-in replacement for laravelcollective/html Form facade.
 * Provides the exact Form:: static methods used in CrudGenerator blade templates.
 * Registered as the 'Form' alias by CrudGeneratorServiceProvider.
 */
class FormHelper
{
    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public static function open(array $attrs = []): string
    {
        $method  = strtoupper($attrs['method'] ?? 'POST');
        $action  = $attrs['url'] ?? $attrs['action'] ?? '';
        $files   = !empty($attrs['files']);

        unset($attrs['url'], $attrs['action'], $attrs['method'], $attrs['files']);

        // HTML forms only support GET and POST natively
        $formMethod = in_array($method, ['GET', 'POST']) ? $method : 'POST';

        $formAttrs = array_merge(['method' => $formMethod, 'action' => url($action)], $attrs);
        if ($files) {
            $formAttrs['enctype'] = 'multipart/form-data';
        }

        $html = '<form' . static::buildAttributes($formAttrs) . '>';

        // CSRF token (omitted for GET forms)
        if ($formMethod !== 'GET') {
            $token = csrf_token();
            if ($token) {
                $html .= '<input type="hidden" name="_token" value="' . static::esc($token) . '">';
            }
        }

        // Method spoofing for PUT / PATCH / DELETE
        if (!in_array($method, ['GET', 'POST'])) {
            $html .= '<input type="hidden" name="_method" value="' . static::esc($method) . '">';
        }

        return $html;
    }

    public static function close(): string
    {
        return '</form>';
    }

    public static function hidden(string $name, $value = null, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'hidden', 'name' => $name, 'value' => (string) ($value ?? '')], $attrs);
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    public static function submit(string $value, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'submit', 'value' => $value], $attrs);
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    public static function label(string $name, string $text, array $attrs = []): string
    {
        if (!array_key_exists('for', $attrs)) {
            $attrs = array_merge(['for' => $name], $attrs);
        }
        return '<label' . static::buildAttributes($attrs) . '>' . static::esc($text) . '</label>';
    }

    public static function text(string $name, $value = null, array $attrs = []): string
    {
        return static::input('text', $name, $value, $attrs);
    }

    public static function email(string $name, $value = null, array $attrs = []): string
    {
        return static::input('email', $name, $value, $attrs);
    }

    public static function password(string $name, array $attrs = []): string
    {
        // password inputs never pre-fill value
        return static::input('password', $name, null, $attrs);
    }

    public static function number(string $name, $value = null, array $attrs = []): string
    {
        return static::input('number', $name, $value, $attrs);
    }

    public static function file(string $name, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'file', 'name' => $name], $attrs);
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    public static function textarea(string $name, $value = null, array $attrs = []): string
    {
        $attrs  = array_merge(['name' => $name], $attrs);
        $content = static::esc((string) ($value ?? ''));
        return '<textarea' . static::buildAttributes($attrs) . '>' . $content . '</textarea>';
    }

    /**
     * @param array        $options   [value => label, ...]
     * @param string|array $selected  currently selected value(s)
     */
    public static function select(string $name, array $options = [], $selected = null, array $attrs = []): string
    {
        $attrs = array_merge(['name' => $name], $attrs);
        $html  = '<select' . static::buildAttributes($attrs) . '>';
        foreach ($options as $value => $label) {
            $isSelected = static::isSelected($value, $selected) ? ' selected="selected"' : '';
            $html .= '<option value="' . static::esc((string) $value) . '"' . $isSelected . '>'
                   . static::esc((string) $label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public static function checkbox(string $name, $value = 1, $checked = false, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'checkbox', 'name' => $name, 'value' => $value], $attrs);
        if ($checked) {
            $attrs['checked'] = 'checked';
        }
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    public static function radio(string $name, $value = null, $checked = false, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'radio', 'name' => $name, 'value' => $value], $attrs);
        if ($checked) {
            $attrs['checked'] = 'checked';
        }
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    protected static function input(string $type, string $name, $value = null, array $attrs = []): string
    {
        $base = ['type' => $type, 'name' => $name];
        if ($type !== 'password' && $value !== null) {
            $base['value'] = (string) $value;
        }
        $attrs = array_merge($base, $attrs);
        return '<input' . static::buildAttributes($attrs) . '>';
    }

    /**
     * Convert an attribute array to an HTML attribute string.
     *
     * Handles:
     *  - string key => string value  →  key="value"
     *  - string key => true          →  key="key"
     *  - string key => false/null    →  (skipped)
     *  - int key    => non-empty string containing '='  →  output as-is (raw, e.g. "disabled='disabled'")
     *  - int key    => non-empty string without '='     →  name="name"
     *  - int key    => empty string  →  (skipped)
     */
    protected static function buildAttributes(array $attrs): string
    {
        $html = '';
        foreach ($attrs as $key => $value) {
            if (is_int($key)) {
                // Integer-indexed entries are raw attribute strings or standalone booleans
                if (is_string($value) && $value !== '') {
                    if (str_contains($value, '=')) {
                        // Already a full attribute pair like "disabled='disabled'" — output as-is
                        $html .= ' ' . $value;
                    } else {
                        // Standalone attribute name like "readonly" → readonly="readonly"
                        $html .= ' ' . static::esc($value) . '="' . static::esc($value) . '"';
                    }
                }
                continue;
            }

            if ($value === false || $value === null) {
                continue;
            }

            if ($value === true) {
                $html .= ' ' . static::esc($key) . '="' . static::esc($key) . '"';
            } else {
                $html .= ' ' . static::esc($key) . '="' . static::esc((string) $value) . '"';
            }
        }
        return $html;
    }

    protected static function isSelected($value, $selected): bool
    {
        if ($selected === null) {
            return false;
        }
        if (is_array($selected)) {
            return in_array((string) $value, array_map('strval', $selected));
        }
        return (string) $value === (string) $selected;
    }

    protected static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
