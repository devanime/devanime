<?php

namespace Backstage\View;

use Backstage\Models\PostBase;

/**
 * Class Link
 * @package Backstage\View
 *
 * Immutable class representing an html <a> tag, its attributes and its content
 *
 * @method target($value)
 * @method class($value)
 */
class Link extends Element
{
    protected $tag = 'a';
    protected $url;

    public function __construct(string $url, string $content = '', array $attributes = [])
    {
        if ($url) {
            $attributes['href'] = $url;
        }
        parent::__construct($content, $attributes);
    }

    public static function createFromField($field)
    {
        if (!(is_array($field) && isset($field['url']))) {
            return null;
        }
        $url = (string) $field['url'] ?? '';
        $content = (string) $field['title'] ?? '';
        $attributes = ['target' => $field['target'] ?? ''];
        return new static($url, $content, array_filter($attributes));
    }

    public static function createFromPostPermalink(PostBase $post)
    {
        return new static($post->permalink(), $post->title());
    }

}