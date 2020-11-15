<?php

namespace Microweber\Utils\Adapters\Template\RenderHelpers;

use Arcanedev\SeoHelper\Entities\Webmasters;
use Arcanedev\SeoHelper\Entities\Analytics;


class TemplateMetaTagsRenderer
{
    /** @var \Microweber\Application */
    public $app;

    public function __construct($app = null)
    {
        $this->app = $app;
    }

    public function render($params)
    {

        if (isset($params['layout'])) {
            $layout = $params['layout'];
            $meta_content_id = 0;
            $meta_category_id = 0;
            $l = $layout;
            $meta = array();
            $meta['content_image'] = '';
            $meta['description'] = '';
            if (is_home()) {
                $meta['content_url'] = site_url();
            } else {
                $meta['content_url'] = $this->app->url_manager->current(1);
            }
            $meta['og_description'] = $this->app->option_manager->get('website_description', 'website');
            $meta['og_type'] = 'website';
            if (isset($params['content_id']) and $params['content_id']) {
                $meta_content_id = $params['content_id'];
            } else {
                $meta_content_id = PAGE_ID;
                if (CONTENT_ID > 0) {
                    $meta_content_id = CONTENT_ID;
                }
            }
            if(POST_ID == 0){

                if (isset($params['category_id']) and $params['category_id']) {
                    $meta_category_id = $params['category_id'];
                } else {
                     if (CATEGORY_ID > 0) {
                         $meta_category_id = CATEGORY_ID;
                    }
                }
            }


            $meta_tags_override = array();
            $event_data = array();
            if ($meta_category_id > 0) {
                $event_data['rel_id'] = $meta_category_id;
                $event_data['rel_type'] = 'category';
            } else if ($meta_content_id > 0) {
                $event_data['rel_id'] = $meta_content_id;
                $event_data['rel_type'] = 'content';
            }

            $override = $this->app->event_manager->trigger('mw.meta_tags.render', $event_data);
            if ($override and is_array($override) && !empty($override)) {
                foreach ($override as $override_items){
                    if(is_array($override_items)){
                    $meta_tags_override = array_merge($meta_tags_override,$override_items);
                    }

                }
            }


            if ($meta_category_id > 0) {
                $meta_category_data = $this->app->category_manager->get_by_id($meta_category_id);

                $meta['title'] = $meta_category_data['title'];
                $meta['description'] = $meta_category_data['description'];
                if (isset($meta_category_data['category_meta_title']) and $meta_category_data['category_meta_title'] != '') {
                    $meta['title'] = $meta_category_data['category_meta_title'];
                }
                if (isset($meta_category_data['category_meta_description']) and $meta_category_data['category_meta_description'] != '') {
                    $meta['description'] = $meta_category_data['category_meta_description'];
                }

                if (isset($meta_category_data['category_meta_keywords']) and $meta_category_data['category_meta_keywords'] != '') {
                    $meta['content_meta_keywords'] = $meta_category_data['category_meta_keywords'];
                }
                $meta['og_description'] = $meta['description'];

                $content_image = $this->app->media_manager->get_picture($meta_category_id,'category');
                if ($content_image) {
                    $meta['content_image'] = $content_image;
                    $meta['og_image'] = $content_image;
                 }
            } else if ($meta_content_id > 0) {
                $meta = $this->app->content_manager->get_by_id($meta_content_id);
                $content_image = $this->app->media_manager->get_picture($meta_content_id);
                if ($content_image) {
                    $meta['content_image'] = $content_image;
                } else {
                    $meta['content_image'] = '';
                    $cont_id = get_content_by_id($meta_content_id);

                    if ($cont_id and isset($cont_id['content'])) {
                        $img = $this->app->media_manager->get_first_image_from_html(html_entity_decode($cont_id['content']));

                        if ($img != false) {
                            $surl = $this->app->url_manager->site();
                            $img = $this->app->format->replace_once('{SITE_URL}', $surl, $img);
                            $meta['content_image'] = $img;
                        }
                    }


                }
                $meta['content_url'] = $this->app->content_manager->link($meta_content_id);
                if (isset($meta['content_type'])) {
                    $meta['og_type'] = $meta['content_type'];
                    if ($meta['content_type'] == 'post') {
                        $meta['og_type'] = 'article';

                    } elseif ($meta['og_type'] != 'page' and trim($meta['subtype']) != '') {
                        $meta['og_type'] = $meta['subtype'];
                    }
                    if ($meta['description'] != false and trim($meta['description']) != '') {
                        // $meta['description'] = $meta['description'];
                    } elseif ($meta['content'] != false and trim($meta['content']) != '') {
                        $meta['description'] = str_replace("\n", ' ', $this->app->format->limit($this->app->format->clean_html(strip_tags($meta['content'])), 500));
                    }

                    if (isset($meta['description']) and $meta['description'] != '') {
                        $meta['og_description'] = $meta['description'];
                    } else {
                        $meta['og_description'] = trim($this->app->format->limit($this->app->format->clean_html(strip_tags($meta['content'])), 500));
                    }
                }
            } else {
                $meta['title'] = $this->app->option_manager->get('website_title', 'website');
                $meta['description'] = $this->app->option_manager->get('website_description', 'website');
                $meta['content_meta_keywords'] = $this->app->option_manager->get('website_keywords', 'website');
            }

            $meta['og_site_name'] = $this->app->option_manager->get('website_title', 'website');

            if (!empty($meta)) {
                if (isset($meta['content_meta_title']) and $meta['content_meta_title'] != '') {
                    $meta['title'] = $meta['content_meta_title'];
                } elseif (isset($meta['title']) and $meta['title'] != '') {
                } else {
                    $meta['title'] = $this->app->option_manager->get('website_title', 'website');
                }
                if (isset($meta['description']) and $meta['description'] != '') {
                } else {
                    $meta['description'] = $this->app->option_manager->get('website_description', 'website');
                }

                if (isset($meta['description']) and $meta['description'] != '') {
                    $meta['content_meta_description'] = $meta['description'];
                } elseif (isset($meta['content_meta_description'])) {
                    $meta['content_meta_description'] = content_description($meta['id']);
                } else {
                    $meta['content_meta_description'] = $this->app->option_manager->get('website_description', 'website');
                }

                if (isset($meta['title']) and $meta['title'] != '') {
                    $meta['content_meta_title'] = strip_tags($meta['title']);
                } elseif (isset($found_mod) and $found_mod != false) {
                    $meta['content_meta_title'] = ucwords(str_replace('/', ' ', $found_mod));
                } else {
                    $meta['content_meta_title'] = ucwords(str_replace('/', ' ', $this->app->url_manager->segment(0)));
                }

                if (isset($meta['content_meta_keywords']) and $meta['content_meta_keywords'] != '') {
                } else {
                    $meta['content_meta_keywords'] = $this->app->option_manager->get('website_keywords', 'website');
                }
                if (is_array($meta)) {

                    if(is_array($meta_tags_override) and !empty($meta_tags_override)){
                        $meta = array_merge($meta,$meta_tags_override);
                    }


                    foreach ($meta as $key => $item) {
                        if (is_string($item)) {
                            $item = html_entity_decode($item);
                            $item = strip_tags($item);
                            $item = str_replace('&amp;zwnj;', ' ', $item);
                            $item = str_replace('"', ' ', $item);
                            $item = str_replace("'", ' ', $item);

                            $item = str_replace('>', '', $item);
                            $item = str_replace('&amp;quot;', ' ', $item);
                            $item = str_replace('quot;', ' ', $item);
                            $item = str_replace('&amp;', ' ', $item);
                            $item = str_replace('amp;', ' ', $item);
                            $item = str_replace('nbsp;', ' ', $item);
                            $item = str_replace('#039;', ' ', $item);
                            $item = str_replace('&amp;nbsp;', ' ', $item);
                            $item = str_replace('&', ' ', $item);
                            $item = str_replace(';', ' ', $item);
                            $item = str_replace('  ', ' ', $item);
                            $item = str_replace(' ', ' ', $item);
                            $l = str_replace('{' . $key . '}', $item, $l);
                        } elseif ($item == false) {
                            $l = str_replace('{' . $key . '}', '', $l);
                        }
                    }
                }
            }
            $headers = array();
            $headers[] = $this->_render_webmasters_tags();
            $headers[] = $this->_render_analytics_tags();

            foreach ($headers as $headers_append) {
                if ($headers_append != false) {
                    $one = 1;
                    $l = str_ireplace('</head>', $headers_append . '</head>', $l, $one);
                }
            }
            $layout = $l;
            return $layout;
        }

    }


    private function _render_webmasters_tags()
    {

        $configs = [
            'google' => get_option('google-site-verification-code', 'website'),
            'bing' => get_option('bing-site-verification-code', 'website'),
            'alexa' => get_option('alexa-site-verification-code', 'website'),
            'pinterest' => get_option('pinterest-site-verification-code', 'website'),
            'yandex' => get_option('yandex-site-verification-code', 'website')
        ];

        $webmasters = Webmasters::make($configs);
        return $webmasters->render();
    }

    private function _render_analytics_tags()
    {
        $code = get_option('google-analytics-id', 'website');
        if ($code) {
            $analytics = new Analytics;
            $analytics->setGoogle($code);
            return $analytics->render();
        }

    }
}