<?php
class ModelJournal2Module extends Model {

    public function getModule($module_id) {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_id = ' . (int)$module_id);
        if (isset($query->row['module_data'])) {
            $query->row['module_data'] = json_decode($query->row['module_data'], true);
        }
        return $query->row;
    }

    public function getProductTabs($product_id) {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_type = "journal2_product_tabs"');
        $tabs = array();
        foreach ($query->rows as $row) {
            if (isset($row['module_data'])) {
                $tab = json_decode($row['module_data'], true);
                if (Journal2Utils::getProperty($tab, 'global')) {
                    $tabs[] = $tab;
                } else {
                    foreach (Journal2Utils::getProperty($tab, 'products', array()) as $product) {
                        if (Journal2Utils::getProperty($product, 'data.id') == $product_id) {
                            $tabs[] = $tab;
                            break;
                        }
                    }
                }
            }
        }
        return $tabs;
    }

}
?>