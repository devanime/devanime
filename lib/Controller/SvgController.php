<?php


namespace DevAnime\Controller;

/**
 * class SvgController
 * @package DevAnime\Controller
 */
class SvgController
{
    public function __construct()
    {
        add_filter('upload_mimes', [$this, 'uploadMimes']);
        add_filter('wp_check_filetype_and_ext', [$this, 'checkFileType'], 10, 3);
    }

    public function uploadMimes($mimes)
    {
        if (current_user_can('manage_options')) {
            $mimes['svg'] = 'text/html';
        }
        return $mimes;
    }

    public function checkFileType($parts, $file, $filename)
    {
        if (pathinfo($filename, PATHINFO_EXTENSION) == 'svg') {
            $contents = file_get_contents($file);
            if (
                substr($contents, 0, 4) == '<svg' && //appears to be svg markup
                false === strpos($contents, '<script') && //doesn't contain javascript
                current_user_can('manage_options') // user is administrator
            ) {
                $parts['type'] = 'image/svg+xml';
                $parts['ext'] = 'svg';
            }
        }
        return $parts;
    }
}