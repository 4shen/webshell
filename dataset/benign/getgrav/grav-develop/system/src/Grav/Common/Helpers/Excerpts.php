<?php

/**
 * @package    Grav\Common\Helpers
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Helpers;

use Grav\Common\Page\Interfaces\PageInterface;
use Grav\Common\Page\Markdown\Excerpts as ExcerptsObject;
use Grav\Common\Page\Medium\Medium;

class Excerpts
{
    /**
     * Process Grav image media URL from HTML tag
     *
     * @param string $html              HTML tag e.g. `<img src="image.jpg" />`
     * @param PageInterface|null $page  Page, defaults to the current page object
     * @return string                   Returns final HTML string
     */
    public static function processImageHtml($html, PageInterface $page = null)
    {
        $excerpt = static::getExcerptFromHtml($html, 'img');

        $original_src = $excerpt['element']['attributes']['src'];
        $excerpt['element']['attributes']['href'] = $original_src;

        $excerpt = static::processLinkExcerpt($excerpt, $page, 'image');

        $excerpt['element']['attributes']['src'] = $excerpt['element']['attributes']['href'];
        unset ($excerpt['element']['attributes']['href']);

        $excerpt = static::processImageExcerpt($excerpt, $page);

        $excerpt['element']['attributes']['data-src'] = $original_src;

        $html = static::getHtmlFromExcerpt($excerpt);

        return $html;
    }

    /**
     * Get an Excerpt array from a chunk of HTML
     *
     * @param string $html         Chunk of HTML
     * @param string $tag          A tag, for example `img`
     * @return array|null   returns nested array excerpt
     */
    public static function getExcerptFromHtml($html, $tag)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $images = $doc->getElementsByTagName($tag);
        $excerpt = null;

        foreach ($images as $image) {
            $attributes = [];
            foreach ($image->attributes as $name => $value) {
                $attributes[$name] = $value->value;
            }
            $excerpt = [
                'element' => [
                    'name'       => $image->tagName,
                    'attributes' => $attributes
                ]
            ];
        }

        return $excerpt;
    }

    /**
     * Rebuild HTML tag from an excerpt array
     *
     * @param array $excerpt
     * @return string
     */
    public static function getHtmlFromExcerpt($excerpt)
    {
        $element = $excerpt['element'];
        $html = '<'.$element['name'];

        if (isset($element['attributes'])) {
            foreach ($element['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }
                $html .= ' '.$name.'="'.$value.'"';
            }
        }

        if (isset($element['text'])) {
            $html .= '>';
            $html .= $element['text'];
            $html .= '</'.$element['name'].'>';
        } else {
            $html .= ' />';
        }

        return $html;
    }

    /**
     * Process a Link excerpt
     *
     * @param array $excerpt
     * @param PageInterface|null $page  Page, defaults to the current page object
     * @param string $type
     * @return mixed
     */
    public static function processLinkExcerpt($excerpt, PageInterface $page = null, $type = 'link')
    {
        $excerpts = new ExcerptsObject($page);

        return $excerpts->processLinkExcerpt($excerpt, $type);
    }

    /**
     * Process an image excerpt
     *
     * @param array $excerpt
     * @param PageInterface|null $page  Page, defaults to the current page object
     * @return array
     */
    public static function processImageExcerpt(array $excerpt, PageInterface $page = null)
    {
        $excerpts = new ExcerptsObject($page);

        return $excerpts->processImageExcerpt($excerpt);
    }

    /**
     * Process media actions
     *
     * @param Medium $medium
     * @param string|array $url
     * @param PageInterface|null $page  Page, defaults to the current page object
     * @return Medium
     */
    public static function processMediaActions($medium, $url, PageInterface $page = null)
    {
        $excerpts = new ExcerptsObject($page);

        return $excerpts->processMediaActions($medium, $url);
    }
}
