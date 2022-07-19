<?php

namespace DevAnime\Models;

class PostFactory
{
    const BASE_CLASS = PostBase::class;
    const DEFAULT_CLASS = PostGeneric::class;
    protected static $models = [];

    public static function create($post = null): PostBase
    {
        $post = get_post($post);
        $model_class = static::$models[$post->post_type] ?? static::DEFAULT_CLASS;
        return new $model_class($post);
    }

    public static function registerPostModel($model_class)
    {
        if (!is_a($model_class, static::BASE_CLASS, true)) {
            throw new \InvalidArgumentException('Invalid post factory registration');
        }
        $post_type = $model_class::POST_TYPE;
        static::$models[$post_type] = $model_class;
    }
}