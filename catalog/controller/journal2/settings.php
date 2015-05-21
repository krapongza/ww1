<?php
class ControllerJournal2Settings extends Controller {

    private static $CACHEABLE = null;

    private $css_settings = array();
    private $js_settings = array();

    public function index() {
        $this->load->model('journal2/db');
        $this->load->model('tool/image');

        // admin mode
        $this->load->library('user');
        $this->user = new User($this->registry);
        if ($this->user->isLogged()) {
            $this->journal2->html_classes->addClass('is-admin');
        }

        // get current store config settings
        $db_config_settings = $this->model_journal2_db->getConfigSettings();
        foreach ($db_config_settings as $key => $value) {
            $this->journal2->settings->set('config_' . $key, $value);
        }

        // get active skin
        $skin_id = $this->journal2->settings->get('config_active_skin', 1);

        if (!$this->model_journal2_db->skinExists($skin_id)) {
            $skin_id = 1;
        }

        $developer_mode = $this->journal2->settings->get('config_system_settings.developer_mode');
        if (!$developer_mode) {
            self::$CACHEABLE = true;
        }

        $this->journal2->cache->setDeveloperMode($developer_mode);
        $this->journal2->minifier->setMinifyCss((bool)$this->journal2->settings->get('config_system_settings.minify_css'));
        $this->journal2->minifier->setMinifyJs((bool)$this->journal2->settings->get('config_system_settings.minify_js'));

        $this->journal2->cache->setSkinId($skin_id);

        $cache_property = 'settings';

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            // load current skin settings
            $db_skin_settings = $this->model_journal2_db->getSkinSettings($skin_id);

            // all settings
            $all_settings = $this->journal2->settings->getAll();

            // parse settings
            foreach ($db_skin_settings as $key => $value) {
                if (!isset($all_settings[$key])) {
                    trigger_error('Journal Error: Could not parse setting ' . $key . '!');
                    exit();
                }
                if (is_scalar($value)) {
                    $value = array(
                        'value' => $value
                    );
                }
                $value['name'] = $key;
                $value['type'] = $all_settings[$key]['type'];
                if (isset($all_settings[$key]['selector'])) {
                    $value['css'] = array(
                        'selector' => $all_settings[$key]['selector'],
                        'property' => $all_settings[$key]['property']
                    );
                    $this->addCssSettings($value);
                }
                $this->addCpSettings($value);
            }

            $cached = array(
                'settings'  => $this->journal2->settings->getAllSettings(),
                'fonts'     => $this->journal2->google_fonts->getAllFonts(),
                'css'       => $this->css_settings
            );

            if (self::$CACHEABLE === true) {
                $this->journal2->cache->set($cache_property, json_encode($cached));
            }
        } else {
            $cache = json_decode($cache, true);
            $this->css_settings = $cache['css'];
            $this->journal2->settings->setAllSettings($cache['settings']);
            $this->journal2->google_fonts->setAllFonts($cache['fonts']);
        }

        foreach ($db_config_settings as $key => $value) {
            $this->journal2->settings->set('config_' . $key, $value);
        }

        // process some settings
        $this->processSettings();

        // assign css settings
        $this->journal2->css_settings = $this->css_settings;

        // add jquery + jquery ui
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/jquery/jquery-1.8.3.min.js', 'header');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/jquery.ui/jquery-ui-1.8.24.min.js', 'header');
        $this->journal2->minifier->addStyle('catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css');
        if ($this->journal2->html_classes->hasClass('tablet')) {
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/touch-punch/jquery.ui.touch-punch.min.js', 'header');
        }

        // opencart scripts
        $this->journal2->minifier->addScript('catalog/view/javascript/common.js', 'header');
        $this->journal2->minifier->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js', 'header');

        // v1541 compatibility
        if (VERSION === '1.5.4' || VERSION === '1.5.4.1') {
            $this->journal2->minifier->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
            $this->journal2->minifier->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox.js', 'header');
            $this->journal2->minifier->addScript('catalog/view/javascript/jquery/tabs.js', 'header');
        }

        // add owl carousel
        $this->journal2->minifier->addStyle('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.css');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.js');

        // add swipebox
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/swipebox/source/jquery.swipebox.js', 'footer');

        // add magnific popup
        if (!$this->journal2->html_classes->hasClass('mobile')) {
            $this->journal2->minifier->addStyle('catalog/view/theme/journal2/lib/magnific-popup/magnific-popup.css');
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/magnific-popup/jquery.magnific-popup.min.js', 'header');
        }

        // add other plugins
        $this->document->addScript('catalog/view/theme/journal2/lib/lazy/jquery.lazy.1.6.min.js');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/actual/jquery.actual.min.js', 'header');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/hover-intent/jquery.hoverIntent.min.js', 'footer');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/countdown/jquery.countdown.js', 'header');
