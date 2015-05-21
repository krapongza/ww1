<?php
class ControllerJournal2Menu extends Controller {

    private static $CACHEABLE = null;
    private $mega_has_random_products = false;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->language->load('common/header');
        $this->language->load('account/login');
        $this->language->load('account/logout');
        $this->language->load('account/register');
        $this->language->load('common/footer');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/information');
        $this->load->model('journal2/menu');
        $this->load->model('journal2/product');
        $this->load->model('tool/image');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = !((bool)$this->journal2->settings->get('config_system_settings.developer_mode'));
        }
    }

    public function header($menu) {
        $wishlist = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
        $compare = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;
        $cache_property = 'config_' . $menu;
        $cache = $wishlist || $compare ? null : $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $items = $this->journal2->settings->get('config_' . $menu . '.items', array());
            $this->data['items'] = $this->generateMenu($items);
            $this->template = 'journal2/template/journal2/menu/header.tpl';

            $cache = $this->render();
            if (!$wishlist && !$compare && self::$CACHEABLE === true) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $this->journal2->settings->set('config_' . $menu, $cache);
    }

    public function footer($menu) {
        $wishlist = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
        $compare = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;
        $cache_property = 'config_' . $menu;
        $cache = $wishlist || $compare ? null : $this->journal2->cache->get($cache_property);
        $has_random_products = false;

        if ($cache === null || self::$CACHEABLE !== true) {
            $rows = $this->journal2->settings->get('config_' . $menu . '.rows', array());
            $rows = Journal2Utils::sortArray($rows);

            $this->data['rows'] = array();

            foreach ($rows as $row) {
                if (isset($row['status']) && !$row['status']) continue;
                $row_css = Journal2Utils::getBackgroundCssProperties(Journal2Utils::getProperty($row, 'background'));
                if (Journal2Utils::getProperty($row, 'bottom_spacing') !== null) {
                    $row_css[] = 'margin-bottom: ' . Journal2Utils::getProperty($row, 'bottom_spacing') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_top') !== null) {
                    $row_css[] = 'padding-top: ' . Journal2Utils::getProperty($row, 'padding_top') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_right') !== null) {
                    $row_css[] = 'padding-right: ' . Journal2Utils::getProperty($row, 'padding_right') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_bottom') !== null) {
                    $row_css[] = 'padding-bottom: ' . Journal2Utils::getProperty($row, 'padding_bottom') . 'px';
                }
                if (Journal2Utils::getProperty($row, 'padding_left') !== null) {
                    $row_css[] = 'padding-left: ' . Journal2Utils::getProperty($row, 'padding_left') . 'px';
                }
                $temp = array(
                    'type' => '',
                    'css' => implode('; ', $row_css),
                    'columns' => array(),
                    'contacts' => array(
                        'left' => array(),
                        'right' => array()
                    )
                );
                switch (Journal2Utils::getProperty($row, 'type')) {
                    case 'columns':
                        $temp['type'] = 'columns';
                        $columns = Journal2Utils::getProperty($row, 'columns');
                        $columns = Journal2Utils::sortArray($columns);
                        $temp['classes'] = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($row, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024), 0);
                        foreach ($columns as $column) {
                            switch (Journal2Utils::getProperty($column, 'type')) {
                                case 'text':
                                    /* icon css */
                                    $css = array();

                                    if (Journal2Utils::getColor(Journal2Utils::getProperty($column, 'icon_bg_color.value.color'))) {
                                        $css[] = 'background-color: ' . Journal2Utils::getColor(Journal2Utils::getProperty($column, 'icon_bg_color.value.color'));
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_width')) {
                                        $css[] = 'width: ' . Journal2Utils::getProperty($column, 'icon_width') . 'px';
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_height')) {
                                        $css[] = 'height: ' . Journal2Utils::getProperty($column, 'icon_height') . 'px';
                                        $css[] = 'line-height: ' . Journal2Utils::getProperty($column, 'icon_height') . 'px';
                                    }
                                    if (Journal2Utils::getProperty($column, 'icon_border')) {
                                        $css = array_merge($css, Journal2Utils::getBorderCssProperties(Journal2Utils::getProperty($column, 'icon_border')));
                                    }

                                    $temp['columns'][] = array(
                                        'type' => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'text' => Journal2Utils::getProperty($column, 'text.' . $this->config->get('config_language_id')),
                                        'has_icon' => Journal2Utils::getProperty($column, 'icon_status'),
                                        'icon_position' => Journal2Utils::getProperty($column, 'icon_position', 'top'),
                                        'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($column, 'icon')),
                                        'icon_css' => implode('; ', $css),
                                    );
                                    break;
                                case 'menu':

                                    $temp['columns'][] = array(
                                        'type' => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'items' => $this->generateMenu(Journal2Utils::getProperty($column, 'items', array()))
                                    );
                                    break;
                                case 'newsletter':
                                    $temp['columns'][] = array(
                                        'type'      => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title'     => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'content'   => $this->getChild('module/journal2_newsletter', array (
                                            'module_id' => Journal2Utils::getProperty($column, 'newsletter_id'),
                                            'layout_id' => -1,
                                            'position'  => 'footer'
                                        ))
                                    );
                                    break;
                                case 'products':
                                    $products = array();
                                    $limit = Journal2Utils::getProperty($column, 'items_limit', 5);

                                    $this->data['image_width'] = $this->journal2->settings->get('footer_product_image_width', 50);
                                    $this->data['image_height'] = $this->journal2->settings->get('footer_product_image_height', 50);
                                    $this->data['image_resize_type'] = 'crop';
                                    $this->data['text_tax'] = $this->language->get('text_tax');
                                    $this->data['button_cart'] = $this->language->get('button_cart');
                                    $this->data['button_wishlist'] = $this->language->get('button_wishlist');
                                    $this->data['button_compare'] = $this->language->get('button_compare');

                                    switch (Journal2Utils::getProperty($column, 'section_type')) {
                                        case 'module':
                                            switch (Journal2Utils::getProperty($column, 'module_type')) {
                                                case 'featured':
                                                    $products = $this->model_journal2_product->getFeatured($limit);
                                                    break;
                                                case 'bestsellers':
                                                    $products = $this->model_journal2_product->getBestsellers($limit);
                                                    break;
                                                case 'specials':
                                                    $products = $this->model_journal2_product->getSpecials($limit);
                                                    break;
                                                case 'latest':
                                                    $products = $this->model_journal2_product->getLatest($limit);
                                                    break;
                                            }
                                            break;
                                        case 'category':
                                            $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($column, 'category.data.id'));
                                            if (!$category_info) continue;
                                            $products = $this->model_journal2_product->getProductsByCategory($category_info['category_id'], $limit);
                                            break;
                                        case 'manufacturer':
                                            $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($column, 'manufacturer.data.id'));
                                            if (!$manufacturer) continue;
                                            $products = $this->model_journal2_product->getProductsByManufacturer($manufacturer['manufacturer_id'], $limit);
                                            break;
                                        case 'custom':
                                            foreach (Journal2Utils::getProperty($column, 'products', array()) as $product) {
                                                $result = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($product, 'data.id'));
                                                if (!$result) continue;
                                                $products[] = $result;
                                            }
                                            break;
                                        case 'random':
                                            $has_random_products = true;
                                            $random_type = Journal2Utils::getProperty($column, 'random_from', 'all');
                                            $category_id = $random_type === 'category' ? Journal2Utils::getProperty($column, 'random_from_category.id', -1) : -1;
                                            $random_products = $this->model_journal2_product->getRandomProducts($limit, $category_id);
                                            foreach ($random_products as $product) {
                                                $result = $this->model_catalog_product->getProduct($product['product_id']);
                                                if (!$result) continue;
                                                $products[] = $result;
                                            }
                                            break;
                                    }

                                    $products_data = array();

                                    foreach ($products as $product) {
                                        $image = $this->getProductImage($product['image'] ? $product['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);

                                        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                                            $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                                        } else {
                                            $price = false;
                                        }

                                        if ((float)$product['special']) {
                                            $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
                                        } else {
                                            $special = false;
                                        }

                                        if ($this->config->get('config_tax')) {
                                            $tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price']);
                                        } else {
                                            $tax = false;
                                        }

                                        if ($this->config->get('config_review_status')) {
                                            $rating = $product['rating'];
                                        } else {
                                            $rating = false;
                                        }

                                        $product_sections = isset($this->data['items'][$product['product_id']]) ? $this->data['items'][$product['product_id']]['section_class'] : array();

                                        $results = $this->model_catalog_product->getProductImages($product['product_id']);

                                        $image2 = false;

                                        if (!$this->journal2->html_classes->hasClass('mobile') && count($results) > 0) {
                                            //$image2 = $this->getProductImage($results[0]['image'] ? $results[0]['image'] : 'data/journal2/no_image_large.jpg', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']);
											$image2 = $this->getProductImage($results[0]['image'] ? $results[0]['image'] : 'data/journal2/no_image_large.jpg', 200, 300, $this->data['image_resize_type']);
                                        }

                                        $products_data[] = array(
                                            'product_id'    => $product['product_id'],
                                            'section_class' => $product_sections,
                                            'thumb'         => $image,
                                            'thumb2'        => $image2,
                                            'dummy'         => $this->model_tool_image->resize('data/journal2/transparent.png', $this->data['image_width'], $this->data['image_height'], $this->data['image_resize_type']),
                                            'name'          => $product['name'],
                                            'price'         => $price,
                                            'special'       => $special,
                                            'rating'        => $rating,
                                            'description'   => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                                            'tax'           => $tax,
                                            'reviews'       => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                            'href'          => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                                            'labels'        => $this->model_journal2_product->getLabels($product['product_id'])
                                        );
                                    }

                                    $temp['columns'][] = array(
                                        'type' => Journal2Utils::getProperty($column, 'type', 'text'),
                                        'title' => Journal2Utils::getProperty($column, 'title.value.' . $this->config->get('config_language_id')),
                                        'products' => $products_data
                                    );
                                break;
                            }
                        }
                        break;
                    case 'contacts':
                        $temp['type'] = 'contacts';
                        $contacts = Journal2Utils::getProperty($row, 'contacts');
                        $contacts = Journal2Utils::sortArray($contacts);
                        foreach ($contacts as $contact) {
                            $position = Journal2Utils::getProperty($contact, 'position');
                            $icon = Journal2Utils::getIconOptions($contact);
                            $temp['contacts'][$position][] = array(
                                'link' => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($contact, 'link')),
                                'target' => Journal2Utils::getProperty($contact, 'target') ? 'target="_blank"' : '',
                                'name' => Journal2Utils::getProperty($contact, 'name.value.' . $this->config->get('config_language_id')),
                                'tooltip' => Journal2Utils::getProperty($contact, 'tooltip'),
                                'icon_left' => $icon['left'],
                                'icon_right' => $icon['right']
                            );
                        }
                        break;
                }
                $this->data['rows'][] = $temp;
            }

            $this->template = 'journal2/template/journal2/menu/footer.tpl';

            $cache = $this->render();
            if (!$wishlist && !$compare && self::$CACHEABLE === true && !$has_random_products) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $this->journal2->settings->set('config_' . $menu, $cache);
    }

    public function mega($menu_name) {
        $wishlist = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0;
        $compare = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;
        $cache_property = 'config_' . $menu_name;
        $cache = $wishlist || $compare ? null : $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true) {
            $menu_items = $this->journal2->settings->get('config_' . $menu_name . '.items', array());
            $menu_items = Journal2Utils::sortArray($menu_items);

            $display = $this->journal2->settings->get('config_' . $menu_name . '.options.display', 'table') === 'floated' ? 'floated' : 'table';
            $this->data['display'] = $display;
            $this->data['table_css_style'] = '';

            $this->data['menu_items'] = array(
                'table' => array(),
                'left'  => array(),
                'right' => array()
            );

            foreach ($menu_items as $key => $menu_item) {
                if (!Journal2Utils::getProperty($menu_item, 'status', 1)) continue;

                $float = Journal2Utils::getProperty($menu_item, 'float', 'left');

                $class = Journal2Utils::getProperty($menu_item, 'hide_on_mobile') ? 'hide-on-mobile' : '';
                if ($class === 'hide-on-mobile' && ($this->journal2->mobile_detect->isMobile() || $this->journal2->mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
                    unset($menu_item[$key]);
                    continue;
                }

                if ($display === 'floated') {
                    $class .= ' float-' . $float;
                }

                $classes = Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($menu_item, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024));
                $menu = array(
                    'name' => '',
                    'href' => '',
                    'items' => array(),
                    'mixed_columns' => array(),
                    'type' => '',
                    'class' => $class,
                    'classes' => $classes,
                    'limit' => Journal2Utils::getProperty($menu_item, 'items_limit', 0),
                    'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($menu_item, 'icon')),
                    'hide_text' => Journal2Utils::getProperty($menu_item, 'hide_text')
                );
                $image_width = Journal2Utils::getProperty($menu_item, 'image_width');
                $image_height = Journal2Utils::getProperty($menu_item, 'image_height');
                $image_resize_type = Journal2Utils::getProperty($menu_item, 'image_type', 'fit');

                $this->generateMenuItem($menu, $menu_item, $image_width, $image_height, $image_resize_type);

                $name_overwrite = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'));
                if ($name_overwrite) {
                    $menu['name'] = $name_overwrite;
                }

                if ($menu['hide_text']) {
                    $menu['class'] .= ' icon-only';
                }

                if ($display === 'table') {
                    $this->data['menu_items']['table'][] = $menu;
                } else {
                    $this->data['menu_items'][$float][] = $menu;
                }

            }

            if ($display === 'table') {
                $this->data['menu_items'] = $this->data['menu_items']['table'];
                $this->data['table_css_style'] = $this->journal2->settings->get('config_' . $menu_name . '.options.table_layout', 'fixed');
            } else {
                $this->data['menu_items'] = array_merge($this->data['menu_items']['left'], array_reverse($this->data['menu_items']['right']));
            }

            $this->data['button_cart'] = $this->language->get('button_cart');
            $this->data['button_wishlist'] = $this->language->get('button_wishlist');
            $this->data['button_compare'] = $this->language->get('button_compare');
            $this->template = 'journal2/template/journal2/menu/main.tpl';

            $cache = $this->render();
            if (!$wishlist && !$compare && self::$CACHEABLE === true && !$this->mega_has_random_products) {
                $this->journal2->cache->set($cache_property, $cache);
            }
        }

        $this->journal2->settings->set('config_' . $menu_name, $cache);
    }

    private function generateMenu($items) {
        $items = Journal2Utils::sortArray($items);
        foreach ($items as $key => &$item) {
            $icon = Journal2Utils::getIconOptions($item);
            /* menu href */
            $href = null;
            $name = null;
            $target = $item['target'] ? ' target="_blank"' : '';
            $class = Journal2Utils::getProperty($item, 'hide_on_mobile') ? 'hide-on-mobile' : '';
            if ($class === 'hide-on-mobile' && ($this->journal2->mobile_detect->isMobile() || $this->journal2->mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
                unset($items[$key]);
                continue;
            }
            /* menu type */
            switch ($item['menu']['menu_type']) {
                case 'category':
                    $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
                    if (!$category_info) continue;
                    $name = $category_info['name'];
                    $href = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                    break;
                case 'product':
                    $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
                    if (!$product_info) continue;
                    $name = $product_info['name'];
                    $href = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                    break;
                case 'manufacturer':
                    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
                    if (!$manufacturer_info) continue;
                    $name = $manufacturer_info['name'];
                    $href = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                    break;
                case 'information':
                    $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($item, 'menu.menu_item.id', -1));
                    if (!$information_info) continue;
                    $name = $information_info['title'];
                    $href = $this->url->link('information/information', 'information_id=' .  $information_info['information_id']);
                    break;
                case 'popup':
                    $href = "javascript:Journal.openPopup('{$item['menu']['menu_item']}')";
                    break;
                case 'opencart':
                    $customer_name = null;
                    switch ($item['menu']['menu_item']['page']) {
                        case 'login':
                            $item['menu']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                            $customer_name = $this->customer->getFirstName();
                            break;
                        case 'register':
                            $item['menu']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                            break;
                        case 'account/wishlist':
                            $class .= ' wishlist-total';
                            break;
                        case 'product/compare':
                            $class .= ' compare-total';
                        default:
                    }
                    $name = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($item['menu']['menu_item']['page']);
                    $href = $item['menu']['menu_item']['page'] === 'common/home' ? $this->journal2->config->base_url : $this->url->link($item['menu']['menu_item']['page'], '', 'SSL');
                    break;
                case 'custom':
                    $name = Journal2Utils::getProperty($item, 'name.value.' . $this->config->get('config_language_id'), '');
                    $href = Journal2Utils::getProperty($item, 'menu.menu_item.url');
                    break;
            }
            $overwrite_name = Journal2Utils::getProperty($item, 'name.value.' . $this->config->get('config_language_id'), '');
            if ($overwrite_name) {
                $name = $overwrite_name;
            }
            if (Journal2Utils::getProperty($item, 'mobile_view') === 'icon') {
                $class .= ' icon-only';
            }
            if (Journal2Utils::getProperty($item, 'mobile_view') === 'text') {
                $class .= ' text-only';
            }
            $item = array(
                'icon_left' => $icon['left'],
                'icon_right'=> $icon['right'],
                'class'     => $class ? ' class="' . $class .'"' : '',
                'href'      => $href,
                'name'      => $name,
                'target'    => $target,
                'subcategories' => array()
            );
        }
        return $items;
    }
	
    private function generateMenuItem (&$menu, $menu_item, $image_width, $image_height, $image_resize_type) {
        $items_limit = Journal2Utils::getProperty($menu_item, 'items_limit', 0);

        switch (Journal2Utils::getProperty($menu_item, 'type')) {
            /* categories menu */
            case 'categories':
                switch (Journal2Utils::getProperty($menu_item, 'categories.render_as', 'megamenu')) {
                    case 'megamenu':
                        $menu['show'] = Journal2Utils::getProperty($menu_item, 'categories.show');
                        switch ($menu['show']) {
                            case 'links':
                                $menu['show_class'] = 'menu-no-image';
                                break;
                            case 'image':
                                $menu['show_class'] = 'menu-no-links';
                                break;
                            default:
                                $menu['show_class'] = '';
                        }
                        $menu['classes'] .= ' menu-image-' . Journal2Utils::getProperty($menu_item, 'categories.image_position', 'right');
                        $menu['type'] = 'mega-menu-categories';
                        $links_type = Journal2Utils::getProperty($menu_item, 'categories.links_type', 'categories');
                        switch (Journal2Utils::getProperty($menu_item, 'categories.type')) {
                            /* existing categories */
                            case 'existing':
                                $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                if (!$parent_category) continue;
                                $menu['name'] = $parent_category['name'];
                                $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                                switch ($links_type) {
                                    case 'categories':
                                        $subcategories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                        foreach ($subcategories as $subcategory) {
                                            $submenu = array();
                                            $sub_categories = $this->model_catalog_category->getCategories($subcategory['category_id']);
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id'] . '_' . $sub_category['category_id']),
                                                    'image' => $this->getImage($sub_category, $image_width, $image_height, $image_resize_type),
                                                    'dummy' => $this->getProductDummyImage($image_width, $image_height)
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $subcategory['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id']),
                                                'items' => $submenu,
                                                'image' => $this->getImage($subcategory, $image_width, $image_height, $image_resize_type),
                                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                    case 'products':
//                                        var_dump($menu_item); die();
                                        $subcategories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                        foreach ($subcategories as $subcategory) {
                                            $submenu = array();
                                            $sub_categories = $this->getProductsByCategory($subcategory['category_id'], $items_limit ? $items_limit : 5);
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id'] . '&product_id=' . $sub_category['product_id']),
                                                    'image' => $this->getImage($sub_category, $image_width, $image_height, $image_resize_type),
                                                    'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $subcategory['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $subcategory['category_id']),
                                                'items' => $submenu,
                                                'image' => $this->getImage($subcategory, $image_width, $image_height, $image_resize_type),
                                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                }

                                break;
                            /* custom categories */
                            case 'custom':
                                switch ($links_type) {
                                    case 'categories':
                                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                        $menu['href'] = 'javascript:;';
                                        foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                            $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                            if (!$parent_category) continue;
                                            $sub_categories = $this->model_catalog_category->getCategories(Journal2Utils::getProperty($category, 'data.id'));
                                            $submenu = array();
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id'] . '_' . $sub_category['category_id']),
                                                    'image' => $this->getImage($sub_category, $image_width, $image_height, $image_resize_type),
                                                    'dummy' => $this->getProductDummyImage($image_width, $image_height)
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $parent_category['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id']),
                                                'image' => $this->getImage($parent_category, $image_width, $image_height, $image_resize_type),
                                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                'items' => $submenu,
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                    case 'products':
                                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                        $menu['href'] = 'javascript:;';
                                        foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                            $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                            if (!$parent_category) continue;
                                            $sub_categories = $this->getProductsByCategory(Journal2Utils::getProperty($category, 'data.id'));
                                            $submenu = array();
                                            foreach ($sub_categories as $sub_category) {
                                                $submenu[] = array(
                                                    'name' => $sub_category['name'],
                                                    'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '&product_id=' . $sub_category['product_id']),
                                                    'image' => $this->getImage($sub_category, $image_width, $image_height, $image_resize_type),
                                                    'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                );
                                            }
                                            $menu['items'][] = array(
                                                'name' => $parent_category['name'],
                                                'href' => $this->url->link('product/category', 'path=' . $parent_category['category_id']),
                                                'image' => $this->getImage($parent_category, $image_width, $image_height, $image_resize_type),
                                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                                'items' => $submenu,
                                                'image-class' => count($submenu) ? '' : 'full-img'
                                            );
                                        }
                                        break;
                                }
                                break;
                        }
                        break;
                    case 'dropdown':
                        $menu['type'] = 'drop-down';
                        switch (Journal2Utils::getProperty($menu_item, 'categories.type')) {
                            /* existing categories */
                            case 'existing':
                                $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'categories.top.id'));
                                if (!$parent_category) continue;
                                $menu['name'] = $parent_category['name'];
                                $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                                $menu['subcategories'] = $this->generateMultiLevelCategoryMenu($parent_category['category_id']);
                                break;
                            /* custom categories */
                            case 'custom':
                                $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                                $menu['href'] = 'javascript:;';
                                $menu['subcategories'] = array();
                                foreach (Journal2Utils::getProperty($menu_item, 'categories.items', array()) as $category) {
                                    $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($category, 'data.id'));
                                    if (!$category_info) continue;
                                    $menu['subcategories'][] = array(
                                        'name' => $category_info['name'],
                                        'href' => $this->url->link('product/category', 'path=' . $category_info['category_id']),
                                        'subcategories' => $this->generateMultiLevelCategoryMenu($category_info['category_id'])
                                    );
                                }
                                break;
                        }
                        break;
                }
                break;

            /* products menu */
            case 'products':
                $menu['type'] = 'mega-menu-products';
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                switch (Journal2Utils::getProperty($menu_item, 'products.source')) {
                    /* products from category */
                    case 'category':
                        $parent_category = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'products.category.id'));
                        if (!$parent_category) continue;
                        $menu['name'] = $parent_category['name'];
                        $menu['href'] = $this->url->link('product/category', 'path=' . $parent_category['category_id']);
                        $products = $this->getProductsByCategory($parent_category['category_id'], $items_limit ? $items_limit : 5);
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', 'path=' . $parent_category['category_id'] . '&product_id=' . $product['product_id']),
                                'image' => $this->getImage($product, $image_width, $image_height, $image_resize_type),
                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;
                    /* products from module */
                    case 'module':
                        $products = array();
                        switch (Journal2Utils::getProperty($menu_item, 'products.module_type')) {
                            case 'featured':
                                $products = $this->getFeatured($items_limit ? $items_limit : 5);
                                $this->load->language('module/featured');
                                break;
                            case 'special':
                                $products = $this->getSpecials($items_limit ? $items_limit : 5);
                                $this->load->language('module/special');
                                break;
                            case 'bestseller':
                                $products = $this->getBestsellers($items_limit ? $items_limit : 5);
                                $this->load->language('module/bestseller');
                                break;
                            case 'latest':
                                $products = $this->getLatest($items_limit ? $items_limit : 5);
                                $this->load->language('module/latest');
                                break;
                        }
                        $menu['name'] = $this->language->get('heading_title');
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                                'image' => $this->getImage($product, $image_width, $image_height, $image_resize_type),
                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;

                    /* products from manufacturer */
                    case 'manufacturer':
                        $manufacturer = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'products.manufacturer.id'));
                        if (!$manufacturer) continue;
                        $menu['name'] = $manufacturer['name'];
                        $menu['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']);
                        $products = $this->getProductsByManufacturer($manufacturer['manufacturer_id']);
                        foreach ($products as $product) {
                            $menu['items'][] = array(
                                'product_id' => $product['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($product['product_id']),
                                'name' => $product['name'],
                                'href' => $this->url->link('product/product', '&manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id']),
                                'image' => $this->getImage($product, $image_width, $image_height, $image_resize_type),
                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                'price' => $this->getProductPrice($product),
                                'special' => $this->getProductSpecialPrice($product),
                                'rating' => $this->config->get('config_review_status') ? $product['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$product['reviews']),
                                'items' => array()
                            );
                        }
                        break;

                    /* custom products */
                    case 'custom':
                        $products = Journal2Utils::getProperty($menu_item, 'products.items', array());
                        foreach ($products as $product) {
                            $result = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($product, 'data.id'));
                            if (!$result) continue;
                            $menu['items'][] = array(
                                'product_id' => $result['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($result['product_id']),
                                'name' => $result['name'],
                                'href' => $this->url->link('product/product', '&product_id=' . $result['product_id']),
                                'image' => $this->getImage($result, $image_width, $image_height, $image_resize_type),
                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                'price' => $this->getProductPrice($result),
                                'special' => $this->getProductSpecialPrice($result),
                                'rating' => $this->config->get('config_review_status') ? $result['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                                'items' => array()
                            );
                        }
                        break;

                    /* random */
                    case 'random':
                        $this->mega_has_random_products = true;
                        $this->load->model('journal2/product');
                        $random_products = $this->model_journal2_product->getRandomProducts();
                        foreach ($random_products as $product) {
                            $result = $this->model_catalog_product->getProduct($product['product_id']);
                            if (!$result) continue;
                            $menu['items'][] = array(
                                'product_id' => $result['product_id'],
                                'labels' => $this->model_journal2_product->getLabels($result['product_id']),
                                'name' => $result['name'],
                                'href' => $this->url->link('product/product', '&product_id=' . $result['product_id']),
                                'image' => $this->getImage($result, $image_width, $image_height, $image_resize_type),
                                'dummy' => $this->getProductDummyImage($image_width, $image_height),
                                'price' => $this->getProductPrice($result),
                                'special' => $this->getProductSpecialPrice($result),
                                'rating' => $this->config->get('config_review_status') ? $result['rating'] : false,
                                'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                                'items' => array()
                            );
                        }
                        break;
                }
                break;

            /* manufacturer menu */
            case 'manufacturers':
                $menu['type'] = 'mega-menu-brands';
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                $manufacturers = array();
                switch (Journal2Utils::getProperty($menu_item, 'manufacturers.type')) {
                    case 'all':
                        $manufacturers = $this->model_catalog_manufacturer->getManufacturers();
                        if ($items_limit > 0) {
                            $manufacturers = array_slice($manufacturers, 0, $items_limit);
                        }
                        break;
                    case 'custom':
                        foreach (Journal2Utils::getProperty($menu_item, 'manufacturers.items', array()) as $manufacturer) {
                            $manufacturers[] = array(
                                'manufacturer_id' => Journal2Utils::getProperty($manufacturer, 'data.id', -1)
                            );
                        }
                }
                $show_name = Journal2Utils::getProperty($menu_item, 'manufacturers.name');
                foreach ($manufacturers as $manufacturer) {
                    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer['manufacturer_id']);
                    if (!$manufacturer_info) continue;

                    $menu['items'][] = array(
                        'name' => $manufacturer_info['name'],
                        'show'  => Journal2Utils::getProperty($menu_item, 'manufacturers.show', 'both'),
                        'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']),
                        'image' => $this->getImage($manufacturer_info, $image_width, $image_height, $image_resize_type),
                        'dummy' => $this->getProductDummyImage($image_width, $image_height),
                        'items' => array()
                    );
                }
                break;

            /* custom menu */
            case 'custom':
                //                    echo "<pre>" . print_r($menu_item, true) . "</pre>";
                $menu['type'] = 'drop-down';
                switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_type')) {
                    case 'category':
                        $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$category_info) continue;
                        $menu['name'] = $category_info['name'];
                        $menu['href'] = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'product':
                        $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$product_info) continue;
                        $menu['name'] = $product_info['name'];
                        $menu['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'manufacturer':
                        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$manufacturer_info) continue;
                        $menu['name'] = $manufacturer_info['name'];
                        $menu['href'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'information':
                        $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.id', -1));
                        if (!$information_info) continue;
                        $menu['name'] = $information_info['title'];
                        $menu['href'] = $this->url->link('information/information', 'information_id=' . $information_info['information_id']);
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'opencart':
                        $customer_name = null;
                        switch (Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.page')) {
                            case 'login':
                                $menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                                $customer_name = $this->customer->getFirstName();
                                break;
                            case 'register':
                                $menu_item['custom']['top']['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                                break;
                            default:
                        }
                        $menu['name'] = $customer_name ? $customer_name : $this->model_journal2_menu->getMenuName($menu_item['custom']['top']['menu_item']['page']);
                        $menu['href'] = $menu_item['custom']['top']['menu_item']['page'] === 'common/home' ? $this->journal2->config->base_url : $this->url->link($menu_item['custom']['top']['menu_item']['page'], '', 'SSL');
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                    case 'custom':
                        $menu['name'] = Journal2Utils::getProperty($menu_item, 'custom.menu_item.name.value.' . $this->config->get('config_language_id'), 'Not Translated');
                        $menu['href'] = Journal2Utils::getProperty($menu_item, 'custom.top.menu_item.url');
                        $menu['subcategories'] = $this->generateMenu(Journal2Utils::getProperty($menu_item, 'custom.items', array()));
                        break;
                }
                break;

            /* html */
            case 'html':
                $menu['type'] = 'mega-menu-html';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'html.' . $this->config->get('config_language_id'));
                $menu['html_blocks'] = array();
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                foreach (Journal2Utils::sortArray(Journal2Utils::getProperty($menu_item, 'html_blocks', array())) as $block) {
                    if (!Journal2Utils::getProperty($block, 'status')) continue;
                    $menu['html_blocks'][] = array(
                        'title' => Journal2Utils::getProperty($block, 'title.value.' . $this->config->get('config_language_id'), ''),
                        'text'  => Journal2Utils::getProperty($block, 'text.' . $this->config->get('config_language_id')),
                        'link'  => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($block, 'link'))
                    );
                }
                break;
            /* mixed */
            case 'mixed':
                $menu['type'] = 'mega-menu-mixed';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'name.value.' . $this->config->get('config_language_id'));
                $menu['html_blocks'] = array();
                $menu['href'] = $this->model_journal2_menu->getLink(Journal2Utils::getProperty($menu_item, 'html_menu_link'));
                $columns = Journal2Utils::getProperty($menu_item, 'mixed_columns', array());
                $columns = Journal2Utils::sortArray($columns);

                $image_resize_type = Journal2Utils::getProperty($menu_item, 'image_type', 'fit');

                foreach ($columns as $column) {
                    $image_width = Journal2Utils::getProperty($column, 'image_width');
                    $image_height = Journal2Utils::getProperty($column, 'image_height');

                    if (!Journal2Utils::getProperty($column, 'status', 1)) continue;
                    $class = Journal2Utils::getProperty($column, 'hide_on_mobile') ? 'hide-on-mobile' : '';
                    if ($class === 'hide-on-mobile' && ($this->journal2->mobile_detect->isMobile() || $this->journal2->mobile_detect->isTablet()) && $this->journal2->settings->get('responsive_design')) {
                        continue;
                    }
                    $cms_blocks = array(
                        'top'       => array(),
                        'bottom'    => array()
                    );
                    foreach (Journal2Utils::getProperty($column, 'cms_blocks', array()) as $cms_block) {
                        if (!$cms_block['status']) return;
                        $cms_blocks[Journal2Utils::getProperty($cms_block, 'position', 'top')][] = array(
                            'content'       => Journal2Utils::getProperty($cms_block, 'content.' . $this->config->get('config_language_id')),
                            'sort_order'    => Journal2Utils::getProperty($cms_block, 'sort_order')
                        );
                    }
                    $column_menu = array(
                        'top_cms_blocks' => Journal2Utils::sortArray($cms_blocks['top']),
                        'bottom_cms_blocks' => Journal2Utils::sortArray($cms_blocks['bottom']),
                        'name' => '',
                        'href' => '',
                        'items' => array(),
                        'type' => '',
                        'class' => $class,
                        'width' => Journal2Utils::getProperty($column, 'width', '25') . '%',
                        'classes' => Journal2Utils::getProductGridClasses(Journal2Utils::getProperty($column, 'items_per_row.value'), $this->journal2->settings->get('site_width', 1024)),
                        'limit' => Journal2Utils::getProperty($column, 'items_limit', 0),
                        'icon' => Journal2Utils::getIconOptions2(Journal2Utils::getProperty($menu_item, 'icon')),
                        'hide_text' => Journal2Utils::getProperty($menu_item, 'hide_text')
                    );
                    $this->generateMenuItem($column_menu, $column, $image_width, $image_height, $image_resize_type);
                    $name_overwrite = Journal2Utils::getProperty($column, 'name.value.' . $this->config->get('config_language_id'));
                    if ($name_overwrite) {
                        $column_menu['name'] = $name_overwrite;
                    }
                    $menu['mixed_columns'][] = $column_menu;
                }
                break;
            /* html block */
            case 'html-block':
                $menu['type'] = 'mega-menu-html-block';
                $menu['name'] = Journal2Utils::getProperty($menu_item, 'html.' . $this->config->get('config_language_id'));
                $menu['html_text'] = Journal2Utils::getProperty($menu_item, 'html_text.' . $this->config->get('config_language_id'));
                break;
        }
    }

    private function generateMultiLevelCategoryMenu ($category_id, $path = '') {
        $categories = $this->model_catalog_category->getCategories($category_id);
        $path = $path ? $path . '_' . $category_id : $category_id;
        $result = array();
        foreach ($categories as $category) {
            $result[] = array(
                'href'			=> $this->url->link('product/category', 'path=' . $path . '_' . $category['category_id']),
                'new_window'	=> 0,
                'name'			=> $category['name'],
                'sort_order'	=> $category['sort_order'],
                'status'		=> $category['status'],
                'subcategories' => $this->generateMultiLevelCategoryMenu($category['category_id'], $path)
            );
        }
        return $result;
    }

    private function getFeatured($limit = 5) {
        $results = array();
        $index = 0;
        foreach (explode(',', $this->config->get('featured_product')) as $product_id) {
            $index++;
            if ($index > $limit) continue;
            $results[] = $this->model_catalog_product->getProduct($product_id);
        }
        return $results;
    }

    private function getBestsellers($limit = 5) {
        return $this->model_catalog_product->getBestSellerProducts($limit);
    }

    private function getSpecials($limit = 5) {
        $data = array(
            'sort'  => 'pd.name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );
        return $this->model_catalog_product->getProductSpecials($data);
    }

    private function getLatest($limit = 5) {
        $data = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit
        );
        return $this->model_catalog_product->getProducts($data);
    }

    private function getProductsByCategory($category_id, $limit = 5) {
        return $this->model_catalog_product->getProducts(array(
            'filter_category_id' => $category_id,
            'start' => 0,
            'limit' => $limit
        ));
    }

    private function getProductsByManufacturer($manufacturer_id, $limit = 5) {
        return $this->model_catalog_product->getProducts(array(
            'filter_manufacturer_id' => $manufacturer_id,
            'start' => 0,
            'limit' => $limit
        ));
    }

    private function getImage($item, $width, $height, $resize_type) {
        if (!is_numeric($width)) {
            $width = $this->config->get('config_image_product_width');
        }
        if (!is_numeric($height)) {
            $height = $this->config->get('config_image_product_height');
        }
        if($resize_type === 'fit'){
            $resize_type = '';
        } else {
            $resize_type = $width < $height ? 'h' : 'w';
        }
        $image = isset($item['image']) && $item['image'] ? $item['image'] : 'no_image.jpg';
        return $this->model_tool_image->resize($image, $width, $height, $resize_type);
    }

    private function getProductImage($image, $width, $height, $resize_type) {
        if (!is_numeric($width)) {
            $width = $this->config->get('config_image_product_width');
        }
        if (!is_numeric($height)) {
            $height = $this->config->get('config_image_product_height');
        }
        if($resize_type === 'fit'){
            $resize_type = '';
        } else {
            $resize_type = $width < $height ? 'h' : 'w';
        }
        return $this->model_tool_image->resize($image, $width, $height, $resize_type);
    }

    private function getProductDummyImage($width, $height) {
        if (!is_numeric($width)) {
            $width = $this->config->get('config_image_product_width');
        }
        if (!is_numeric($height)) {
            $height = $this->config->get('config_image_product_height');
        }
        return $this->model_tool_image->resize('data/journal2/transparent.png', $width, $height);
    }

    private function getProductPrice($product) {
        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            return $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
        }
        return false;
    }

    private function getProductSpecialPrice($product) {
        if ((float)$product['special']) {
            return$this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
        }
        return false;
    }

}
