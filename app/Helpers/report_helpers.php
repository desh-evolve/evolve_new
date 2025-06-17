<?php

if (!function_exists('html_report_filter')) {
    /**
     * Generates HTML for a report filter with dual select boxes for selecting/unselecting options.
     *
     * @param array $params Parameters including filter_data, label, colspan, date_type, order, display_name, display_plural_name, type
     * @return string HTML output
     */
    function html_report_filter($params)
    {
        $filter_data = $params['filter_data'] ?? [];
        $label = $params['label'] ?? '';
        $colspan = $params['colspan'] ?? 2;
        $date_type = $params['date_type'] ?? false;
        $order = $params['order'] ?? false;
        $display_name = __($params['display_name'] ?? $label); // Using Laravel's translation
        $display_plural_name = __($params['display_plural_name'] ?? $label);
        $type = $params['type'] ?? '';

        // Calculate select box sizes
        $src_select_box_size = select_size(['array' => $filter_data['src_' . $label . '_options'] ?? [], 'min' => 2]);
        $select_box_size = select_size(['array' => $filter_data['selected_' . $label . '_options'] ?? [], 'min' => 2]);
        $max_select_box_size = max($src_select_box_size, $select_box_size, 2);

        // Type-specific HTML (e.g., for job type)
        $type_src_html = '';
        $type_dst_html = '';
        if ($type === 'job') {
            $type_src_html = '<b>Code:</b> <input type="text" size="4" id="src_quick_job_id_' . $label . '" onKeyUp="TIMETREX.punch.selectJobOption( \'src_quick_job_id_' . $label . '\', \'src_filter_' . $label . '\' );">';
            $type_dst_html = '<b>Code:</b> <input type="text" size="4" id="quick_job_id_' . $label . '" onKeyUp="TIMETREX.punch.selectJobOption( \'quick_job_id_' . $label . '\', \'filter_' . $label . '\' );">';
        }

        // Order arrows
        $order_html = '';
        if ($order) {
            $order_html = '<br><br><a href="javascript:select_item_move_up(document.getElementById(\'filter_' . $label . '\') );"><i class="ri-arrow-drop-up-fill arrow-icon" style="vertical-align: middle"></i></a><a href="javascript:select_item_move_down(document.getElementById(\'filter_' . $label . '\') );"><i class="ri-arrow-drop-down-fill arrow-icon" style="vertical-align: middle"></i></a>';
        }
        
        // Date type radio button
        $retval = '<tr>';
        if ($date_type) {
            $colspan = 1;
            $date_type_selected = (!empty($filter_data['date_type']) && $filter_data['date_type'] === $label . '_ids') ? 'checked' : '';
            $retval .= '<td class="cellReportRadioColumn">
                <input type="radio" class="checkbox" id="date_type_' . $label . '" name="filter_data[date_type]" value="' . $label . '_ids" onClick="showReportDateType();" ' . $date_type_selected . '>
            </td>';
        }

        // Main filter HTML
        $retval .= '
            <td colspan="' . $colspan . '" class="text-end" style="width:25%" nowrap>
                <b>' . $display_name . ':</b> <a href="javascript:toggleReportCriteria(\'filter_' . $label . '\');"><i class="ri-arrow-down-double-fill arrow-icon" style="vertical-align: middle" id="filter_' . $label . '_img" ></i></a>
            </td>
            <td id="filter_' . $label . '_right_cell" class="cellRightEditTableHeader">
                <div id="filter_' . $label . '_on" style="display:none">
                    <table class="table">
                        <tr class="bg-primary text-white text-center">
                            <td>' . sprintf(__('UnSelected %s'), $display_plural_name) . '</td>
                            <td><br></td>
                            <td>' . sprintf(__('Selected %s'), $display_plural_name) . '</td>
                        </tr>
                        <tr>
                            <td class="cellRightEditTable" width="50%" align="center">
                                ' . $type_src_html . '
                                <input type="button" name="Select All" value="' . __('Select All') . '" onClick="selectAll(document.getElementById(\'src_filter_' . $label . '\'))">
                                <input type="button" name="Un-Select" value="' . __('Un-Select All') . '" onClick="unselectAll(document.getElementById(\'src_filter_' . $label . '\'))">
                                <br>
                                <select id="src_filter_' . $label . '" style="width:90%;margin:5px 0 5px 0;" size="' . $src_select_box_size . '" multiple>
                                    ' . html_options(['options' => $filter_data['src_' . $label . '_options'] ?? []]) . '
                                </select>
                            </td>
                            <td class="cellRightEditTable" style="vertical-align: middle;" width="1">
                                <a href="javascript:moveReportCriteriaItems(\'src_filter_' . $label . '\', \'filter_' . $label . '\', ' . $max_select_box_size . ', true, \'value\' );"><i class="ri-arrow-right-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                <a href="javascript:moveReportCriteriaItems(\'filter_' . $label . '\', \'src_filter_' . $label . '\', ' . $max_select_box_size . ', true, \'value\' );"><i class="ri-arrow-left-double-fill arrow-icon" style="vertical-align: middle"></i></a>
                                ' . $order_html . '
                            </td>
                            <td class="cellRightEditTable" align="center">
                                ' . $type_dst_html . '
                                <input type="button" name="Select All" value="' . __('Select All') . '" onClick="selectAll(document.getElementById(\'filter_' . $label . '\'))">
                                <input type="button" name="Un-Select" value="' . __('Un-Select All') . '" onClick="unselectAll(document.getElementById(\'filter_' . $label . '\'))">
                                <br>
                                <select name="filter_data[' . $label . '_ids][]" id="filter_' . $label . '" style="width:90%;margin:5px 0 5px 0;" size="' . $select_box_size . '" multiple>
                                    ' . html_options([
                                        'options' => $filter_data['selected_' . $label . '_options'] ?? [],
                                        'selected' => array_keys($filter_data['selected_' . $label . '_options'] ?? [])
                                    ]) . '
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="filter_' . $label . '_off">
                    <span id="filter_' . $label . '_count">' . __('N/A') . '</span> ' . __('currently selected, click the arrow to modify.') . '
                </div>
            </td>
        </tr>';

        return $retval;
    }
}

