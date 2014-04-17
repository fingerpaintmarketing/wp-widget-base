<?php

/**
 * Contains a widget base class for handling configurable widgets.
 *
 * PHP Version 5.3+
 *
 * @category Wordpress
 * @package  Widgets
 * @author   Kevin Fodness <kevin@kevinfodness.com>, Beau Watson <bwatson@fingerpaintmarketing.com>
 * @license  GPLv3
 * @link     http://www.kevinfodness.com
 * @version  1.0.1
 */

/**
 * A widget base class for handling configurable widgets.
 * 
 * @category Wordpress
 * @package  Widgets
 * @author   Kevin Fodness <kevin@kevinfodness.com>, Beau Watson <bwatson@fingerpaintmarketing.com>
 * @license  GPLv3
 * @link     http://www.kevinfodness.com
 */
class Widget_Base extends WP_Widget
{
    /**
     * Fields listing. To be overriden by child class.
     *
     * @access protected
     * @var array
     */
    protected $fields = array();
    
    /**
     * Constructor function. Registers the widget by calling the parent constructor.
     *
     * @param string $id          The ID of the widget.
     * @param string $name        The name of the widget.
     * @param string $description The description for the widget.
     *
     * @access public
     * @return void
     */
    public function __construct($id, $name, $description)
    {
        parent::__construct(
            $id,
            __($name, 'text_domain'),
            array('description' => __($description, 'text_domain'))
        );

        /* Add action hooks. */
        add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
        add_action('admin_print_styles', array($this, 'admin_print_styles'));
    }

    /**
     * Function to retrieve form data values from the instance based on defined fields.
     *
     * @param array $instance The widget instance from WP.
     *
     * @access protected
     * @return array
     */
    protected function get_data($instance)
    {
        $data = array();
        foreach ($this->fields as $key => $field) {

            /* Construct field data from instance data for each field. */
            $data[$key]['title']      = ucwords(str_replace('-', ' ', $key)) . ':';
            $data[$key]['value']      = (isset($instance[$key])) ? esc_attr($instance[$key]) : '';
            $data[$key]['field_id']   = $this->get_field_id($key);
            $data[$key]['field_name'] = $this->get_field_name($key);

            /* Sanitize field value. */
            switch ($field['type']) {
            case 'select':
            case 'text':
                $data[$key]['value'] = esc_attr($data[$key]['value']);
                break;
            case 'textarea':
                $data[$key]['value'] = esc_textarea($data[$key]['value']);
                break;
            case 'url':
                $data[$key]['value'] = esc_url($data[$key]['value']);
                break;
            }
        }
        return $data;
    }

    /**
     * A function to print a field on the edit form.
     *
     * @param string $key  The key from the fields definition for this field.
     * @param array  $data The data array for this field.
     *
     * @access protected
     * @return void
     */
    protected function print_field($key, $data)
    {
        /* Switch to construct HTML for the field itself. */
        switch ($this->fields[$key]['type']) {
        case 'select':
            $options = '';
            foreach ($this->fields[$key]['values'] as $value => $name) {
                $options .= '<option value="' . esc_attr($value) . '"';
                if ($data['value'] === esc_attr($value)) {
                    $options .= ' selected="selected"';
                }
                $options .= '>' . esc_attr($name) . '</option>';
            }
            $field = <<<HTML
<select id="{$data['field_id']}" name="{$data['field_name']}">
    <option value="">-- Select --</option>
    {$options}
</select>
HTML;
            break;
        case 'text':
        case 'url':
            $field = <<<HTML
<input id="{$data['field_id']}" name="{$data['field_name']}" type="text" value="{$data['value']}" />
HTML;
            break;
        case 'textarea':
            $field = <<<HTML
<textarea id="{$data['field_id']}" name="{$data['field_name']}">{$data['value']}</textarea>
HTML;
            break;
        case 'checkbox':
            $field = '';
            //if we have an array ofcheckboxes, loop through them
            if (!empty($this->fields[$key]['values']) 
                && is_array($this->fields[$key]['values'])
            ) {
                //base64 decode the data, then unserialize it
                $values = unserialize(base64_decode($data['value']));
                $data["title"] .= '</label>';
                //looping through the checkboxes
                foreach ($this->fields[$key]['values'] as $value => $name) {
                    // if the current instance's value is in the values array, set it as checked
                    $checked = (!empty($values) && in_array($value,$values)) ? 'checked="checked"' : '';

                    //prep the HTML. We're cheating and closing the label early to keep the entire checkbox group from getting encloded
                    //  in the label. Then, wrap a label around the checkbox.
                    $data['title'] .= <<<HTML
<br><label><input id="{$data['field_id']}" value="{$value}" name="{$data['field_name']}[]" type="checkbox" {$checked} /> {$name}</label>
HTML;
                }
            } else {
                //if it's not an array, simply write it out
                $checked = (!empty($data['value'])) ? 'checked="checked"' : '';
                $data['title'] = <<<HTML
<input id="{$data['field_id']}" name="{$data['field_name']}" type="checkbox" {$checked} />
HTML
                . substr($data['title'], 0, -1);
            }

            break;
        case 'file':
            $field = <<<HTML
<input id="{$data['field_id']}" name="{$data['field_name']}" type="hidden" value="{$data['value']}" />
HTML;
            if (!empty($data['value'])) {
                $media = wp_get_attachment_image($data['value']);
                $field .= <<<HTML
{$media}<br />
<a href="#" class="widget-base-remove-media">Remove media</a>
HTML;
            } else {
                $field .= <<<HTML
<input class="button widget-base-add-media" type="button" value="Add Media" />
HTML;
            }
        }

        /* Print the field. */
        echo <<<HTML
<p>
    <label for="{$data['field_id']}">{$data['title']}</label><br/>
    {$field}
</p>
HTML;
    }

    /**
     * Action hook function to enqueue admin scripts used by this widget.
     *
     * @access public
     * @return void
     */
    public function admin_print_scripts()
    {
        /* Compute URL of supplemental JS file. */
        $js_url = get_template_directory_uri();
        $template_base = substr($js_url, strpos($js_url, '/', 8));
        $js_url .= substr(__DIR__, strpos(__DIR__, $template_base) + strlen($template_base)) . '/widget_base.js';

        /* Register and enqueue scripts. */
        wp_enqueue_media();
        wp_register_script('widget-base', $js_url);
        wp_enqueue_script('widget-base');
    }

    /**
     * Action hook function to enqueue admin styles used by this widget.
     *
     * @access public
     * @return void
     */
    public function admin_print_styles()
    {
        wp_enqueue_style('thickbox');
    }

    /**
     * A function to print the edit form on the back-end.
     *
     * @param array $instance The instance variables, as reported by WP.
     *
     * @access public
     * @return void
     */
    public function form($instance)
    {
        $data = $this->get_data($instance);
        foreach ($data as $key => $field) {
            $this->print_field($key, $field);
        }
    }

    /**
     * A function to process and sanitize inputs on save.
     *
     * @param array $new_instance The new save data.
     * @param array $old_instance The old save data.
     *
     * @access public
     * @return array  The modified instance data.
     */
    public function update($new_instance, $old_instance)
    {
        foreach ($this->fields as $key => $field) {
            if (!empty($new_instance[$key]) && !is_array($new_instance[$key])) {
                $instance[$key] = strip_tags($new_instance[$key]);
            } else {
                $values = array();
                foreach ($new_instance[$key] as $value) {
                    $values[] = strip_tags($value);
                }
                // base64_encode (to prevent glitches) and serialize the values
                $instance[$key] = base64_encode(serialize($values));
            }
        }

        return $instance;
    }
}
