<?php


namespace Backstage\Models;


class Page extends PostBase
{
    const POST_TYPE = 'page';

    public function parent()
    {
        return $this->post()->post_parent ? static::create($this->post()->post_parent) : null;
    }

    public function template()
    {
        return get_page_template_slug($this->ID) ?: 'page.php';
    }
}