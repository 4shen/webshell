<?php

/**
 * @package    Grav\Common\Page
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Page\Medium;

use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
use Grav\Common\Data\Data;
use Grav\Common\Data\Blueprint;
use Grav\Common\Media\Interfaces\MediaObjectInterface;
use Grav\Common\Utils;

/**
 * Class Medium
 * @package Grav\Common\Page\Medium
 *
 * @property string $mime
 */
class Medium extends Data implements RenderableInterface, MediaObjectInterface
{
    use ParsedownHtmlTrait;

    /**
     * @var string
     */
    protected $mode = 'source';

    /**
     * @var Medium
     */
    protected $_thumbnail = null;

    /**
     * @var array
     */
    protected $thumbnailTypes = ['page', 'default'];

    protected $thumbnailType = null;

    /**
     * @var Medium[]
     */
    protected $alternatives = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $styleAttributes = [];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $medium_querystring = [];

    protected $timestamp;

    /**
     * Construct.
     *
     * @param array $items
     * @param Blueprint $blueprint
     */
    public function __construct($items = [], Blueprint $blueprint = null)
    {
        parent::__construct($items, $blueprint);

        if (Grav::instance()['config']->get('system.media.enable_media_timestamp', true)) {
            $this->timestamp = Grav::instance()['cache']->getKey();
        }

        $this->def('mime', 'application/octet-stream');
        $this->reset();
    }

    public function __clone()
    {
        // Allows future compatibility as parent::__clone() works.
    }

    /**
     * Create a copy of this media object
     *
     * @return Medium
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Return just metadata from the Medium object
     *
     * @return Data
     */
    public function meta()
    {
        return new Data($this->items);
    }

    /**
     * Check if this medium exists or not
     *
     * @return bool
     */
    public function exists()
    {
        $path = $this->get('filepath');
        if (file_exists($path)) {
            return true;
        }
        return false;
    }

    /**
     * Get file modification time for the medium.
     *
     * @return int|null
     */
    public function modified()
    {
        $path = $this->get('filepath');

        if (!file_exists($path)) {
            return null;
        }

        return filemtime($path) ?: null;
    }

    /**
     * @return int
     */
    public function size()
    {
        $path = $this->get('filepath');

        if (!file_exists($path)) {
            return 0;
        }

        return filesize($path) ?: 0;
    }

    /**
     * Set querystring to file modification timestamp (or value provided as a parameter).
     *
     * @param string|int|null $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp = null)
    {
        $this->timestamp = (string)($timestamp ?? $this->modified());

        return $this;
    }

    /**
     * Returns an array containing just the metadata
     *
     * @return array
     */
    public function metadata()
    {
        return $this->metadata;
    }

    /**
     * Add meta file for the medium.
     *
     * @param string $filepath
     */
    public function addMetaFile($filepath)
    {
        $this->metadata = (array)CompiledYamlFile::instance($filepath)->content();
        $this->merge($this->metadata);
    }

    /**
     * Add alternative Medium to this Medium.
     *
     * @param int|float $ratio
     * @param Medium $alternative
     */
    public function addAlternative($ratio, Medium $alternative)
    {
        if (!is_numeric($ratio) || $ratio === 0) {
            return;
        }

        $alternative->set('ratio', $ratio);
        $width = $alternative->get('width');

        $this->alternatives[$width] = $alternative;
    }

    /**
     * Return string representation of the object (html).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }

    /**
     * Return PATH to file.
     *
     * @param bool $reset
     * @return string path to file
     */
    public function path($reset = true)
    {
        if ($reset) {
            $this->reset();
        }

        return $this->get('filepath');
    }

    /**
     * Return the relative path to file
     *
     * @param bool $reset
     * @return mixed
     */
    public function relativePath($reset = true)
    {
        $output = preg_replace('|^' . preg_quote(GRAV_ROOT, '|') . '|', '', $this->get('filepath'));

        $locator = Grav::instance()['locator'];
        if ($locator->isStream($output)) {
            $output = $locator->findResource($output, false);
        }

        if ($reset) {
            $this->reset();
        }

        return str_replace(GRAV_ROOT, '', $output);
    }

    /**
     * Return URL to file.
     *
     * @param bool $reset
     * @return string
     */
    public function url($reset = true)
    {
        $output = preg_replace('|^' . preg_quote(GRAV_ROOT, '|') . '|', '', $this->get('filepath'));

        $locator = Grav::instance()['locator'];
        if ($locator->isStream($output)) {
            $output = $locator->findResource($output, false);
        }

        if ($reset) {
            $this->reset();
        }

        return trim(Grav::instance()['base_url'] . '/' . $this->urlQuerystring($output), '\\');
    }

