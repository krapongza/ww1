<?php
class ModelJournal2Product extends Model {

    private static $cache = array();
    private static $latest = null;
    private static $category_products = null;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('catalog/product');
//        if (isset($this->request->get['path'])) {
//            $parts = explode('_', $this->request->get['path']);
//            $category_id = end($parts);
//            if ($category_id) {
//                self::$category_products = array();
//                foreach ($this->getProductsByCategory($category_id, PHP_INT_MAX) as $value) {
//                    self::$category_products[] = $value['product_id'];
//                }
//            }
//        }
    }

    private function addLabel($product_id, $label, $name) {
        if (!isset(self::$cache[$product_id])) {
            self::$cache[$product_id] = array();
        }
        self::$cache[$product_id][$label] = $name;
    }

    private function hasLabel($product_id, $label) {
        if (!isset(self::$cache[$product_id])) {
            return false;
        }
        return in_array($label, self::$cache[$product_id]);
    }

    public function getLabels($product_id) {
        if (!defined('JOURNAL_INSTALLED')) {
            return array();
        }
        /* get latest label */
        if ($this->journal2->settings->get('label_latest_status', 'always') !== 'never') {
            if (self::$latest === null) {
                self::$latest = $this->model_catalog_product->getLatestProducts($this->journal2->settings->get('label_latest_limit', 10));
            }
            if (!$this->hasLabel($product_id, 'latest') && is_array(self::$latest)) {
                foreach (self::$latest as $product) {
                    if ($product_id == $product['product_id']) {
                        $this->addLabel($product_id, 'latest', $this->journal2->settings->get('label_latest_text', 'New'));
                        break;
                    }
                }
            }
        }

        $product = $this->model_catalog_product->getProduct($product_id);

        /* get special label */
        if ($this->journal2->settings->get('label_special_status', 'always') !== 'never') {
            if ((float)$product['special']) {
                if ($this->journal2->settings->get('label_special_type', 'percent') === 'percent') {
                    if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                        $price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
                    } else {
                        $price = false;
                    }
                    $special = $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
                    if ($price > 0.0) {
                        $this->addLabel($product_id, 'sale', '-' . round(($price - $special) / $price * 100) . '%');
                    }
                } else {
                    $this->addLabel($product_id, 'sale', $this->journal2->settings->get('label_special_text', 'Sale'));
                }
            }
        }

        /* get stock label */
        if ($product['quantity'] <= 0 && $this->journal2->settings->get('out_of_stock_status', 'always') !== 'never' && Journal2Utils::canGenerateImages()) {
            $this->addLabel($product_id, 'outofstock', $product['stock_status']);
        }

        if (!isset(self::$cache[$product_id])) {
            return array();
        }

        return self::$cache[$product_id];
    }

    public function getSpecialCountdown($product_id) {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        $query = $this->db->query("SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = '" . (int)$product_id . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1");
        return $query->row['date_end'];
    }


    public function getProductViews($product_id) {
        $query = $this->db->query("SELECT viewed FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row['viewed'] : null;
    }

    public function getRandomProducts ($limit = 5, $category_id = -1) {
        $sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

        if ($category_id !== -1) {
            $sql .= " LEFT JOIN " . DB_PREFIX ."product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if ($category_id !== -1) {
            $sql .= " AND p2c.category_id = '" . (int)$category_id . "'";
        }

        $sql .= " ORDER BY rand() LIMIT " . (int)$limit;

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getFeatured($limit = 5, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $results = array();
                $i = 0;
                foreach (explode(',', $this->config->get('featured_product')) as $product_id) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product_id, self::$category_products)) continue;
                    $results[] = $this->model_catalog_product->getProduct($product_id);
                    if ($limit && $i === $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        $results = array();
        $i = 0;
        foreach (explode(',', $this->config->get('featured_product')) as $product_id) {
            $i++;
            $results[] = $this->model_catalog_product->getProduct($product_id);
            if ($limit && $i === $limit) {
                break;
            }
        }
        return $results;
    }

    public function getBestsellers($limit = 5, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getBestSellerProducts(PHP_INT_MAX) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i === $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        return $this->model_catalog_product->getBestSellerProducts($limit);
    }

    public function getSpecials($limit = 5, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $data = array(
                    'sort'  => 'pd.name',
                    'order' => 'ASC',
                    'start' => 0
                );
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getProductSpecials($data) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i === $limit) {
                        break;
                    }
                }
                return $results;
            }
        }
        $data = array(
            'sort'  => 'pd.name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );
        return $this->model_catalog_product->getProductSpecials($data);
    }

    public function getLatest($limit = 5, $filter_category = false) {
        if (false && $filter_category) {
            if (self::$category_products) {
                $data = array(
                    'sort'  => 'p.date_added',
                    'order' => 'DESC',
                    'start' => 0,
                    'limit' => PHP_INT_MAX
                );
                $results = array();
                $i = 0;
                foreach ($this->model_catalog_product->getProducts($data) as $product) {
                    $i++;
                    if ($filter_category && self::$category_products !== null && !in_array($product['product_id'], self::$category_products)) continue;
                    $results[] = $product;
                    if ($limit && $i === $limit) {
                        break;
                    }
                }
				//echo "1111";
                return $results;
            }
        }
        $data = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
			'status' => array('0','3'),
            'limit' => $limit
        );
		//echo "2222";
        return $this->model_catalog_product->getProducts($data);
    }

    public function getProductsByCategory($category_id, $limit = 5) {
        return $this->model_catalog_product->getProducts(array(
            'filter_category_id' => $category_id,
            'start' => 0,
            'limit' => $limit
        ));
    }

    public function getProductsByManufacturer($manufacturer_id, $limit = 5) {
        return $this->model_catalog_product->getProducts(array(
            'filter_manufacturer_id' => $manufacturer_id,
            'start' => 0,
            'limit' => $limit
        ));
    }

    public function getPeopleAlsoBought($product_id, $limit = 5) {
        $sql = '
            SELECT distinct product_id FROM ' . DB_PREFIX . 'order_product WHERE order_id IN (
                SELECT order_id FROM ' . DB_PREFIX . 'order_product WHERE product_id = ' . (int)$product_id . '
            ) LIMIT ' . (int) $limit . '
        ';
        $query = $this->db->query($sql);
        $results = array();
        foreach ($query->rows as $row) {
            $result = $this->model_catalog_product->getProduct($row['product_id']);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    public function getProductRelated($product_id, $limit = 5) {
        return array_slice($this->model_catalog_product->getProductRelated($product_id), 0, $limit);
    }

}
?>