if (!function_exists('html_report_group')) {
    /**
     * Generates HTML for a report grouping selection.
     *
     * @param array $params Parameters including filter_data, total
     * @return string HTML output
     */
    function html_report_group($params)
    {
        $filter_data = $params['filter_data'] ?? [];
        $total = $params['total'] ?? 2;

        $retval = '<tr onClick="showHelpEntry(\'group_by\')">
            <td colspan="2" class="text-end">
                ' . __('Group By:') . '
            </td>
            <td class="cellRightEditTable">
                <select id="columns" name="filter_data[primary_group_by]">
                    ' . html_options([
                        'options' => $filter_data['group_by_options'] ?? [],
                        'selected' => $filter_data['primary_group_by'] ?? ''
                    ]) . '
                </select>';

        if ($total >= 2) {
            $retval .= '
                <b>' . __('then:') . '</b>
                <select id="columns" name="filter_data[secondary_group_by]">
                    ' . html_options([
                        'options' => $filter_data['group_by_options'] ?? [],
                        'selected' => $filter_data['secondary_group_by'] ?? ''
                    ]) . '
                </select>';
        }

        if ($total >= 3) {
            $retval .= '
                <br>
                <select id="columns" name="filter_data[tertiary_group_by]">
                    ' . html_options([
                        'options' => $filter_data['group_by_options'] ?? [],
                        'selected' => $filter_data['tertiary_group_by'] ?? ''
                    ]) . '
                </select>';
        }

        if ($total >= 4) {
            $retval .= '
                <b>' . __('then:') . '</b>
                <select id="columns" name="filter_data[quaternary_group_by]">
                    ' . html_options([
                        'options' => $filter_data['group_by_options'] ?? [],
                        'selected' => $filter_data['quaternary_group_by'] ?? ''
                    ]) . '
                </select>';
        }

        $retval .= '
            </td>
        </tr>';

        return $retval;
    }
}

