<?php
class ControllerModuleJournal2SideCategory extends Controller {

    private static $CACHEABLE = null;

    public function __construct($registry) {
        parent::__construct($registry);
        if (!defined('JOURNAL_INSTALLED')) {
            return;
        }
        $this->load->model('journal2/module');
        $this->load->model('journal2/menu');

        if (self::$CACHEABLE === null) {
            self::$CACHEABLE = (bool)$this->journal2->settings->get('config_system_settings.side_category_cache');
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

        if ($this->journal2->mobile_detect->isMobile() && !$this->journal2->mobile_detect->isTablet() && $this->journal2->settings->get('responsive_design')) return;

        $hash = isset($this->request->server['REQUEST_URI']) ? md5($this->request->server['REQUEST_URI']) : null;

        $cache_property = "module_journal_side_category_{$setting['module_id']}_{$setting['layout_id']}_{$setting['position']}_{$hash}";

        $cache = $this->journal2->cache->get($cache_property);

        if ($cache === null || self::$CACHEABLE !== true || $hash === null) {
            $module = mt_rand();

            $this->data['module'] = $module;
            $this->data['class'] = $setting['position'] === 'column_right' ? 'side-category-right' : 'side-category-left';
            $this->data['type'] = Journal2Utils::getProperty($module_data, 'module_data.type');

            switch ($this->data['type']) {
                case 'accordion':
                    $this->data['class'] .= ' side-category-accordion';
                    break;
                case 'dropdown':
                    $this->data['class'] .= ' side-category-dropdown';
                    break;
            }

            $this->language->load('module/category');

            $this->data['heading_title'] = Journal2Utils::getProperty($module_data, 'module_data.title.value.' . $this->config->get('config_language_id'), $this->language->get('heading_title'));

            $tree = array();

            if (Journal2Utils::getProperty($module_data, 'module_data.show_categories')) {
                if (isset($this->request->get['path'])) {
                    $parts = explode('_', (string)$this->request->get['path']);
                } else {
                    $parts = array();
                }

                $sql  = "SELECT c.category_id, c.parent_id, cd.name ";

                if ($this->config->get('config_product_count')) {
                    $sql  .= ", (SELECT COUNT(p.product_id) FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)  WHERE p.status = '1' AND p.date_available <= NOW()  AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2c.category_id = c.category_id) as total ";
                }

                $sql .= "FROM " . DB_PREFIX . "category c  LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)  LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)  WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'   AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";


                $query = $this->db->query($sql);

                $results = array();

                foreach ($query->rows as $row) {
                    $results[$row['parent_id']][] = $row;
                }

                $tree = $this->generateMultiLevelCategoryMenu($results, $results[0], '', $parts);

                if ($this->config->get('config_product_count')) {
                    for ($i = 0; $i < count($tree); $i++) {
                        $this->sum($tree[$i]);
                    }
                }
            }

            $top_items = array();
            $bottom_items = array();

            foreach (Journal2Utils::getProperty($module_data, 'module_data.sections', array()) as $item_data) {
                $item = array(
                    'name' => Journal2Utils::getProperty($item_data, 'name.value.' . $this->config->get('config_language_id')),
                    'href' => $this->model_journal2_menu->getLink(Journal2Utils::getProperty($item_data, 'link')),
                    'target' => Journal2Utils::getProperty($item_data, 'new_window') ? 'target="_blank"' : '',
                    'sort_order' => Journal2Utils::getProperty($item_data, 'sort_order')
                );

                $item['class'] = $this->isActive($item['href']);

                if (Journal2Utils::getProperty($item_data, 'position') === 'top') {
                    $top_items[] = $item;
                } else {
                    $bottom_items[] = $item;
                }
            }

            $top_items = Journal2Utils::sortArray($top_items);
            $bottom_items = Journal2Utils::sortArray($bottom_items);

            $this->data['categories'] = $tree;
            $this->data['top_items'] = $top_items;
            $this->data['bottom_items'] = $bottom_items;
            $this->data['show_total'] = $this->config->get('config_product_count');

            $this->template = 'journal2/template/journal2/module/side_category.tpl';
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

    private function generateMultiLevelCategoryMenu(&$list, $parent, $path, $parts){
        $tree = array();

        foreach ($parent as $key => $value) {
            $temp = $path . ($path == '' ? $value['category_id'] : '_' . $value['category_id']);
            $value['href'] = $this->url->link('product/category', 'path=' . $temp);
            $value['class'] = in_array($value['category_id'], $parts) ? 'active' : '';

            if (isset($list[$value['category_id']])) {
                $value['subcategories'] = $this->generateMultiLevelCategoryMenu($list, $list[$value['category_id']], $temp, $parts);
            } else {
                $value['subcategories'] = array();
            }
            $tree[] = $value;
        }

        return $tree;
    }

    private function sum(&$tree){
        if (isset($tree['subcategories'])) {
            foreach ($tree['subcategories'] as $level => $child){
                $tree['total'] += $this->sum($tree['subcategories'][$level]);
            }
        }
        return $tree['total'];
    }

    private function isActive($url) {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        return trim(str_replace('index.php?route=common/home', '', $current_url), '/') === trim($url, '/') ? 'active' : '';
    }

}
