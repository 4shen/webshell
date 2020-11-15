<?php

namespace spouts\youtube;

/**
 * Spout for fetching a YouTube rss feed
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 * @copywork   Arndt Staudinger <info@clucose.com> April 2013
 */
class youtube extends \spouts\rss\feed {
    /** @var string name of source */
    public $name = 'YouTube: channel';

    /** @var string description of this source type */
    public $description = 'Fetch posts from a YouTube channel.';

    /** @var array configurable parameters */
    public $params = [
        'channel' => [
            'title' => 'Channel',
            'type' => 'text',
            'default' => '',
            'required' => true,
            'validation' => ['notempty']
        ]
    ];

    public function load(array $params) {
        $url = $this->getXmlUrl($params);
        parent::load(['url' => $url]);
    }

    public function getXmlUrl(array $params) {
        $channel = $params['channel'];
        if (preg_match('(^https?://www.youtube.com/channel/([a-zA-Z0-9_-]+)$)', $params['channel'], $matched)) {
            $channel = $matched[1];
            $channel_type = 'channel_id';
        } elseif (preg_match('(^https?://www.youtube.com/user/([a-zA-Z0-9_]+)$)', $params['channel'], $matched)) {
            $channel = $matched[1];
            $channel_type = 'username';
        } elseif (preg_match('(^https?://www.youtube.com/([a-zA-Z0-9_]+)$)', $params['channel'], $matched)) {
            $channel = $matched[1];
            $channel_type = 'username';
        } else {
            $channel_type = 'username';
        }

        if ($channel_type === 'username') {
            return 'https://www.youtube.com/feeds/videos.xml?user=' . $channel;
        } else {
            return 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $channel;
        }
    }

    public function getThumbnail() {
        if ($this->items === false || $this->valid() === false) {
            return null;
        }

        $item = current($this->items);

        // search enclosures (media tags)
        if (count(@$item->get_enclosures()) > 0) {
            if (@$item->get_enclosure(0)->get_thumbnail()) {
                // thumbnail given
                return @$item->get_enclosure(0)->get_thumbnail();
            } elseif (@$item->get_enclosure(0)->get_link()) {
                // link given
                return @$item->get_enclosure(0)->get_link();
            }
        } else { // no enclosures: search image link in content
            $image = \helpers\Image::findFirstImageSource(@$item->get_content());
            if ($image !== null) {
                return $image;
            }
        }

        return null;
    }
}
