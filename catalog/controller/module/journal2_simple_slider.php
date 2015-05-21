<?php  
class ControllerModuleJournal2SimpleSlider extends Controller {

    private static $CACHEABLE = null;

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
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.simple_slider_cache');
        }
    }

    public function index($setting) {
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }

        Journal2::startTimer(get_class($this));

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
            $this->journal2->settings->set('module_journal2_simple_slider_' . $setting['module_id'], implode('; ', $css));
        }

        $cache_property = "module_journal_simple_slider_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $module = mt_rand();

            /* slider position */
            $height = Journal2Utils::getProperty($module_data, 'height', 400);
            $width = null;
            switch ($setting['position']) {
                case 'column_left':
                case 'column_right':
                    $width = 220;
                    $this->data['width'] = "max-width: {$width}px";
                    break;
                case 'content_top':
                case 'content_bottom':
                    if ($this->journal2->settings->get('extended_layout')) {
                        $width = $this->journal2->settings->get('site_width', 1024) - 240 * $this->journal2->settings->get('config_columns_count');
                    } else {
                        $width = $this->journal2->settings->get('site_width', 1024) - 40 - 240 * $this->journal2->settings->get('config_columns_count');
                    }
                    $this->data['width'] = "max-width: {$width}px";
                    break;
                case 'top':
                case 'bottom':
                    $width = $this->journal2->settings->get('site_width', 1024);
                    $this->data['width'] = "max-width: {$width}px";
                    break;
                case 'multi_module':
                    $width = $setting['width'];
                    $height = $setting['height'];
                    $this->data['width'] = "max-width: {$width}px";
                    break;
            }

            /* global style data */
            $this->data['global_style'] = array();

            $slides = Journal2Utils::getProperty($module_data, 'slides', array());
            $slides = Journal2Utils::sortArray($slides);
            $_slides = array();

            $this->data['js_options'] = array(
                'slideSpeed'        => (int)Journal2Utils::getProperty($module_data, 'transition_speed', 800),
                'stopOnHover'       => (bool)Journal2Utils::getProperty($module_data, 'pause_on_hover', 1),
                'lazyLoad'          => (bool)Journal2Utils::getProperty($module_data, 'preload_images', '1'),
                'navigation'        => (bool)Journal2Utils::getProperty($module_data, 'arrows', 1),
                'pagination'        => (bool)Journal2Utils::getProperty($module_data, 'bullets', 1)
            );

            if (Journal2Utils::getProperty($module_data, 'transition', 'fade') !== 'slide') {
                $this->data['js_options']['transitionStyle'] = Journal2Utils::getProperty($module_data, 'transition', 'fade');
            }

            $this->data['nav_on_hover'] = Journal2Utils::getProperty($module_data, 'show_on_hover', 1) ? 'nav-on-hover' : '';

            if (Journal2Utils::getProperty($module_data, 'autoplay')) {
                $this->data['js_options']['autoPlay'] = (int)Journal2Utils::getProperty($module_data, 'transition_delay', 3000);
            } else {
                $this->data['js_options']['autoPlay'] = false;
            }

            foreach ($slides as $slide) {
                if (isset($slide['status']) && !$slide['status']) continue;
                $image = Journal2Utils::getProperty($slide, 'image', 'no_image.jpg');
                if (!file_exists(DIR_IMAGE . $image)) {
                    $image = 'no_image.jpg';
                }
                list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $image);
                $_slides[] = array(
                    'image'     => $this->model_tool_image->resize($image, $width, $height, $width_orig < $height_orig ? 'w' : 'h'),
                    'name'      => Journal2Utils::getProperty($slide, 'slide_name'),
                    'link'      => $this->getLink(Journal2Utils::getProperty($slide, 'link')),
                    'target'    => Journal2Utils::getProperty($slide, 'link_new_window') ? 'target="_blank"' : ''
                );
            }

            $this->data['slides'] = $_slides;
            if (count($_slides) <= 1) {
                $this->data['js_options']['autoPlay'] = false;
            }

            $this->data['module'] = $module;
            $this->data['preload_images'] = Journal2Utils::getProperty($module_data, 'preload_images', '1');
            $this->data['height'] = $height;

            $this->template = 'journal2/template/journal2/module/slider_simple.tpl';
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

        $this->document->addStyle('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.css');
        $this->document->addScript('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.js');
        $this->document->addScript('catalog/view/javascript/jquery/tabs.js');

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
