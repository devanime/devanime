<?php
/**
 * Class Post_Generic
 * @package DevAnime\Models
 * @author  DevAnime
 * @version 1.0
 */

namespace DevAnime\Models;


class TermGeneric extends TermBase {
    
    /**
     * Term_Generic constructor.
     *
     * @param $term
     * @param $taxonomy
     */
    public function __construct($term, $taxonomy = null) {
        if ($taxonomy) {
            $term = get_term($term, $taxonomy);
        }
        parent::__construct($term);
    }
}