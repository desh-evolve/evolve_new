<?php

if (!function_exists('html_options')) {
    /**
     * Generates HTML <option> tags from an array of options.
     *
     * @param array $params Parameters including name, options, values, output, selected
     * @return string HTML output
     */
    function html_options($params)
    {
        $name = $params['name'] ?? null;
        $values = $params['values'] ?? null;
        $options = $params['options'] ?? null;
        $selected = isset($params['selected']) ? array_map('strval', array_values((array)$params['selected'])) : [];
        $output = $params['output'] ?? null;

        $extra = '';
        foreach ($params as $key => $val) {
            if (!in_array($key, ['name', 'options', 'values', 'output', 'selected']) && !is_array($val)) {
                $extra .= ' ' . $key . '="' . e($val) . '"';
            }
        }

        $html_result = '';

        if ($options) {
            foreach ($options as $key => $val) {
                $html_result .= html_options_optoutput($key, $val, $selected);
            }
        } elseif ($values) {
            foreach ($values as $i => $key) {
                $val = $output[$i] ?? '';
                $html_result .= html_options_optoutput($key, $val, $selected);
            }
        }

        if ($name) {
            $html_result = '<select name="' . $name . '"' . $extra . '>' . "\n" . $html_result . '</select>' . "\n";
        }

        return $html_result;
    }
}

if (!function_exists('html_options_optoutput')) {
    function html_options_optoutput($key, $value, $selected)
    {
        if (!is_array($value)) {
            $html_result = '<option label="' . e($value) . '" value="' . e($key) . '"';
            if (in_array((string)$key, $selected)) {
                $html_result .= ' selected="selected"';
            }
            $html_result .= '>' . e($value) . '</option>' . "\n";
        } else {
            $html_result = html_options_optgroup($key, $value, $selected);
        }
        return $html_result;
    }
}

if (!function_exists('html_options_optgroup')) {
    function html_options_optgroup($key, $values, $selected)
    {
        $optgroup_html = '<optgroup label="' . e($key) . '">' . "\n";
        foreach ($values as $key => $value) {
            $optgroup_html .= html_options_optoutput($key, $value, $selected);
        }
        $optgroup_html .= "</optgroup>\n";
        return $optgroup_html;
    }
}

if (!function_exists('select_size')) {
    /**
     * Calculates the size attribute for a select box based on the number of options.
     *
     * @param array $params Parameters including array, array2, min, max
     * @return int Size value
     */
    function select_size($params)
    {
        $array = $params['array'] ?? [];
        $array2 = $params['array2'] ?? [];
        $min = $params['min'] ?? '';
        $max = $params['max'] ?? '';

        $total = count($array) + count($array2);
        $retval = ceil($total / 3);

        if ($min !== '' && $retval < $min) {
            $retval = $min;
        } elseif ($max !== '' && $retval > $max) {
            $retval = $max;
        }

        if ($total > 0 && $retval < 5) {
            $retval = $total < 5 ? $total : 5;
        } elseif ($total == 0) {
            $retval = 2;
        }

        if ($retval > 30) {
            $retval = 30;
        }

        return ceil($retval);
    }
}

if (!function_exists('is_valid')) {
    /**
     * Validates an object field and returns a CSS class based on validity.
     *
     * @param array $params Parameters including object, label, value, error_value
     * @return string CSS class
     */
    function is_valid($params)
    {
        $object = $params['object'] ?? '';
        $value = $params['value'] ?? '';
        $label = $params['label'] ?? '';
        $error_value = $params['error_value'] ?? ($value . '_error');

        $labels = str_contains($label, ',') ? explode(',', $label) : [$label];

        foreach ($labels as $lbl) {
            // Assuming Validator is accessible via the object
            // You may need to adjust this based on your application's validation logic
            if (isset($GLOBALS['smarty']->_tpl_vars[$object]->Validator) &&
                !$GLOBALS['smarty']->_tpl_vars[$object]->Validator->isValid($lbl)) {
                return $error_value;
            }
        }

        return $value;
    }
}