//        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/responsive-elements/responsive-elements.js', 'footer');
        $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/pnotify/jquery.pnotify.min.js', 'footer');
        if (!$this->journal2->html_classes->hasClass('mobile') && !$this->journal2->html_classes->hasClass('tablet')) {
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/autocomplete2/jquery.autocomplete2.min.js', 'footer');
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/respond/respond.js', 'footer');
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/image-zoom/jquery.imagezoom.min.js', 'header');
            $this->journal2->minifier->addScript('catalog/view/theme/journal2/lib/sticky/jquery.sticky.js', 'footer');
        }
    }

    private function addCssSettings($setting) {
        /* selector */
        $md5_selector = md5($setting['css']['selector']);
        if (!isset($this->css_settings[$md5_selector])) {
            $this->css_settings[$md5_selector] = array(
                'selector'      => $setting['css']['selector'],
                'properties'    => array()
            );
        }

        /* hover selector */
        $hover_selector = isset($setting['css']['hover_selector']) ? $setting['css']['hover_selector'] : $setting['css']['selector'] . ':hover';
        $md5_hover_selector = md5($hover_selector);
        if (!isset($this->css_settings[$md5_hover_selector])) {
            $this->css_settings[$md5_hover_selector] = array(
                'selector'      => $hover_selector,
                'properties'    => array()
            );
        }

        /* expand values */
        switch($setting['type']) {
            case 'j-opt-color':
            case 'j-opt-color-gradient':
                if (Journal2Utils::getProperty($setting, 'value.gradient') !== null) {
                    $this->css_settings[$md5_selector]['properties'][] = preg_replace( '/\s*(?!<\")\/\*[^\*]+\*\/(?!\")\s*/' , '' , Journal2Utils::getProperty($setting, 'value.gradient'));
                } elseif (Journal2Utils::getProperty($setting, 'value.color')) {
                    $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue($setting['css']['property'], Journal2Utils::getColor(Journal2Utils::getProperty($setting, 'value.color')));
                }
                break;
            case 'j-opt-text':
                if (Journal2Utils::getProperty($setting, 'value.text') !== null) {
                    $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue($setting['css']['property'], Journal2Utils::getProperty($setting, 'value.text'));
                }
                break;
            case 'j-opt-icon':

                switch (Journal2Utils::getProperty($setting, 'value.icon_type')) {
                    case 'icon':
                        if (Journal2Utils::getProperty($setting, 'value.icon.icon')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue("content: '%s'", str_replace(array('&#x', ';'), array('\\', ''), Journal2Utils::getProperty($setting, 'value.icon.icon')));
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.font_size')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('font-size', Journal2Utils::getProperty($setting, 'value.options.font_size'));
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.color.value.color')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('color', Journal2Utils::getColor(Journal2Utils::getProperty($setting, 'value.options.color.value.color')));
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.top')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('top', Journal2Utils::getProperty($setting, 'value.options.top') . 'px');
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.left')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('left', Journal2Utils::getProperty($setting, 'value.options.left') . 'px');
                        }
                        break;
                    case 'image':
                        if (Journal2Utils::getProperty($setting, 'value.image')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('content', 'url("image/' . Journal2Utils::getProperty($setting, 'value.image') . '")');
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.font_size')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('font-size', Journal2Utils::getProperty($setting, 'value.options.font_size'));
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.color.value.color')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('color', Journal2Utils::getColor(Journal2Utils::getProperty($setting, 'value.options.color.value.color')));
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.top')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('top', Journal2Utils::getProperty($setting, 'value.options.top') . 'px');
                        }
                        if (Journal2Utils::getProperty($setting, 'value.options.left')) {
                            $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue('left', Journal2Utils::getProperty($setting, 'value.options.left') . 'px');
                        }
                        break;
                }
                break;
            case 'j-opt-image':
                if (Journal2Utils::getProperty($setting, 'value.image') !== null) {
                    $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue($setting['css']['property'], 'image/' . Journal2Utils::getProperty($setting, 'value.image'));
                }
                break;
            case 'j-opt-select':
                if (Journal2Utils::getProperty($setting, 'value') !== null) {
                    $this->css_settings[$md5_selector]['properties'][] = $this->parseCssValue($setting['css']['property'], Journal2Utils::getProperty($setting, 'value'));
                }
                break;
            case 'j-opt-font':
                if (Journal2Utils::getProperty($setting, 'value.font_type') === 'google') {
                    $this->journal2->google_fonts->add(Journal2Utils::getProperty($setting, 'value.font_name'), Journal2Utils::getProperty($setting, 'value.font_subset'), Journal2Utils::getProperty($setting, 'value.font_weight'));
                    $weight = filter_var(Journal2Utils::getProperty($setting, 'value.font_weight'), FILTER_SANITIZE_NUMBER_INT);
                    $this->css_settings[$md5_selector]['properties'][] = 'font-weight: ' . ($weight ? $weight : 400) . $setting['css']['property'];
                    $this->css_settings[$md5_selector]['properties'][] = 'font-family: "' . Journal2Utils::getProperty($setting, 'value.font_name') . '"' . $setting['css']['property'];
                }
                if (Journal2Utils::getProperty($setting, 'value.font_type') === 'system') {
                    $this->css_settings[$md5_selector]['properties'][] = 'font-weight: ' . Journal2Utils::getProperty($setting, 'value.font_weight') . $setting['css']['property'];
                    $this->css_settings[$md5_selector]['properties'][] = 'font-family: ' . Journal2Utils::getProperty($setting, 'value.font_family') . $setting['css']['property'];
                }
                if (Journal2Utils::getProperty($setting, 'value.font_type') !== 'none') {
                    $this->css_settings[$md5_selector]['properties'][] = 'font-style: ' . Journal2Utils::getProperty($setting, 'value.font_style') . $setting['css']['property'];
                    $this->css_settings[$md5_selector]['properties'][] = 'font-size: ' . Journal2Utils::getProperty($setting, 'value.font_size') . $setting['css']['property'];
                    $this->css_settings[$md5_selector]['properties'][] = 'text-transform: ' . Journal2Utils::getProperty($setting, 'value.text_transform') . $setting['css']['property'];
                    if (Journal2Utils::getProperty($setting, 'value.letter_spacing')) {
                        $this->css_settings[$md5_selector]['properties'][] = 'letter-spacing: ' . Journal2Utils::getProperty($setting, 'value.letter_spacing') . 'px' . $setting['css']['property'];
                    }
                }
                if (Journal2Utils::getProperty($setting, 'value.color.value.color')) {
                    $color = Journal2Utils::getColor(Journal2Utils::getProperty($setting, 'value.color.value.color'));
                    $this->css_settings[$md5_selector]['properties'][] = 'color: ' . $color . $setting['css']['property'];
                    $this->journal2->settings->set($setting['name'] . ':color', $color);
                }
                break;
            case 'j-opt-background':
                foreach (Journal2Utils::getBackgroundCssProperties($setting) as $sett) {
                    $this->css_settings[$md5_selector]['properties'][] = $sett;
                    $parts = explode(':', $sett);
                    if (count($parts) > 1 && strlen(trim($parts[0])) && strlen(trim($parts[1]))) {
                        $this->journal2->settings->set($setting['name'] . ':' . trim($parts[0]), trim($parts[1]));
                    }
                }
                break;
            case 'j-opt-border':
                foreach (Journal2Utils::getBorderCssProperties($setting) as $sett) {
                    $this->css_settings[$md5_selector]['properties'][] = $sett;
                    $parts = explode(':', $sett);
                    if (count($parts) > 1 && strlen(trim($parts[0])) && strlen(trim($parts[1]))) {
                        $this->journal2->settings->set($setting['name'] . ':' . trim($parts[0]), trim($parts[1]));
                    }
                }
                break;
        }
    }

    private function parseCssValue($property, $value) {
        return strpos($property, '%s') === FALSE ? $property . ': ' . $value : str_replace('%s',$value, $property);
    }

    private function addCpSettings($setting) {
        switch($setting['type']) {
            case 'j-opt-color':
            case 'j-opt-color-gradient':
                if (Journal2Utils::getProperty($setting, 'value.color') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getColor(Journal2Utils::getProperty($setting, 'value.color')));
                }
                break;
            case 'j-opt-text':
                if (Journal2Utils::getProperty($setting, 'value.text') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value.text'));
                }
                break;
            case 'j-opt-textarea':
                if (Journal2Utils::getProperty($setting, 'value.text') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value.text'));
                }
                break;
            case 'j-opt-text-lang':
                if (Journal2Utils::getProperty($setting, 'value') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value.' . $this->config->get('config_language_id')));
                }
                break;
            case 'j-opt-image':
                if (Journal2Utils::getProperty($setting, 'value.image') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value.image'));
                }
                break;
            case 'j-opt-select':
                if (Journal2Utils::getProperty($setting, 'value') !== null) {
                    $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value'));
                }
                break;
            case 'j-opt-font':
            case 'j-opt-border':
            break;
            case 'j-opt-background':
               break;
            case 'j-opt-icon':
                $icon = Journal2Utils::getIconOptions2(Journal2Utils::getProperty($setting, 'value'));
                $this->journal2->settings->set($setting['name'], $icon);
                break;
            case 'j-opt-items-per-row':
                $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value'));
                break;
            case 'j-opt-slider':
                $this->journal2->settings->set($setting['name'], Journal2Utils::getProperty($setting, 'value'));
                break;
            case 'j-opt-sharethis':
                $share_this_data = json_decode(file_get_contents(DIR_SYSTEM . 'journal2/data/share_this.json'), true);
                $items = array();
                foreach ($setting as $k => $v) {
                    if (is_numeric($k)) {
                        $items[] = array(
                            'class' => 'st_' . str_replace('st_li_', '', $v['id']),
                            'name'  => $share_this_data[$v['id']]['name']
                        );
                    }
                }
                $this->journal2->settings->set('config_share_buttons', $items);
                break;
            default:
                trigger_error($setting['type'] . ' not parsed!');
        }
        return false;
    }

    private function processSettings() {
        if ($this->config->get('config_maintenance')) {
            $this->journal2->html_classes->addClass('maintenance-mode');
        }
        if ($this->journal2->settings->get('responsive_design')) {
            $this->journal2->html_classes->addClass('responsive-layout');
        }

        if ($this->journal2->settings->get('mobile_menu_on', 'phone') === 'tablet') {
            $this->journal2->html_classes->addClass('mobile-menu-on-tablet');
        }
        if($this->journal2->settings->get('extended_layout', '0') === '1'){
            $this->journal2->html_classes->addClass('extended-layout');
        }
        if($this->journal2->settings->get('boxed_header', '0') === '1'){
            $this->journal2->html_classes->addClass('boxed-header');
        }
        if($this->journal2->settings->get('header_type', 'default') === 'center' || $this->journal2->settings->get('header_type', 'default') === 'mega'){
            $this->journal2->html_classes->addClass('header-center');
        }

        if($this->journal2->settings->get('header_type', 'default') === 'default' && $this->journal2->settings->get('sticky_header', '0') === '1') {
            $this->journal2->html_classes->addClass('header-default-sticky');
        }
        if($this->journal2->settings->get('header_type', 'default') === 'center' && $this->journal2->settings->get('sticky_header', '0') === '1') {
            $this->journal2->html_classes->addClass('header-center-sticky');
        }
        if($this->journal2->settings->get('header_type', 'default') === 'mega' && $this->journal2->settings->get('sticky_header', '0') === '1') {
            $this->journal2->html_classes->addClass('header-center-sticky');
        }
        if($this->journal2->settings->get('header_type', 'default') === 'extended' && $this->journal2->settings->get('sticky_header', '0') === '1') {
            $this->journal2->html_classes->addClass('header-extended-sticky');
        }
        $this->journal2->html_classes->addClass('backface');

        $cols = 0;
        if ($this->journal2->page->hasModules('column_left')) {
            $cols ++;
        }
        if ($this->journal2->page->hasModules('column_right')) {
            $cols ++;
        }
        if ($cols == 1){
            $this->journal2->html_classes->addClass('one-column');
        }
        if ($cols == 2){
            $this->journal2->html_classes->addClass('two-columns');
        }
        $this->journal2->settings->set('config_columns_count', $cols);
        $this->journal2->settings->set('product_grid_classes', Journal2Utils::getProductGridClasses($this->journal2->settings->get('category_page_products_per_row'), $this->journal2->settings->get('site_width', 1024), $cols));
        if (!$this->journal2->settings->get('related_products_carousel')) {
            $this->journal2->settings->set('related_products_grid_classes', Journal2Utils::getProductGridClasses($this->journal2->settings->get('related_products_items_per_row'), $this->journal2->settings->get('site_width', 1024), $cols));
        }

        /* second images */
        if (!$this->journal2->mobile_detect->isMobile() && $this->journal2->settings->get('product_grid_second_image') === '1') {
            $this->journal2->html_classes->addClass('product-grid-second-image');
        }

        if (!$this->journal2->mobile_detect->isMobile() && $this->journal2->settings->get('product_list_second_image') === '1') {
            $this->journal2->html_classes->addClass('product-list-second-image');
        }

        // product views
        if (($this->journal2->page->getType() === 'product' || $this->journal2->page->getType() === 'quickview')  && $this->journal2->settings->get('product_page_options_views')) {
            $this->load->model('journal2/product');
            $this->journal2->settings->set('product_views', $this->model_journal2_product->getProductViews($this->journal2->page->getId()));
        }

        $this->processFooter();
    }

    private function processFooter() {
        /* copyright text */
        $copyright = $this->journal2->settings->get('config_copyright', array());
        $copyright_text = Journal2Utils::getProperty($copyright, 'value.' . $this->config->get('config_language_id'));
        $this->journal2->settings->set('config_copyright', $copyright_text);

        /* payment methods */
        $payments = $this->journal2->settings->get('config_payments.payments', array());
        $payments = Journal2Utils::sortArray($payments);
        $payment_methods = array();
        foreach ($payments as $payment) {
            $image = Journal2Utils::getProperty($payment, 'image', 'no_image.jpg');
            if (!file_exists(DIR_IMAGE . $image)) {
                $image = 'no_image.jpg';
            }
            list($width, $height) = getimagesize(DIR_IMAGE . $image);
            $payment_methods[] = array(
                'image'     => 'image/' . Journal2Utils::getProperty($payment, 'image', 'no_image.jpg'),
                'name'      => Journal2Utils::getProperty($payment, 'name.value.' . $this->config->get('config_language_id')),
                'url'       => Journal2Utils::getProperty($payment, 'link.value.text'),
                'target'    => Journal2Utils::getProperty($payment, 'new_window') ? ' target="_blank"' : '',
                'width'     => $width,
                'height'    => $height
            );
        }
        $this->journal2->settings->set('config_payments', $payment_methods);

        /* custom classes */
        $classes = array();
        if (!$copyright_text) $classes[] = 'no-copyright';
        if (!$payment_methods) $classes[] = 'no-payments';
        $this->journal2->settings->set('config_footer_classes', implode(' ', $classes));
    }

}
?>