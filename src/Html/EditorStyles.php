<?php


namespace Backstage\Html;


class EditorStyles
{
    protected $style_formats = [];

    protected $enable_all_heading_levels;

    public function __construct($enable_all_heading_levels = false)
    {
        $this->enable_all_heading_levels = $enable_all_heading_levels;
        add_filter('mce_buttons_2', function($buttons) {
            array_unshift($buttons, 'styleselect');
            $buttons = array_flip($buttons);
            unset($buttons['forecolor'], $buttons['outdent'], $buttons['indent']);
            return array_keys($buttons);
        });
        add_filter('tiny_mce_before_init', [$this, 'addStyleFormats']);
    }

    public function addStyleFormats($init) {
        $style_formats = apply_filters('backstage/editor-styles', $this->style_formats);
        $init['style_formats'] = json_encode(array_values($style_formats));
        if (!$this->enable_all_heading_levels) {
            $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
        }
        return $init;
    }


    public function add(string $title, string $classes, array $config = ['inline' => 'span'])
    {
        $this->style_formats[$classes] = array_merge(compact('title', 'classes'), $config);
        return $this;
    }

    public function addInline(string $title, string $classes, string $tag = 'span')
    {
        return $this->add($title, $classes, ['inline' => $tag]);
    }

    public function addBlock(string $title, string $classes, string $tag = 'p')
    {
        return $this->add($title, $classes, ['block' => $tag]);
    }

    public function addSelector(string $title, string $classes, string $selector = '*')
    {
        return $this->add($title, $classes, compact('selector'));
    }

    public function remove($key)
    {
        unset($this->style_formats[$key]);
        return $this;
    }
}