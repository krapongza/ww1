<?php  
class ControllerModuleJournal2PhotoGallery extends Controller {

    private static $CACHEABLE = null;

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');
        $this->load->model('tool/image');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.photo_gallery_cache');
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
        if (Journal2Utils::getProperty($module_data, 'disable_mobile') && ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
            return;
        }

        $this->data['css'] = '';

        /* css for top / bottom positions */
        if (in_array($setting['position'], array('top', 'bottom'))) {
            $padding = $this->journal2->settings->get('module_margins', 20) . 'px';
            /* outer */
            $css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($module_data, 'background'));
            $css[] = 'padding-top: ' . Journal2Utils::getProperty($module_data, 'margin_top', 0) . 'px';
            $css[] = 'padding-bottom: ' . Journal2Utils::getProperty($module_data, 'margin_bottom', 0) . 'px';
            $this->journal2->settings->set('module_journal2_photo_gallery_' . $setting['module_id'], implode('; ', $css));

            /* inner css */
            $css = array();
            if (Journal2Utils::getProperty($module_data, 'fullwidth')) {
                $css[] = 'max-width: 100%';
                $css[] = 'padding-left: ' . $padding;
                $css[] = 'padding-right: ' . $padding;
            } else {
                $css[] = 'max-width: ' . $this->journal2->settings->get('site_width', 1024) . 'px';
            }
            $this->data['css'] = implode('; ', $css);
        }

        $cache_property = "module_journal_photo_gallery_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();

            $this->data['hide_on_mobile_class'] = Journal2Utils::getProperty($module_data, 'disable_mobile') ? 'hide-on-mobile' : '';

            /* set global module properties */
            $this->data['module'] = $module;
            $this->data['title'] = Journal2Utils::getProperty($module_data, 'gallery_name.value.' . $this->config->get('config_language_id'), '');

            /* image border */
            $this->data['image_border'] = implode('; ', Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($module_data, 'image_border')));

            /* grid */
            $this->data['is_column'] = in_array($setting['position'], array('column_left', 'column_right'));

            if (in_array($setting['position'], array('column_left', 'column_right'))) {
                $this->data['grid_classes'] = 'xs-33 sm-33 md-33 lg-33 xl-33';
            } else {
                $columns = in_array($setting['position'], array('top', 'bottom')) ? 0 : $this->journal2->settings->get('config_columns_count', 0);
                $this->data['grid_classes'] = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($module_data, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024), $columns);
            }

            /* carousel */
            $this->data['carousel'] = in_array($setting['position'], array('column_left', 'column_right')) ? false : Journal2Utils::getProperty($module_data, 'carousel');
            if ($this->data['carousel']) {
                $this->data['items_per_row'] = Journal2Utils::getProperty($module_data, 'items_per_row.value');
                $this->data['arrows'] = Journal2Utils::getProperty($module_data, 'carousel_arrows');
                $this->data['bullets'] = Journal2Utils::getProperty($module_data, 'carousel_bullets');
                if (Journal2Utils::getProperty($module_data, 'autoplay')) {
                    $this->data['autoplay'] = Journal2Utils::getProperty($module_data, 'transition_delay', 3000);
                } else {
                    $this->data['autoplay'] = false;
                }
                $this->data['slide_speed'] = (int)Journal2Utils::getProperty($module_data, 'transition_speed', 400);
                $this->data['pause_on_hover'] = Journal2Utils::getProperty($module_data, 'pause_on_hover');
                $this->data['touch_drag'] = Journal2Utils::getProperty($module_data, 'touch_drag');
            }

            /* sort images */
            $images = Journal2Utils::getProperty($module_data, 'images', array());
            $images = Journal2Utils::sortArray($images);

            /* generate images */
            $this->data['thumb_limit'] = Journal2Utils::getProperty($module_data, 'thumbs_limit', PHP_INT_MAX);
            $this->data['images'] = array();
            foreach ($images as $image) {
                if (isset($image['status']) && !$image['status']) continue;
                if (!file_exists(DIR_IMAGE . $image['image'])) {
                    $image['image'] = 'no_image.jpg';
                }
                list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $image['image']);
                $this->data['images'][] = array(
                    'name'      => Journal2Utils::getProperty($image, 'name.value.' . $this->config->get('config_language_id'), ''),
                    'image'     => 'image/' . Journal2Utils::getProperty($image, 'image'),
					'thumb'     => $this->model_tool_image->resize(Journal2Utils::getProperty($image, 'image'), 200, 300, 'w')
                    //'thumb'     => $this->model_tool_image->resize(Journal2Utils::getProperty($image, 'image'), 200, 200, $width_orig < $height_orig ? 'w' : 'h')
                );
            }

            $this->template = 'journal2/template/journal2/module/photo_gallery.tpl';
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

        $this->document->addScript('catalog/view/theme/journal2/lib/swipebox/source/jquery.swipebox.js');

        Journal2::stopTimer(get_class($this));
    }
}
