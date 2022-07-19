<?php
/**
 * Class ACF_Bidirectional_Relationship
 * @package DevAnime\Vendor
 * @author  DevAnime
 * @version 1.0
 */

namespace DevAnime\Vendor\AdvancedCustomFields;

/**
 * Converts the default ACF storage for a post relationship field
 * to multiple meta entries instead of a serialized array
 */
class BidirectionalRelationship
{
    protected $field_name;
    protected $combined_field_name;

    protected $update_lock = false;

    /**
     * ACF_Bidirectional_Relationship constructor.
     * @param string $field_name
     */
    public function __construct($field_name)
    {
        $this->field_name = $field_name;
        $this->combined_field_name = $this->field_name . '_combined';
        add_filter('update_post_metadata', [$this, 'updatePostMetadata'], 10, 5);
        add_filter('acf/load_field/name=' . $field_name, [$this, 'loadRelatedField']);
        add_filter('acf/format_value/name=' . $field_name, [$this, 'getRelatedValue'], 10, 2);
    }

    /**
     * Get array of values instead of single value
     *
     * @param mixed $value
     * @param int $post_id
     * @return mixed
     */
    public function getRelatedValue($value = null, $post_id = null)
    {
        if (is_null($post_id)) {
            $post_id = get_the_ID();
        }
        $combined = get_post_meta($post_id, $this->combined_field_name, true);
        return !empty($combined) ? $combined : get_post_meta($post_id, $this->field_name);
    }

    /**
     * Replaces value when field loads
     *
     * @param array $field
     * @return array
     */
    public function loadRelatedField($field)
    {
        $field['value'] = $this->getRelatedValue();
        return $field;
    }

    /**
     * If correct field conditions, update with multiple entry values and short-circuit
     *
     * @param $check
     * @param $object_id
     * @param $meta_key
     * @param $meta_value
     * @param $prev_value
     * @return bool
     */
    public function updatePostMetadata($check, $object_id, $meta_key, $meta_value, $prev_value)
    {
        if ($meta_key == $this->field_name && !$this->update_lock) {
            $value = $this->sanitizeValue($meta_value, $object_id);
            $this->updateValues($value, $object_id);
            update_metadata( 'post', $object_id, $this->combined_field_name, $value );
            $check = false;
        }
        return $check;
    }

    /**
     * Prepare array of value diffs and run metadata crud functions
     *
     * @param string|array $value
     * @param int $post_id
     */
    protected function updateValues($value, $post_id)
    {
        list($existing_diff, $new_diff) = $this->getPreviousValueDiffs($value, $post_id);

        /* pop both arrays and update entry with new value, until one array runs out */
        while (count($existing_diff) && count($new_diff)) {
            $this->update_lock = true;
            update_metadata('post', $post_id, $this->field_name, array_pop($new_diff), array_pop($existing_diff));
            $this->update_lock = false;
        }

        /* remove remaining existing values */
        foreach ($existing_diff as $prev_value) {
            delete_metadata('post', $post_id, $this->field_name, $prev_value);
        }

        /* add remaining new values */
        foreach ($new_diff as $new_value) {
            add_metadata('post', $post_id, $this->field_name, $new_value, false);
        }
    }

    /**
     * Ensures array with correct values
     *
     * @param string|array $value
     * @param int $post_id
     *
     * @return array
     */
    protected function sanitizeValue($value, $post_id)
    {
        //corrects for ACF only copying single value for revisions
        if ($the_post = wp_is_post_revision($post_id)) {
            $value = get_post_meta($the_post, $this->field_name);
        }
        return array_unique(array_filter((array) $value));
    }

    /**
     * Returns two-way diff array
     * @param array $value
     * @param int $post_id
     * @return array
     */
    protected function getPreviousValueDiffs(array $value, $post_id)
    {
        $previous_values = get_post_meta($post_id, $this->field_name);
        $existing_diff = array_diff($previous_values, $value);
        $new_diff = array_diff($value, $previous_values);
        return [$existing_diff, $new_diff];
    }
}