<?php

namespace spouts\rss;

use Graby\Graby;
use helpers\FeedReader;
use helpers\Image;
use helpers\WebClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Monolog\Logger;

/**
 * Plugin for fetching the news with fivefilters Full-Text RSS
 *
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class fulltextrss extends feed {
    /** @var string name of spout */
    public $name = 'RSS Feed (with content extraction)';

    /** @var string description of this source type */
    public $description = 'Use “Graby” library to get full content of feed posts instead of partial content provided by some websites.';

    /** @var array configurable parameters */
    public $params = [
        'url' => [
            'title' => 'URL',
            'type' => 'url',
            'default' => '',
            'required' => true,
            'validation' => ['notempty']
        ],
    ];

    /** @var string tag for logger */
    private static $loggerTag = 'selfoss.graby';

    /** @var Graby */
    private $graby;

    /** @var Logger */
    private $logger;

    /** @var WebClient */
    private $webClient;

    public function __construct(FeedReader $feed, Image $imageHelper, Logger $logger, WebClient $webClient) {
        parent::__construct($feed, $imageHelper, $logger);

        $this->imageHelper = $imageHelper;
        $this->logger = $logger;
        $this->webClient = $webClient;
    }

    public function getContent() {
        $url = $this->getLink();

        if (!isset($this->graby)) {
            $this->graby = new Graby([
                'extractor' => [
                    'config_builder' => [
                        'site_config' => [\F3::get('ftrss_custom_data_dir')],
                    ],
                ],
            ], new GuzzleAdapter($this->webClient->getHttpClient()));
            $logger = $this->logger->withName(self::$loggerTag);
            $this->graby->setLogger($logger);
        }

        $this->logger->info('Extracting content for page: ' . $url);

        $response = $this->graby->fetchContent($url);

        if ($response['status'] !== 200) {
            $this->logger->error('Failed loading page');

            return '<p><strong>Failed to get web page</strong></p>' . parent::getContent();
        }

        $content = $response['html'];

        return $content;
    }

    public function getLink() {
        $url = parent::getLink();

        return self::removeTrackersFromUrl($url);
    }

    /**
     * remove trackers from url
     *
     * @author Jean Baptiste Favre
     *
     * @param string $url
     *
     * @return string url
     */
    private static function removeTrackersFromUrl($url) {
        $url = parse_url($url);

        // Next, rebuild URL
        $real_url = $url['scheme'] . '://';
        if (isset($url['user']) && isset($url['password'])) {
            $real_url .= $url['user'] . ':' . $url['password'] . '@';
        }
        $real_url .= $url['host'] . $url['path'];

        // Query string
        if (isset($url['query'])) {
            parse_str($url['query'], $q_array);
            $real_query = [];
            foreach ($q_array as $key => $value) {
                // Remove utm_* parameters
                if (strpos($key, 'utm_') === false) {
                    $real_query[] = $key . '=' . $value;
                }
            }
            $real_url .= '?' . implode('&', $real_query);
        }
        // Fragment
        if (isset($url['fragment'])) {
            // Remove xtor=RSS anchor
            if (strpos($url['fragment'], 'xtor=RSS') === false) {
                $real_url .= '#' . $url['fragment'];
            }
        }

        return $real_url;
    }
}
