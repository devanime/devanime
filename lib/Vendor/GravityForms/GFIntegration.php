<?php
/**
 * Created by PhpStorm.
 * User: DevAnime
 * Date: 5/31/17
 * Time: 3:40 PM
 */

namespace DevAnime\Vendor\GravityForms;


class GFIntegration
{
    const CONFIRM_TRACKING_FIELD = 'confirm_tracking';
    const CONFIRM_TRACKING_LABEL = 'Confirmation Tracking';

    public function __construct()
    {
        add_action('devanime/load_stack', function() {
            add_filter('gform_cdata_open', [$this, 'wrapJsOpen']);
            add_filter('gform_cdata_close', [$this, 'wrapJsClose']);
            add_filter('gform_force_hooks_js_output', function($return) {
                if (did_action('wp_print_footer_scripts')) {
                    remove_filter('gform_cdata_open', [$this, 'wrapJsOpen']);
                    remove_filter('gform_cdata_close', [$this, 'wrapJsClose']);
                }
                return $return;
            });
            add_action('wp_head', function() {
                echo '<script>window.gFormLoadStack=window.gFormLoadStack||{};</script>';
            }, 999);
            add_filter('gform_get_form_filter', function($form_string, $form) {
                $form_string = '<script>window.gFormLoadStack[%form_id%]=[];</script>' . $form_string;
                return $this->replaceIdPlaceholder($form_string, $form);
            }, 10, 2);
            add_filter('gform_footer_init_scripts_filter', [$this, 'replaceIdPlaceholder'], 10, 2);
            if (apply_filters('devanime/defer_gform_scripts', false)) {
                add_filter('script_loader_tag', function ($tag, $handle) {
                    if (!is_admin() && 0 === strpos($handle, 'gform_')) {
                        $tag = str_replace(' src=', ' data-gform-src=', $tag);
                    }
                    return $tag;
                }, 1, 2);
            } else {
                add_action('wp_footer', function() {
                    echo "<script>jQuery.each(window.gFormLoadStack,function(k,v){window.executeLoadStack(v);});</script>";
                }, 9999);
            }


        });
        add_filter('gform_get_form_filter', [$this, 'filterFormJs']);
        add_filter('gform_footer_init_scripts_filter', [$this, 'filterFormJs']);
        add_filter('gform_tabindex', '__return_zero');
        add_filter('gform_init_scripts_footer', '__return_true');
        add_filter('gform_confirmation_anchor', '__return_false');
        add_filter('gform_confirmation', [$this, 'addConfirmationTracking'], 10, 2);
        add_filter('gform_form_settings', [$this, 'displayConfirmationTrackingField'], 10, 2);
        add_filter('gform_pre_form_settings_save', [$this, 'saveConfirmationTrackingField']);
    }

    public function wrapJsOpen($content)
    {
        return $content . 'window.gFormLoadStack[%form_id%].push(function (){';
    }

    public function wrapJsClose($content)
    {
        return '});' . $content;
    }

    public function filterFormJs($content)
    {
        return str_replace('var gf_global', 'window.gf_global', $content);
    }

    public function addConfirmationTracking($content, $form) {
        $tracking = sprintf('data-ga-confirm="%s"', rgar($form, static::CONFIRM_TRACKING_FIELD));
        return str_replace(
            "id='gform_confirmation_wrapper_",
            "$tracking id='gform_confirmation_wrapper_",
            $content
        );
    }

    public function displayConfirmationTrackingField($settings, $form)
    {
        $value = rgar($form, static::CONFIRM_TRACKING_FIELD);
        $settings['Form Basics'][static::CONFIRM_TRACKING_FIELD] = sprintf(
            '<tr><th><label for="%1%s">%2$s</label></th><td><input value="%3$s" name="%1$s" placeholder="Category, Action, Label" class="fieldwidth-3"></td></tr>',
            static::CONFIRM_TRACKING_FIELD, static::CONFIRM_TRACKING_LABEL, $value
        );
        return $settings;
    }

    public function saveConfirmationTrackingField($form)
    {
        $form[static::CONFIRM_TRACKING_FIELD] = rgpost(static::CONFIRM_TRACKING_FIELD);
        return $form;
    }

    public function replaceIdPlaceholder($form_string, $form)
    {
        return str_replace('%form_id%', "'gform_wrapper_{$form['id']}'", $form_string);
    }
}
