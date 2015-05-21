<?php
class ControllerModuleJournal2FullscreenSlider extends Controller {

    private static $CACHEABLE = null;

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.fullscreen_slider_cache');
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

        if (Journal2Utils::getProperty($module_data, 'module_data.disable_mobile') && ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet())) {
            return;
        }

        $cache_property = "module_journal_fullscreen_slider_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();

            $this->data['module'] = $module;
            $this->data['transition'] = Journal2Utils::getProperty($module_data, 'module_data.transition', 'fade');
            $this->data['transition_speed'] = Journal2Utils::getProperty($module_data, 'module_data.transition_speed', '700');
            $this->data['transition_delay'] = Journal2Utils::getProperty($module_data, 'module_data.transition_delay', '3000');

            $this->data['transparent_overlay'] = Journal2Utils::getProperty($module_data, 'module_data.transparent_overlay', '');

            $this->data['images'] = array();

            $images = Journal2Utils::getProperty($module_data, 'module_data.images', array());
            $images = Journal2Utils::sortArray($images);

            foreach ($images as $image) {
                if (!$image['status']) continue;
                $this->data['images'][] = array(
                    'image' => 'image/' . $image['image'],
                    'title' => ''
                );
            }

            $this->template = 'journal2/template/journal2/module/fullscreen_slider.tpl';
            if (self::$CACHEABLE === true) {
                $html = Minify_HTML::minify($this->render(), array(
                    'xhtml' => false,
                    'jsMinifier' => 'j2_js_minify'
                ));
                $this->journal2->cache->set($cache_property, $html);
            } else {
                $this->render();
            }
        } else {
            $this->template = 'journal2/template/journal2/cache/cache.tpl';
            $this->data['cache'] = $cache;
            $this->render();
        }

        $this->document->addStyle('catalog/view/theme/journal2/lib/supersized/css/supersized.css');
        $this->document->addScript('catalog/view/theme/journal2/lib/supersized/js/jquery.easing.min.js');
        $this->document->addScript('catalog/view/theme/journal2/lib/supersized/js/supersized.3.2.7.min.js');

        Journal2::stopTimer(get_class($this));

    }

}