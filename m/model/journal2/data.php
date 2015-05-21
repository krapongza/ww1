<?php
define('TF_OK', 1);
define('TF_INVALID_CODE', 2);
define('TF_INVALID_USER', 3);
define('TF_API_KEY', '');

function verifyPurchaseCode($code, $user) {
    if (strlen(trim($code)) === 0) {
        return TF_INVALID_CODE;
    }
    $url = 'http://marketplace.envato.com/api/edge/DigitalAtelier/' . TF_API_KEY . '/verify-purchase:' . $code . '.json';
    $json = json_decode(file_get_contents($url), true);
    if (!(isset($json['verify-purchase']) && isset($json['verify-purchase']['item_id']) && isset($json['verify-purchase']['item_id']) == '4260361')) {
        return TF_INVALID_CODE;
    }
    if (trim(strtolower($json['verify-purchase']['buyer'])) !== trim(strtolower($user))) {
        return TF_INVALID_USER;
    }
    return TF_OK;
}

class ModelJournal2Data extends Model{

    private $post_data;
    private $get_data;

    private static $tables = array(
        'store_data' => array(
            'attribute',
            'attribute_description',
            'attribute_group',
            'attribute_group_description',

            'category',
            'category_description',
            'category_filter',
            'category_path',
            'category_to_layout',
            'category_to_store',

            'information',
            'information_description',
            'information_to_layout',
            'information_to_store',

            'layout',
            'layout_route',

            'manufacturer',
            'manufacturer_to_store',

            'option',
            'option_description',
            'option_value',
            'option_value_description',

            'product',
            'product_attribute',
            'product_description',
            'product_discount',
            'product_filter',
            'product_image',
            'product_option',
            'product_option_value',
            'product_profile',
            'product_recurring',
            'product_related',
            'product_reward',
            'product_special',
            'product_to_category',
            'product_to_download',
            'product_to_layout',
            'product_to_store',

            'stock_status',

            'url_alias'
        ),
        'layouts' => array(
            'layout',
            'layout_route'
        ),
        'journal_modules' => array(
            'journal2_modules'
        ),
        'journal_settings' => array(
            'journal2_config',
            'journal2_settings',
            'journal2_skins'
        )
    );

    public function __construct($registry) {
        parent::__construct($registry);
        $this->post_data = json_decode(file_get_contents('php://input'), true);
        $this->get_data = $this->request->get;
    }

    private function exportData($sql, $table, $dummy_images = false) {
        $output = '';

        $query = $this->db->query($sql);

        $remove_pk = in_array($table, array(
            DB_PREFIX . 'extension',
            DB_PREFIX . 'setting',
        ));

        foreach ($query->rows as $result) {
            $fields = '';

            foreach (array_keys($result) as $value) {
                $fields .= '`' . $value . '`, ';
            }

            $values = '';

            foreach ($result as $key => $value) {
                $value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
                $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
                $value = str_replace('\\', '\\\\', $value);
                $value = str_replace('\'', '\\\'', $value);
                $value = str_replace('\\\n', '\n', $value);
                $value = str_replace('\\\r', '\r', $value);
                $value = str_replace('\\\t', '\t', $value);

                /* check dummy image */
                if ($dummy_images && $value && $key === 'image' && in_array($table, array(
                    DB_PREFIX . 'category',
                    DB_PREFIX . 'manufacturer',
                    DB_PREFIX . 'product',
                    DB_PREFIX . 'product_image'
                ))) {
                    $value = 'data/journal2/no_image_large.jpg';
                }
                /* end check dummy image */

                /* remove primary key from extension and setting tables */
                if ($remove_pk && in_array($key, array(
                        'extension_id',
                        'setting_id'
                    ))) {
                    $value = 'NULL';
                }
                /* end remove primary key from extension and setting tables */

                /* json fix */
                if (strpos($table, DB_PREFIX . 'journal2_') === 0) {
                    $value = str_replace('\n', '\\\n', $value);
                }
                /* end json fix */

                $values .= '\'' . $value . '\', ';
            }

            $line = 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";

            $line = str_replace("('NULL',", "(NULL,", $line);

            $output .= $line;
        }

        return $output;
    }

    public function export() {
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . date('Y-m-d_H-i-s', time()).'_backup.sql');
        header('Content-Transfer-Encoding: binary');

        $output = '';

        /* opencart data */
        if (isset($this->get_data['include_store_data']) && $this->get_data['include_store_data']) {
            $dummy_images = isset($this->get_data['add_dummy_images']) && $this->get_data['add_dummy_images'];
            foreach (self::$tables['store_data'] as $table) {
                $output .= "TRUNCATE TABLE `" . DB_PREFIX . $table . "`;" . "\n\n";
                $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . $table . "`", DB_PREFIX . $table, $dummy_images);
                $output .= "\n\n";
            }
        }

        /* opencart layouts */
        foreach (self::$tables['layouts'] as $table) {
            $output .= "TRUNCATE TABLE `" . DB_PREFIX . $table . "`;" . "\n\n";
            $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . $table . "`", DB_PREFIX . $table);
            $output .= "\n\n";
        }

        /* journal modules */
        foreach (self::$tables['journal_modules'] as $table) {
            $output .= "TRUNCATE TABLE `" . DB_PREFIX . $table . "`;" . "\n\n";
            $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . $table . "`", DB_PREFIX . $table);
            $output .= "\n\n";
        }

        /* journal settings */
        foreach (self::$tables['journal_settings'] as $table) {
            $output .= "TRUNCATE TABLE `" . DB_PREFIX . $table . "`;" . "\n\n";
            if ($table === 'journal2_skins') {
                $output .= "ALTER TABLE `" . DB_PREFIX . $table . "` AUTO_INCREMENT = 100;" . "\n\n";
            }
            $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . $table . "`", DB_PREFIX . $table);
            if ($table === 'journal2_config') {
                $output .= "DELETE FROM `" . DB_PREFIX . $table . "` WHERE `key` = \"system_settings\";" . "\n\n";
            }
            $output .= "\n\n";
        }

        /* journal extensions */
        $output .= "DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = \"module\" AND `code` LIKE \"journal2%\";" . "\n\n";
        $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = \"module\" AND `code` LIKE \"journal2%\"", DB_PREFIX . 'extension');
        $output .= "\n\n";

        /* module placements */
        $output .= "DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE \"%_module\" OR `key` LIKE \"config_image_%\";" . "\n\n";
        $output .= $this->exportData("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE \"%_module\" OR `key` LIKE \"config_image_%\"", DB_PREFIX . 'setting');
        $output .= "\n\n";

        echo $output;

        exit();
    }

    public function import() {

    }

    public function verify_code() {
        if (!isset($this->post_data['purchased_code'])) {
            throw new Exception('Parameter purchased_code was not found');
        }
        if (!isset($this->post_data['tf_user'])) {
            throw new Exception('Parameter tf_user was not found');
        }
        $code = verifyPurchaseCode($this->post_data['purchased_code'], $this->post_data['tf_user']);
        if ($code === TF_INVALID_CODE) {
            throw new Exception('Invalid purchase code.');
        }
        if ($code === TF_INVALID_USER) {
            throw new Exception('ThemeForest user name does not match the purchase code.');
        }
        return true;
    }

}