    /**
     * Get/set querystring for the file's url
     *
     * @param  string  $querystring
     * @param  bool $withQuestionmark
     * @return string
     */
    public function querystring($querystring = null, $withQuestionmark = true)
    {
        if (null !== $querystring) {
            $this->medium_querystring[] = ltrim($querystring, '?&');
            foreach ($this->alternatives as $alt) {
                $alt->querystring($querystring, $withQuestionmark);
            }
        }

        if (empty($this->medium_querystring)) {
            return '';
        }

        // join the strings
        $querystring = implode('&', $this->medium_querystring);
        // explode all strings
        $query_parts = explode('&', $querystring);
        // Join them again now ensure the elements are unique
        $querystring = implode('&', array_unique($query_parts));

        return $withQuestionmark ? ('?' . $querystring) : $querystring;
    }

    /**
     * Get the URL with full querystring
     *
     * @param string $url
     * @return string
     */
    public function urlQuerystring($url)
    {
        $querystring = $this->querystring();
        if (isset($this->timestamp) && !Utils::contains($querystring, $this->timestamp)) {
            $querystring = empty($querystring) ? ('?' . $this->timestamp) : ($querystring . '&' . $this->timestamp);
        }

        return ltrim($url . $querystring . $this->urlHash(), '/');
    }

    /**
     * Get/set hash for the file's url
     *
     * @param  string  $hash
     * @param  bool $withHash
     * @return string
     */
    public function urlHash($hash = null, $withHash = true)
    {
        if ($hash) {
            $this->set('urlHash', ltrim($hash, '#'));
        }

        $hash = $this->get('urlHash', '');

        return $withHash && !empty($hash) ? '#' . $hash : $hash;
    }

    /**
     * Get an element (is array) that can be rendered by the Parsedown engine
     *
     * @param  string  $title
     * @param  string  $alt
     * @param  string  $class
     * @param  string  $id
     * @param  bool $reset
     * @return array
     */
    public function parsedownElement($title = null, $alt = null, $class = null, $id = null, $reset = true)
    {
        $attributes = $this->attributes;

        $style = '';
        foreach ($this->styleAttributes as $key => $value) {
            if (is_numeric($key)) // Special case for inline style attributes, refer to style() method
                $style .= $value;
            else
                $style .= $key . ': ' . $value . ';';
        }
        if ($style) {
            $attributes['style'] = $style;
        }

        if (empty($attributes['title'])) {
            if (!empty($title)) {
                $attributes['title'] = $title;
            } elseif (!empty($this->items['title'])) {
                $attributes['title'] = $this->items['title'];
            }
        }

        if (empty($attributes['alt'])) {
            if (!empty($alt)) {
                $attributes['alt'] = $alt;
            } elseif (!empty($this->items['alt'])) {
                $attributes['alt'] = $this->items['alt'];
            } elseif (!empty($this->items['alt_text'])) {
                $attributes['alt'] = $this->items['alt_text'];
            } else {
                $attributes['alt'] = '';
            }
        }

        if (empty($attributes['class'])) {
            if (!empty($class)) {
                $attributes['class'] = $class;
            } elseif (!empty($this->items['class'])) {
                $attributes['class'] = $this->items['class'];
            }
        }

        if (empty($attributes['id'])) {
            if (!empty($id)) {
                $attributes['id'] = $id;
            } elseif (!empty($this->items['id'])) {
                $attributes['id'] = $this->items['id'];
            }
        }

        switch ($this->mode) {
            case 'text':
                $element = $this->textParsedownElement($attributes, false);
                break;
            case 'thumbnail':
                $element = $this->getThumbnail()->sourceParsedownElement($attributes, false);
                break;
            case 'source':
                $element = $this->sourceParsedownElement($attributes, false);
                break;
            default:
                $element = [];
        }

        if ($reset) {
            $this->reset();
        }

        $this->display('source');

        return $element;
    }

    /**
     * Parsedown element for source display mode
     *
     * @param  array $attributes
     * @param  bool $reset
     * @return array
     */
    protected function sourceParsedownElement(array $attributes, $reset = true)
    {
        return $this->textParsedownElement($attributes, $reset);
    }

    /**
     * Parsedown element for text display mode
     *
     * @param  array $attributes
     * @param  bool $reset
     * @return array
     */
    protected function textParsedownElement(array $attributes, $reset = true)
    {
        $text = empty($attributes['title']) ? empty($attributes['alt']) ? $this->get('filename') : $attributes['alt'] : $attributes['title'];

        $element = [
            'name' => 'p',
            'attributes' => $attributes,
            'text' => $text
        ];

        if ($reset) {
            $this->reset();
        }

        return $element;
    }

