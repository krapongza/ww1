<?php
/* @property ModelCatalogManufacturer model_catalog_manufacturer */
/* @property ModelCatalogCategory model_catalog_category */
class ControllerModuleJournal2CarouselGrid extends Controller {

    private static $CACHEABLE = null;

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');

        if (self::$CACHEABLE === null) {
            //self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.carousel_grid_cache');
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

        /* hide on mobile */
        $disable_mobile = Journal2Utils::getProperty($module_data, 'disable_mobile') && $this->journal2->settings->get('responsive_design');

        if ($disable_mobile && ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet())) {
            return;
        }

        $this->data['disable_mobile'] = $disable_mobile ? 'hide-on-mobile' : '';
        $this->data['height'] = Journal2Utils::getProperty($module_data, 'height');

        $this->data['css'] = array();

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_carousel_grid_' . $setting['module_id'], implode('; ', $css));

            /* inner css */
            $css = array();
            if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                $css[] = 'max-width: 100%';
                $css[] = 'padding-left: ' . $padding;
                $css[] = 'padding-right: ' . $padding;
            } else {
                $css[] = 'max-width: ' . $this->journal2->settings->get('site_width', 1024) . 'px';
            }

            $this->data['css'] = $css;
        }

        $module_spacing = Journal2Utils::getProperty($module_data, 'module_spacing');
        $this->data['module_spacing'] = $module_spacing !== null ? $module_spacing . 'px' : null;

        $this->data['css'] = implode('; ', $this->data['css']);

        $cache_property = "module_journal_carousel_grid_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $this->data['module'] = mt_rand();

            $this->template = 'journal2/template/journal2/module/carousel_grid.tpl';

            $columns = Journal2Utils::getProperty($module_data, 'columns', array());
            $columns = Journal2Utils::sortArray($columns);

            $this->data['columns'] = array();

            foreach ($columns as $column) {
                if (!$column['status']) continue;

                $module_disable_mobile = Journal2Utils::getProperty($column, 'disable_mobile') && $this->journal2->settings->get('responsive_design');

                if ($module_disable_mobile && ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet())) {
                    continue;
                }

                $size = floor($column['width']);

                $this->data['columns'][] = array(
                    'classes'   => "xs-{$size} sm-{$size} md-{$size} lg-{$size} xl-{$size}",
                    'content'   => $this->getChild('module/journal2_carousel', array (
                        'module_id' => Journal2Utils::getProperty($column, 'module_id'),
                        'layout_id' => -1,
                        'position'  => 'multi_module'
                    ))
                );
            }

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

        Journal2::stopTimer(get_class($this));
    }

}
