<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2Popup extends Controller {

    private static $CACHEABLE = null;
    private $google_fonts = array();

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');
        $this->load->model('journal2/menu');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.popup_cache');
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        if ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet() && $this->journal2->settings->get('responsive_design')) return;

        Journal2::startTimer(get_class($this));

        $module_data = $this->model_journal2_module->getModule($setting['module_id']);
        if (!$module_data || !isset($module_data['module_data']) || !$module_data['module_data']) return;
        $module_data = $module_data['module_data'];

        $cache_property = "module_journal_popup_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        $this->data['cookie_name'] = 'popup-' . Journal2Utils::getProperty($module_data, 'do_not_show_again_cookie');

        if ($this->data['is_ajax'] = isset($setting['position']) && $setting['position'] === 'ajax') {
            $cache = null;
        } else {
            if (isset($this->request->cookie[$this->data['cookie_name']])) {
                return;
            }
        }

        if ($cache === null || self::$CACHEABLE !== true) {
            /* set global module properties */
            $this->data['module'] = mt_rand();

            /* dimensions */
            $width = Journal2Utils::getProperty($module_data, 'width', 600);
            $header_height = Journal2Utils::getProperty($module_data, 'title_height', 40);
            $footer_height = Journal2Utils::getProperty($module_data, 'footer_height', 60);
            $newsletter_height = Journal2Utils::getProperty($module_data, 'newsletter_height', 80);
            $content_height = Journal2Utils::getProperty($module_data, 'height', 400);
            $height = $header_height + $footer_height + $content_height;

            /* newsletter */
            if (Journal2Utils::getProperty($module_data, 'newsletter')) {
                $this->data['newsletter'] = $this->getChild('module/journal2_newsletter', array (
                    'module_id' => Journal2Utils::getProperty($module_data, 'newsletter_id'),
                    'layout_id' => -1,
                    'position'  => 'footer'
                ));
                $this->data['newsletter_style'] = array();
                $color = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'newsletter_bg_color.value.color'));
                if ($color) {
                    $this->data['newsletter_style'][] = "background-color: {$color}";
                }
                if ($newsletter_height) {
                    $this->data['newsletter_style'][] = "height: {$newsletter_height}px";
                    $height += $newsletter_height;
                }
            } else {
                $this->data['newsletter'] = false;
            }

            /* header */
            $this->data['close_button'] = (int)Journal2Utils::getProperty($module_data, 'close_button');
            $this->data['title'] = Journal2Utils::getProperty($module_data, 'title.value.' . $this->config->get('config_language_id'));
            $this->data['header_style'] = array();
            $color = Journal2Utils::getColor(Journal2Utils::getProperty($module_data, 'title_bg_color.value.color'));
            if ($color) {
                $this->data['header_style'][] = "background-color: {$color}";
            }
            if ($header_height) {
                $this->data['header_style'][] = "height: {$header_height}px";
            }
            $this->data['header_style'] = array_merge($this->data['header_style'], $this->getFontSettings($module_data, 'title_font'));
            if (!$this->data['title']) {
                $height -= $header_height;
            }

            /* content */
            $this->data['content'] = Journal2Utils::getProperty($module_data, 'text.' . $this->config->get('config_language_id'), '&nbsp;');
            $this->data['content_style'] = array();
            $this->data['content_style'][] = "height: {$content_height}px";
            if ($padding = Journal2Utils::getProperty($module_data, 'padding')) {
                $this->data['content_style'][] = "padding: {$padding}px";
            }
            if (!$this->data['content']) {
                $height -= $content_height;
            }
            if (Journal2Utils::getProperty($module_data, 'content_overflow', '1') == '1') {
                $this->data['content_overflow'] = 'overflow-on';
            } else {
                $this->data['content_overflow'] = '';
            }

            /* footer */
            $this->data['footer'] = false;
            $this->data['footer_style'] = array();
            $color = Journal2Utils::getProperty($module_data, 'footer_bg_color.value.color');
            if ($color) {
                $this->data['footer_style'][] = "background-color: " . Journal2Utils::getColor($color);
            }
            if ($footer_height) {
                $this->data['footer_style'][] = "height: {$footer_height}px";
            }
            $this->data['button_1'] = $this->getButtonStyle($module_data, 'button_1');
            $this->data['button_2'] = $this->getButtonStyle($module_data, 'button_2');
            $this->data['do_not_show_again'] = Journal2Utils::getProperty($module_data, 'do_not_show_again', '0');
            $this->data['do_not_show_again_text'] = Journal2Utils::getProperty($module_data, 'do_not_show_again_text.value.' . $this->config->get('config_language_id'), "Don't show again.");
            $this->data['do_not_show_again_font'] = $this->getFontSettings($module_data, 'do_not_show_again_font');
            $this->data['footer_buttons_class'] = '';
            if ($this->data['button_1']['status'] || $this->data['button_1']['status']) {
                $this->data['footer'] = true;
                $this->data['footer_buttons_class'] = 'has-btn';
            }
            if ($this->data['do_not_show_again']) {
                $this->data['footer'] = true;
            }

            /* global styles */
            $this->data['global_style'] = array();
            $this->data['global_style'][] = "width: {$width}px";
            $this->data['global_style'][] = "height: {$height}px";
            $this->data['global_style'] = array_merge($this->data['global_style'], Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background')));

            /* render*/
            $this->template = 'journal2/template/journal2/module/popup.tpl';
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

        $this->document->addScript('catalog/view/theme/journal2/lib/jqueryc/jqueryc.js');

        Journal2::stopTimer(get_class($this));
    }

    public function show() {
        echo $this->getChild('module/journal2_popup', array (
            'module_id' => $this->request->get['module_id'],
            'layout_id' => -1,
            'position'  => 'ajax'
        ));
    }

    private function getFontSettings($module_data, $property) {
        $css = array();
        if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') === 'google') {
            $font_name = Journal2Utils::getProperty($module_data, $property . '.value.font_name');
            $font_subset = Journal2Utils::getProperty($module_data, $property . '.value.font_subset');
            $font_weight = Journal2Utils::getProperty($module_data, $property . '.value.font_weight');
            $this->journal2->google_fonts->add($font_name, $font_subset, $font_weight);
            $this->google_fonts[] = array(
                'name'  => $font_name,
                'subset'=> $font_subset,
                'weight'=> $font_weight
            );
            $weight = filter_var(Journal2Utils::getProperty($module_data, $property . '.value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
            $css[] = 'font-weight: ' . ($weight ? $weight : 400);
            $css[] = "font-family: '" . Journal2Utils::getProperty($module_data, $property . '.value.font_name') . "'";
        }
        if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') === 'system') {
            $css[] = 'font-weight: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_weight');
            $css[] = 'font-family: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_family');
        }
        if (Journal2Utils::getProperty($module_data, $property . '.value.font_type') !== 'none') {
            $css[] = 'font-size: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_size');
            $css[] = 'font-style: ' . Journal2Utils::getProperty($module_data, $property . '.value.font_style');
            $css[] = 'text-transform: ' . Journal2Utils::getProperty($module_data, $property . '.value.text_transform');
        }
        if (Journal2Utils::getProperty($module_data, $property . '.value.color.value.color')) {
            $css[] = 'color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($module_data, $property . '.value.color.value.color'));
        }
        return $css;
    }

    private function getButtonStyle($module_data, $property) {
        $style = $this->getFontSettings($module_data, $property . '_font');
        if ($color = Journal2Utils::getProperty($module_data, $property . '_bgcolor.value.color')) {
            $style[] = 'background-color: ' . Journal2Utils::getColor($color);
        }

        $hover_style = array();
        if ($color = Journal2Utils::getProperty($module_data, $property . '_hover_bgcolor.value.color')) {
            $hover_style[] = 'background-color: ' . Journal2Utils::getColor($color) . ' !important';
        }

        return array(
            'status'        => Journal2Utils::getProperty($module_data, $property),
            'text'          => Journal2Utils::getProperty($module_data, $property . '_text.value.' . $this->config->get('config_language_id')),
            'icon'          => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($module_data, $property . '_icon')),
            'icon_position' => Journal2Utils::getProperty($module_data, $property . '_icon_position', 'right'),
            'link'          => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($module_data, $property . '_link')),
            'target'        => Journal2Utils::getProperty($module_data, $property . '_new_window') ? 'target="_blank"' : '',
            'style'         => implode('; ', $style),
            'hover_style'   => implode('; ', $hover_style)
        );
    }

}