if (!function_exists('html_report_save')) {
    /**
     * Generates HTML for a report save form.
     *
     * @param array $params Parameters including generic_data, object, button_prefix, onclick, action_element_id
     * @return string HTML output
     */
    function html_report_save($params)
    {
        $generic_data = $params['generic_data'] ?? [];
        $object = $params['object'] ?? '';
        $submit_button_prefix = $params['button_prefix'] ?? 'action';
        $submit_button_prefix = $submit_button_prefix . ':';
        $onclick_html = $params['onclick'] ?? '';
        $action_element_id = $params['action_element_id'] ?? 'action';

        $retval = '<tr>
            <td colspan="2" class="' . is_valid(['object' => $object, 'label' => 'name', 'value' => 'cellLeftEditTable']) . '">
                ' . __('Name:') . '
            </td>
            <td class="cellRightEditTable">
                <input type="text" name="generic_data[name]" value="' . ($generic_data['name'] ?? '') . '">
                <select id="generic_id" name="generic_data[id]" onChange="' . $onclick_html . '; this.form.target = \'_self\';document.getElementById(\'' . $action_element_id . '\').name = \'' . $submit_button_prefix . 'load\';document.getElementById(\'' . $action_element_id . '\').value = \'Load\'; this.form.submit()">
                    ' . html_options([
                        'options' => $generic_data['saved_report_options'] ?? [],
                        'selected' => $generic_data['id'] ?? ''
                    ]) . '
                </select>
                ' . __('Default:') . ' <input type="checkbox" class="checkbox" name="generic_data[is_default]" value="1">
                <input type="BUTTON" name="' . $submit_button_prefix . 'action" value="' . __('Save') . '" onClick="selectAllReportCriteria(); ' . $onclick_html . '; this.form.target = \'_self\';document.getElementById(\'' . $action_element_id . '\').name = \'' . $submit_button_prefix . 'save\'; document.getElementById(\'' . $action_element_id . '\').value = \'Save\'; this.form.submit()">
                <input type="BUTTON" name="' . $submit_button_prefix . 'action" value="' . __('Delete') . '" onClick="' . $onclick_html . '; this.form.target = \'_self\';document.getElementById(\'' . $action_element_id . '\').name = \'' . $submit_button_prefix . 'delete\'; document.getElementById(\'' . $action_element_id . '\').value = \'Delete\'; this.form.submit()">
            </td>
        </tr>';

        return $retval;
    }
}

if (!function_exists('html_report_sort')) {
    /**
     * Generates HTML for a report sort selection.
     *
     * @param array $params Parameters including filter_data
     * @return string HTML output
     */
    function html_report_sort($params)
    {
        $filter_data = $params['filter_data'] ?? [];

        $retval = '<tr onClick="showHelpEntry(\'sort\')">
            <td colspan="2" class="text-end">
                ' . __('Sort By:') . '
            </td>
            <td class="cellRightEditTable">
                <select id="columns" name="filter_data[primary_sort]">
                    ' . html_options([
                        'options' => $filter_data['sort_options'] ?? [],
                        'selected' => $filter_data['primary_sort'] ?? ''
                    ]) . '
                </select>
                <select id="columns" name="filter_data[primary_sort_dir]">
                    ' . html_options([
                        'options' => $filter_data['sort_direction_options'] ?? [],
                        'selected' => $filter_data['primary_sort_dir'] ?? ''
                    ]) . '
                </select>
                <b>' . __('then:') . '</b>
                <select id="columns" name="filter_data[secondary_sort]">
                    ' . html_options([
                        'options' => $filter_data['sort_options'] ?? [],
                        'selected' => $filter_data['secondary_sort'] ?? ''
                    ]) . '
                </select>
                <select id="columns" name="filter_data[secondary_sort_dir]">
                    ' . html_options([
                        'options' => $filter_data['sort_direction_options'] ?? [],
                        'selected' => $filter_data['secondary_sort_dir'] ?? ''
                    ]) . '
                </select>
            </td>
        </tr>';

        return $retval;
    }
}

