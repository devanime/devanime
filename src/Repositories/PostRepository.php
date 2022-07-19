<?php

namespace DevAnime\Repositories;

use DevAnime\Models\Field;
use DevAnime\Models\PostBase;
use DevAnime\Models\PostCollection;

class PostRepository implements Repository
{

    /**
     * Post_Base class name bound to this repository, override in subclasses for
     *
     * @var string $model_class
     */
    protected $model_class = PostBase::class;
    protected $field_ids = [];
    protected $exclude_current_singular_post = true;
    protected $use_post_collection = false;
    protected $collection_class = PostCollection::class;

    public function findById($id)
    {
        return call_user_func([$this->model_class, 'create'], $id);
    }

    public function findOne(array $query)
    {
        $query['posts_per_page'] = 1;
        $posts = $this->find($query);
        return $posts[0] ?? null;
    }

    public function findOneBySlug($name)
    {
        return $this->findOne(compact('name'));
    }

    public function findOneByAuthor($author)
    {
        if (is_object($author)) $author = $author->ID;
        return $this->findOne(compact('author'));
    }

    public function findAllByAuthor($author)
    {
        if (is_object($author)) $author = $author->ID;
        return $this->find(compact('author'));
    }

    public function findAll($any_status = false)
    {
        $status = $any_status ? 'any' : 'publish';
        return $this->find(['posts_per_page' => -1, 'post_status' => $status]);
    }

    public function findAllDrafts()
    {
        return $this->find(['posts_per_page' => -1, 'post_status' => 'draft']);
    }

    public function find(array $query)
    {
        $model_class = $this->model_class;
        $query = $this->maybeExcludeCurrentSingularPost($query);
        $posts = array_filter($model_class::getPosts($query));
        if ($this->use_post_collection) {
            $collection_class = $this->collection_class;
            return new $collection_class($posts);
        }
        return $posts;
    }

    public function findWithIds(array $post_ids, int $count = 10)
    {
        if (empty($post_ids)) {
            return [];
        }
        return $this->find([
            'posts_per_page' => $count,
            'post__in' => $post_ids,
            'orderby' => 'post__in'
        ]);
    }

    /**
     * @param array $term_ids
     * @param int $count
     * @param string $taxonomy
     * @param array $excluded_post_ids
     * @return array
     */
    public function findWithTermIds(array $term_ids, string $taxonomy = 'category', $count = 10, array $excluded_post_ids = [])
    {
        if (empty($term_ids)) {
            return [];
        }
        return $this->find([
            'posts_per_page' => $count,
            'post__not_in' => $excluded_post_ids,
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'terms' => $term_ids,
                    'field' => 'term_id',
                    'compare' => 'IN'
                ]
            ]
        ]);
    }

    public function add($post)
    {
        $this->checkBoundModelType($post);
        $post_arr = (array)$post->post();
        $post_id = $post->ID ? wp_update_post($post_arr, true) : wp_insert_post($post_arr, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        $post->ID = $post_id;
        foreach ($post->allTermIdsByTaxonomy() as $taxonomy => $term_ids) {
            wp_set_object_terms($post->ID, $term_ids, $taxonomy);
        }
        $this->addFeaturedImage($post);
        $this->addFields($post);
        $post->reset(true);
        return true;
    }

    public function remove($post)
    {
        $this->checkBoundModelType($post);
        $result = wp_delete_post($post->ID);
        return !empty($result);
    }

    protected function addFeaturedImage(PostBase $post)
    {
        $featured_image = $post->featuredImage();
        if ($featured_image) {
            set_post_thumbnail($post->ID, $featured_image->ID);
        } else {
            delete_post_thumbnail($post->ID);
        }
    }

    protected function addFields(PostBase $post)
    {
        foreach ($post->fields(false) as $key => $value) {
            $field_ids = $this->getFieldIds($post);
            $value = $this->prepareFieldValue($key, $value);
            if (isset($field_ids[$key])) {
                update_field($field_ids[$key], $value, $post->ID);
            } else {
                update_post_meta($post->ID, $key, $value);
            }
        }
    }

    protected function getFieldIds(PostBase $post)
    {
        if (empty($this->field_ids)) {
            foreach (acf_get_field_groups(['post_type' => $post::POST_TYPE]) as $group) {
                foreach (acf_get_fields($group) as $field) {
                    $this->field_ids[$field['name']] = $field['key'];
                }
            }
        }
        return $this->field_ids;
    }

    protected function checkBoundModelType(PostBase $post)
    {
        if (!is_a($post, $this->model_class)) {
            throw new \InvalidArgumentException('PostBase parameter is not a :' . $this->model_class);
        }
    }

    protected function prepareFieldValue($key, $value)
    {
        if (method_exists($this, "prepare_$key")) {
            $value = $this->{"prepare_$key"}($value);
        }
        if ($value instanceof Field) {
            $value = $value->getValue();
        }
        return $value;
    }

    protected function maybeExcludeCurrentSingularPost($query)
    {
        global $wp_query;
        if (!$wp_query) {
            return $query;
        }
        $model_class = $this->model_class;
        $post_obj = $wp_query->get_queried_object();
        if (
            $this->exclude_current_singular_post &&
            $wp_query->is_singular &&
            $post_obj && $post_obj->post_type == $model_class::POST_TYPE
        ) {
            if (empty($query['post__not_in'])) {
                $query['post__not_in'] = [];
            }
            if (!is_array($query['post__not_in'])) {
                $query['post__not_in'] = [$query['post__not_in']];
            }
            array_push($query['post__not_in'], get_the_ID());
        }
        return $query;
    }

}