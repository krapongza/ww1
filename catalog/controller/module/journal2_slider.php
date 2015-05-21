<?php  
class ControllerModuleJournal2Slider extends Controller {

    private static $CACHEABLE = null;
    private $google_fonts = array();

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');
        $this->load->model('tool/image');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/information');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.slider_cache');
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        Journal2::startTimer(get_class($this));

        /* get module data from db */
        $module_data = $this->model_journal2_module->getModule($setting['module_id']);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;
        $module_data = $module_data['module_data'];

        if (Journal2Utils::getProperty($module_data, 'hideonmobile') && $this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet() && $this->journal2->settings->get('responsive_design')) {
            return;
        }

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_slider_' . $setting['module_id'], implode('; ', $css));
        }

        $this->journal2->html_classes->removeClass('backface');

        $cache_property = "module_journal_slider_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();
            $caption_id = 0;

            /* slider position */
            $height = Journal2Utils::getProperty($module_data, 'height', 400);
            $width = null;
            switch ($setting['position']) {
                case 'column_left':
                case 'column_right':
                    $width = 220;
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
                case 'content_top':
                case 'content_bottom':
                    if ($this->journal2->settings->get('extended_layout')) {
                        $width = $this->journal2->settings->get('site_width', 1024) - 240 * $this->journal2->settings->get('config_columns_count');
                    } else {
                        $width = $this->journal2->settings->get('site_width', 1024) - 40 - 240 * $this->journal2->settings->get('config_columns_count');
                    }
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
                case 'top':
                case 'bottom':
                    $width = $this->journal2->settings->get('site_width', 1024);
                    if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                        $this->data['width'] = "max-width: 100%";
                        $this->data['slider_class'] = 'journal-fullwidth-slider';
                    } else {
                        $this->data['width'] = "max-width: {$width}px";
                        $this->data['slider_class'] = 'journal-slider';
                    }
                    break;
                case 'multi_module':
                    $width = $setting['width'];
                    $height = $setting['height'];
                    $this->data['width'] = "max-width: {$width}px";
                    $this->data['slider_class'] = 'journal-slider';
                    break;
            }

            /* global style data */
            $this->data['global_style'] = array();

            $this->data['js_options'] = Journal2Utils::getProperty($module_data, 'js_options', array());
            $this->data['js_options']['startwidth'] = $width;
            $this->data['js_options']['startheight'] = $height;
            $this->data['height'] = $height;

            if (Journal2Utils::getProperty($module_data, 'hidecaptionsonmobile')) {
                $this->data['js_options']['hideAllCaptionAtLimit'] = 760;
            }

            $slides = Journal2Utils::getProperty($module_data, 'slides', array());
            $slides = Journal2Utils::sortArray($slides);
            $_slides = array();

            $first = true;

            foreach ($slides as $slide) {
                if (isset($slide['status']) && !$slide['status']) continue;
                $slide_data = array();
                if ($first) {
                    $slide_data[] = 'data-fstransition="fade"';
                    $slide_data[] = 'data-fsslotamount="0"';
                    $slide_data[] = 'data-fsmasterspeed="0"';
                    $first = false;
                }
                $slide_data[] = 'data-transition="' . Journal2Utils::getProperty($slide, 'transition', 'fade') . '"';
                $slide_data[] = 'data-easing="' . Journal2Utils::getProperty($slide, 'easing', 'Expo.easeOut') . '"';
                if (Journal2Utils::getProperty($slide, 'thumb')) {
                    $thumb = Journal2Utils::getProperty($slide, 'thumb', 'no_image.jpg');
                    if (!file_exists(DIR_IMAGE . $thumb)) {
                        $thumb = 'no_image.jpg';
                    }
                    list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $thumb);
                    $slide_data[] = 'data-thumb="' . $this->model_tool_image->resize($thumb, $this->data['js_options']['thumbWidth'], $this->data['js_options']['thumbHeight'], $width_orig < $height_orig ? 'w' : 'h') . '"';
                } else {
                    $thumb = Journal2Utils::getProperty($slide, 'image', 'no_image.jpg');
                    if (!file_exists(DIR_IMAGE . $thumb)) {
                        $thumb = 'no_image.jpg';
                    }
                    list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $thumb);
                    $slide_data[] = 'data-thumb="' . $this->model_tool_image->resize($thumb, $this->data['js_options']['thumbWidth'], $this->data['js_options']['thumbHeight'], $width_orig < $height_orig ? 'w' : 'h') . '"';
                }
                $slide_data[] = 'data-masterspeed="' . Journal2Utils::getProperty($slide, 'masterspeed', 800) . '"';
                if (Journal2Utils::getProperty($slide, 'slotamount')) {
                    $slide_data[] = 'data-slotamount="' . Journal2Utils::getProperty($slide, 'slotamount') . '"';
                }
                if (Journal2Utils::getProperty($slide, 'delay')) {
                    $slide_data[] = 'data-delay="' . Journal2Utils::getProperty($slide, 'delay') . '"';
                }
                $link = $this->getLink(Journal2Utils::getProperty($slide, 'link'));
                if ($link) {
                    $slide_data[] = 'data-link="' . $link . '"';
                    if (Journal2Utils::getProperty($slide, 'link_new_window')) {
                        $slide_data[] = 'data-target="_blank"';
                    }
                }

                $captions = Journal2Utils::getProperty($slide, 'captions', array());
                $captions = Journal2Utils::sortArray($captions);
                $_captions = array();

                foreach ($captions as $caption) {
                    if (isset($caption['status']) && !$caption['status']) continue;
                    $caption_id++;
                    $caption_data = array();
                    $caption_classes = array();
                    if (Journal2Utils::getProperty($caption, 'x', '1')) {
                        $caption_data[] = 'data-x="' . Journal2Utils::getProperty($caption, 'x', '1') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'y', '1')) {
                        $caption_data[] = 'data-y="' . Journal2Utils::getProperty($caption, 'y', '1') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'speed')) {
                        $caption_data[] = 'data-speed="' . Journal2Utils::getProperty($caption, 'speed') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'start')) {
                        $caption_data[] = 'data-start="' . Journal2Utils::getProperty($caption, 'start') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'endspeed')) {
                        $caption_data[] = 'data-endspeed="' . Journal2Utils::getProperty($caption, 'endspeed') .'"';
                    }
                    if (Journal2Utils::getProperty($caption, 'end')) {
                        $caption_data[] = 'data-end="' . Journal2Utils::getProperty($caption, 'end') .'"';
                    }
                    $caption_data[] = 'data-easing="' . Journal2Utils::getProperty($caption, 'easing', 'Expo.easeOut') . '"';
                    $caption_data[] = 'data-endeasing="' . Journal2Utils::getProperty($caption, 'endeasing', 'Expo.easeOut') . '"';
                    $content = '';
                    $css = array();
                    switch (Journal2Utils::getProperty($caption, 'type')) {
                        case 'image':
                            $image = Journal2Utils::optimizeImage(Journal2Utils::getProperty($caption, 'image', 'no_image.jpg'));
                            $alt = Journal2Utils::getProperty($caption, 'caption_name');
                            $content = '<img src="' . $image . '" alt="' . $alt .'" />';
                            break;
                        case 'text':
                            if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') === 'google') {
                                $font_name = Journal2Utils::getProperty($caption, 'text_font.value.font_name');
                                $font_subset = Journal2Utils::getProperty($caption, 'text_font.value.font_subset');
                                $font_weight = Journal2Utils::getProperty($caption, 'text_font.value.font_weight');
                                $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
                                $this->google_fonts[] = array(
                                    'name'  => $font_name,
                                    'subset'=> $font_subset,
                                    'weight'=> $font_weight
                                );
                                $weight = filter_var(Journal2Utils::getProperty($caption, 'text_font.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                                $css[] = 'font-weight: ' . ($weight ? $weight : 400);
                                $css[] = "font-family: '" . Journal2Utils::getProperty($caption, 'text_font.value.font_name') . "'";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') === 'system') {
                                $css[] = 'font-weight: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_weight');
                                $css[] = 'font-family: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_family');
                            }
                            if (Journal2Utils::getProperty($caption, 'text_font.value.font_type') !== 'none') {
                                $css[] = 'font-size: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_size');
                                $css[] = 'font-style: ' . Journal2Utils::getProperty($caption, 'text_font.value.font_style');
                                $css[] = 'text-transform: ' . Journal2Utils::getProperty($caption, 'text_font.value.text_transform');
                            }
                            if (Journal2Utils::getProperty($caption, 'text_font.value.color.value.color')) {
                                $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_font.value.color.value.color'));
                            }
                            $css[] = 'text-align: ' . Journal2Utils::getProperty($caption, 'text_align', 'center');
                            if (Journal2Utils::getProperty($caption, 'text_bgcolor.value.color')) {
                                $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_bgcolor.value.color'));
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_bg_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { background-color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_bg_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_border')) {
                                $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($caption, 'text_border')));
                            }
                            if (Journal2Utils::getProperty($caption, 'text_hover_border_color.value.color')) {
                                $this->data['global_style'][] = "#jcaption-{$module}-{$caption_id}:hover { border-color: " . Journal2Utils::getColor(Journal2Utils::getProperty($caption, 'text_hover_border_color.value.color')) . " !important; }";
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_top') !== null) {
                                $css[] = 'padding-top: ' . Journal2Utils::getProperty($caption, 'text_padding_top') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_right') !== null) {
                                $css[] = 'padding-right: ' . Journal2Utils::getProperty($caption, 'text_padding_right') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_bottom') !== null) {
                                $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($caption, 'text_padding_bottom') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_padding_left') !== null) {
                                $css[] = 'padding-left: ' . Journal2Utils::getProperty($caption, 'text_padding_left') . 'px';
                            }
                            if (Journal2Utils::getProperty($caption, 'text_line_height')) {
                                $css[] = 'line-height: ' . Journal2Utils::getProperty($caption, 'text_line_height') . 'px';
                            }
                            $content = Journal2Utils::getProperty($caption, 'text.value.' . $this->config->get('config_language_id'));
                            break;
                        case 'video':
                            if (Journal2Utils::getProperty($caption, 'video_fullwidth')) {
                                $caption_classes[] = 'fullscreenvideo';
                                $caption_data[] = 'data-forceCover="1"';
                                $width = '100%';
                                $height = '100%';
                            } else {
                                $width = Journal2Utils::getProperty($caption, 'video_width', 100);
                                $height = Journal2Utils::getProperty($caption, 'video_height', 100);
                            }
                            switch (Journal2Utils::getProperty($caption, 'video_type')) {
                                case 'youtube':
                                    $video_id = Journal2Utils::getProperty($caption, 'video_yt_id');
                                    if (!$video_id) continue;
                                    $content = '<iframe src="//www.youtube.com/embed/' . $video_id .'?enablejsapi=1&html5=1&amp;hd=1&amp;wmode=opaque&amp;controls=1&amp;showinfo=0;rel=0;" width="' . $width . '" height="' . $height . '"></iframe>';
                                    break;
                                case 'vimeo':
                                    $video_id = Journal2Utils::getProperty($caption, 'video_vm_id');
                                    if (!$video_id) continue;
                                    $content = '<iframe src="//player.vimeo.com/video/' . $video_id .'?title=0&amp;byline=0&amp;portrait=0;api=1" width="' . $width . '" height="' . $height . '"></iframe>';
                                    break;
                                case 'local':
                                    $file_extensions = array('mp4', 'webm', 'ogg');
                                    $content = '<video class="" preload="none" width="' . $width . '" height="' . $height .'" data-setup="{}" controls';
                                    $poster_path = Journal2Utils::getProperty($caption, 'video_path') . '.png';
                                    if (file_exists(DIR_APPLICATION . '../' .  $poster_path)) {
                                        $content .= ' poster="' . $poster_path . '" data-setup="{}"';
                                    }
                                    $content .= '>';
                                    foreach ($file_extensions as $file_extension) {
                                        $video_path = Journal2Utils::getProperty($caption, 'video_path') . '.' . $file_extension;
                                        if (file_exists(DIR_APPLICATION . '../' .  $video_path)) {
                                            $content .= '<source src="' . $video_path . '" type="video/' . $file_extension . '" />';
                                        }
                                    }
                                    $content .= '</video>';
                                    break;
                            }
                            if (Journal2Utils::getProperty($caption, 'video_autoplay')) {
                                $caption_data[] = 'data-autoplay="true"';
                                $caption_data[] = 'data-forcerewind="on"';
                            } else {
                                $caption_data[] = 'data-autoplay="false"';
                            }
                            if (Journal2Utils::getProperty($caption, 'video_autoplayonlyfirsttime')) {
                                $caption_data[] = 'data-autoplayonlyfirsttime="true"';
                            } else {
                                $caption_data[] = 'data-autoplayonlyfirsttime="false"';
                            }
                            if (Journal2Utils::getProperty($caption, 'video_nextslideatend')) {
                                $caption_data[] = 'data-nextslideatend="true"';
                            } else {
                                $caption_data[] = 'data-nextslideatend="false"';
                            }
                            if (!Journal2Utils::getProperty($caption, 'video_volume')) {
                                $caption_data[] = 'data-volume="mute"';
                            }
                            break;
                    }
                    $caption_classes[] = Journal2Utils::getProperty($caption, 'animation_in', 'fade');
                    $caption_classes[] = Journal2Utils::getProperty($caption, 'animation_out', 'fadeout');

                    if (Journal2Utils::getProperty($caption, 'animation_in') === 'customin') {
                        $custom_in = array();
                        $custom_out = array();
                        // in
                        if (Journal2Utils::getProperty($caption, 'custom_in_transition_x') !== null) {
                            $custom_in[] = 'x:' . Journal2Utils::getProperty($caption, 'custom_in_transition_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_transition_y') !== null) {
                            $custom_in[] = 'y:' . Journal2Utils::getProperty($caption, 'custom_in_transition_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_scale_x') !== null) {
                            $custom_in[] = 'scaleX:' . Journal2Utils::getProperty($caption, 'custom_in_scale_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_scale_y') !== null) {
                            $custom_in[] = 'scaleY:' . Journal2Utils::getProperty($caption, 'custom_in_scale_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_x') !== null) {
                            $custom_in[] = 'rotationX:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_y') !== null) {
                            $custom_in[] = 'rotationY:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_rotation_z') !== null) {
                            $custom_in[] = 'rotationZ:' . Journal2Utils::getProperty($caption, 'custom_in_rotation_z');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_in_transform_perspective', '500') !== null) {
                            $custom_in[] = 'transformPerspective:' . Journal2Utils::getProperty($caption, 'custom_in_transform_perspective', '500');
                        }
                        $custom_in[] = 'opacity:' . Journal2Utils::getProperty($caption, 'custom_in_opacity');
                        $custom_in[] = 'transformOrigin:' . Journal2Utils::getProperty($caption, 'transformOriginXin', 'center') . ' ' . Journal2Utils::getProperty($caption, 'transformOriginYin', 'center');
                        // out
                        if (Journal2Utils::getProperty($caption, 'custom_out_transition_x') !== null) {
                            $custom_out[] = 'x:' . Journal2Utils::getProperty($caption, 'custom_out_transition_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_transition_y') !== null) {
                            $custom_out[] = 'y:' . Journal2Utils::getProperty($caption, 'custom_out_transition_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_scale_x') !== null) {
                            $custom_out[] = 'scaleX:' . Journal2Utils::getProperty($caption, 'custom_out_scale_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_scale_y') !== null) {
                            $custom_out[] = 'scaleY:' . Journal2Utils::getProperty($caption, 'custom_out_scale_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_x') !== null) {
                            $custom_out[] = 'rotationX:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_x');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_y') !== null) {
                            $custom_out[] = 'rotationY:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_y');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_rotation_z') !== null) {
                            $custom_out[] = 'rotationZ:' . Journal2Utils::getProperty($caption, 'custom_out_rotation_z');
                        }
                        if (Journal2Utils::getProperty($caption, 'custom_out_transform_perspective', '500') !== null) {
                            $custom_out[] = 'transformPerspective:' . Journal2Utils::getProperty($caption, 'custom_out_transform_perspective', '500');
                        }
                        $custom_out[] = 'opacity:' . Journal2Utils::getProperty($caption, 'custom_out_opacity');
                        $custom_out[] = 'transformOrigin:' . Journal2Utils::getProperty($caption, 'transformOriginXout', 'center') . ' ' . Journal2Utils::getProperty($caption, 'transformOriginYout', 'center');
                        $caption_data[] = 'data-customin="' . implode(';',$custom_in) . '"';
                        $caption_data[] = 'data-customout="' . implode(';',$custom_out) . '"';
                    }

                    $_captions[] = array(
                        'id'            => "{$module}-{$caption_id}",
                        'content'       => $content,
                        'data'          => implode(' ', $caption_data),
                        'classes'       => implode(' ', $caption_classes),
                        'css'           => implode('; ', $css),
                        'link'          => $this->getLink(Journal2Utils::getProperty($caption, 'link')),
                        'target'        => Journal2Utils::getProperty($caption, 'link_new_window') ? ' target="_blank"' : ''
                    );
                }

                $_slides[] = array(
                    'image'     => Journal2Utils::optimizeImage(Journal2Utils::getProperty($slide, 'image', 'no_image.jpg')),
                    'name'      => Journal2Utils::getProperty($slide, 'slide_name'),
                    'data'      => implode(' ', $slide_data),
                    'captions'  => $_captions
                );
            }

            $this->data['module'] = $module;
            $this->data['slides'] = $_slides;
            $this->data['js_options']['thumbAmount'] = min($this->data['js_options']['thumbAmount'], count($this->data['slides']));
            $this->data['timer'] = Journal2Utils::getProperty($module_data, 'timer');
            $this->data['preload_images'] = Journal2Utils::getProperty($module_data, 'preload_images', '1');
            $this->data['hide_on_mobile_class'] = Journal2Utils::getProperty($module_data, 'hideonmobile') ? 'hide-on-mobile' : '';

            $this->template = 'journal2/template/journal2/module/slider_advanced.tpl';
            if (self::$CACHEABLE === true) {
                $html = Minify_HTML::minify($this->render(), array(
                    'xhtml' => false,
                    'jsMinifier' => 'j2_js_minify'
                ));
                $this->journal2->cache->set($cache_property, $html);
                $this->journal2->cache->set($cache_property . '_fonts', json_encode($this->google_fonts));
            } else {
                $this->render();
            }
        } else {
            if ($fonts = $this->journal2->cache->get($cache_property . '_fonts')) {
                $fonts = json_decode($fonts, true);
                if (is_array($fonts)) {
                    foreach ($fonts as $font) {
                        $this->journal2->google_fonts->add($font['name'], $font['subset'], $font['weight']);
                    }
                }
            }
            $this->template = 'journal2/template/journal2/cache/cache.tpl';
            $this->data['cache'] = $cache;
            $this->render();
        }

        $this->document->addStyle('catalog/view/theme/journal2/lib/rs-plugin/css/settings.css');
        $this->document->addScript('catalog/view/theme/journal2/lib/rs-plugin/js/jquery.themepunch.tools.min.js');
        $this->document->addScript('catalog/view/theme/journal2/lib/rs-plugin/js/jquery.themepunch.revolution.min.js');

        Journal2::stopTimer(get_class($this));

    }

    private function getLink($link) {
        $href = null;
        /* menu type */
        switch ($link['menu_type']) {
            case 'category':
                $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$category_info) continue;
                $href = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                break;
            case 'product':
                $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$product_info) continue;
                $href = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                break;
            case 'manufacturer':
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$manufacturer_info) continue;
                $href = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                break;
            case 'information':
                $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$information_info) continue;
                $href = $this->url->link('information/information', 'information_id=' .  $information_info['information_id']);
                break;
            case 'popup':
                $href = "javascript:Journal.openPopup('{$link['menu_item']}')";
                break;
            case 'opencart':
                $customer_name = null;
                switch ($link['menu_item']['page']) {
                    case 'login':
                        $link['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                        $customer_name = $this->customer->getFirstName();
                        break;
                    case 'register':
                        $link['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                        break;
                    case 'account/wishlist':
                        break;
                    default:
                }
                $href = $link['menu_item']['page'] === 'common/home' ? $this->journal2->config->base_url : $this->url->link($link['menu_item']['page'], '', 'SSL');
                break;
            case 'custom':
                $href = Journal2Utils::getProperty($link, 'menu_item.url');
                break;
        }

        return $href;
    }

}
