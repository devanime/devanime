<?php
/**
 * Class ACFIntegration
 * @package Backstage\Vendor\AdvancedCustomFields
 * @author  Jeremy Strom <jstrom@situationinteractive.com>
 * @version 1.0
 */

namespace Backstage\Vendor\AdvancedCustomFields;

use Backstage\View\Link;

class ACFIntegration
{

    public function __construct()
    {
        add_filter( 'acf/load_value', [$this, 'enforceOrder'], 10, 3);
        add_action('init', [$this, 'unhookEnforce']);
        add_filter('acf/location/rule_values/post_type', [$this, 'locationRules']);
        add_action('acf/include_field_types', function() {
            if (did_action('after_setup_theme')) {
                return;
            }
            $acf_json = acf()->json;
            remove_action('acf/include_fields', [$acf_json, 'include_json_folders']);
            add_action('init', [$acf_json, 'include_json_folders'], 1);
        });
        add_shortcode('acf-option', [$this, 'optionShortcode']);

    }

    public function enforceOrder($value, $post_id, $field)
    {
        if (! apply_filters('backstage/enforce_acf_order', true)) {
            return $value;
        }
        if (! did_action('init')) {
            throw new \Exception('get_field called before init');
        }
        return $value;
    }

    public function unhookEnforce()
    {
        remove_filter('acf/load_value', [$this, 'enforceOrder'], 10);
    }

    public function locationRules($choices)
    {
        $choices['backstage_disable'] = 'None';

        return $choices;
    }

    public function optionShortcode($atts, $content = null)
    {
        if (empty($atts[0])) {
            return $content;
        }
        $field = array_shift($atts);
        return $this->optionShortcodeContent($field, $atts, $content) ?: $content;
    }

    protected function optionShortcodeContent(string $field, array $atts = [], $content = null)
    {
        $value = get_field($field, 'option', true);

        //automatically convert links
        if ($link = Link::createFromField($value)) {
            $link = $link->attributes($atts);
            if (!empty($content)) {
                $link = $link->content($content);
            }
            $value = $link;
        }

        $value = apply_filters('backstage/acf-option-shortcode/' . $field, $value, $atts, $content);
        $value = apply_filters('backstage/acf-option-shortcode', $value, $field, $atts, $content);

        if( is_array($value) ) {
            $value = @implode( ', ', $value );
        }

        return $value;
    }
}