    /**
     * Reset medium.
     *
     * @return $this
     */
    public function reset()
    {
        $this->attributes = [];
        return $this;
    }

    /**
     * Switch display mode.
     *
     * @param string $mode
     *
     * @return $this
     */
    public function display($mode = 'source')
    {
        if ($this->mode === $mode) {
            return $this;
        }


        $this->mode = $mode;

        return $mode === 'thumbnail' ? ($this->getThumbnail() ? $this->getThumbnail()->reset() : null) : $this->reset();
    }

    /**
     * Helper method to determine if this media item has a thumbnail or not
     *
     * @param string $type;
     *
     * @return bool
     */
    public function thumbnailExists($type = 'page')
    {
        $thumbs = $this->get('thumbnails');
        if (isset($thumbs[$type])) {
            return true;
        }
        return false;
    }

    /**
     * Switch thumbnail.
     *
     * @param string $type
     *
     * @return $this
     */
    public function thumbnail($type = 'auto')
    {
        if ($type !== 'auto' && !\in_array($type, $this->thumbnailTypes, true)) {
            return $this;
        }

        if ($this->thumbnailType !== $type) {
            $this->_thumbnail = null;
        }

        $this->thumbnailType = $type;

        return $this;
    }


    /**
     * Turn the current Medium into a Link
     *
     * @param  bool $reset
     * @param  array  $attributes
     * @return Link
     */
    public function link($reset = true, array $attributes = [])
    {
        if ($this->mode !== 'source') {
            $this->display('source');
        }

        foreach ($this->attributes as $key => $value) {
            empty($attributes['data-' . $key]) && $attributes['data-' . $key] = $value;
        }

        empty($attributes['href']) && $attributes['href'] = $this->url();

        return new Link($attributes, $this);
    }

    /**
     * Turn the current Medium into a Link with lightbox enabled
     *
     * @param  int  $width
     * @param  int  $height
     * @param  bool $reset
     * @return Link
     */
    public function lightbox($width = null, $height = null, $reset = true)
    {
        $attributes = ['rel' => 'lightbox'];

        if ($width && $height) {
            $attributes['data-width'] = $width;
            $attributes['data-height'] = $height;
        }

        return $this->link($reset, $attributes);
    }

    /**
     * Add a class to the element from Markdown or Twig
     * Example: ![Example](myimg.png?classes=float-left) or ![Example](myimg.png?classes=myclass1,myclass2)
     *
     * @return $this
     */
    public function classes()
    {
        $classes = func_get_args();
        if (!empty($classes)) {
            $this->attributes['class'] = implode(',', $classes);
        }

        return $this;
    }

    /**
     * Add an id to the element from Markdown or Twig
     * Example: ![Example](myimg.png?id=primary-img)
     *
     * @param string $id
     * @return $this
     */
    public function id($id)
    {
        if (is_string($id)) {
            $this->attributes['id'] = trim($id);
        }

        return $this;
    }

    /**
     * Allows to add an inline style attribute from Markdown or Twig
     * Example: ![Example](myimg.png?style=float:left)
     *
     * @param string $style
     * @return $this
     */
    public function style($style)
    {
        $this->styleAttributes[] = rtrim($style, ';') . ';';
        return $this;
    }

    /**
     * Allow any action to be called on this medium from twig or markdown
     *
     * @param string $method
     * @param mixed $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $qs = $method;
        if (\count($args) > 1 || (\count($args) === 1 && !empty($args[0]))) {
            $qs .= '=' . implode(',', array_map(function ($a) {
                if (is_array($a)) {
                    $a = '[' . implode(',', $a) . ']';
                }
                return rawurlencode($a);
            }, $args));
        }

        if (!empty($qs)) {
            $this->querystring($this->querystring(null, false) . '&' . $qs);
        }

        return $this;
    }

    /**
     * Get the thumbnail Medium object
     *
     * @return ThumbnailImageMedium
     */
    protected function getThumbnail()
    {
        if (!$this->_thumbnail) {
            $types = $this->thumbnailTypes;

            if ($this->thumbnailType !== 'auto') {
                array_unshift($types, $this->thumbnailType);
            }

            foreach ($types as $type) {
                $thumb = $this->get('thumbnails.' . $type, false);

                if ($thumb) {
                    $thumb = $thumb instanceof ThumbnailImageMedium ? $thumb : MediumFactory::fromFile($thumb, ['type' => 'thumbnail']);
                    $thumb->parent = $this;
                }

                if ($thumb) {
                    $this->_thumbnail = $thumb;
                    break;
                }
            }
        }

        return $this->_thumbnail;
    }

}
