<?php
require_once DIR_SYSTEM . 'journal2/classes/journal2_cache.php';
require_once DIR_SYSTEM . 'journal2/lib/Browser.php';

class ControllerModuleJournal2 extends Controller {

    public function  __construct($registry) {
        parent::__construct($registry);

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_CATALOG;
        } else {
            $this->data['base'] = HTTP_CATALOG;
        }

        $this->data['base_href'] = $this->url->link('module/journal2', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['export_href'] = $this->url->link('module/journal2/rest/data/export', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['import_href'] = $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['export_csv'] = $this->url->link('module/journal2/rest/newsletter/export_csv', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['transition_gallery_href'] = $this->url->link('module/journal2/transition_gallery', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['journal2_config'] = array();
        $this->data['journal2_config']['token'] = $this->session->data['token'];
        $this->data['journal2_config']['version'] = defined('JOURNAL_VERSION') ? JOURNAL_VERSION : null;
    }

    public function index() {
        $this->document->setTitle('Journal Control Panel');

        $this->children = array(
            'common/header',
            'common/footer'
        );

        /* check browser */
        $browser = new Browser();
        if ($browser->isBrowser('Internet Explorer') && version_compare($browser->getVersion(), '8.0', '<=')) {
            $this->template = '../journal2/tpl/error_browser.tpl';
            $this->response->setOutput($this->render());
            return;
        }

        /* tables does not exist*/
        if (!$this->db->query('show tables like "' . DB_PREFIX . 'journal2_config"')->num_rows) {
            $this->template = '../journal2/tpl/error.tpl';
            $this->response->setOutput($this->render());
            return;
        }

        /* check system engine file */
        if (!defined('JOURNAL_VERSION')) {
            $this->template = '../journal2/tpl/error2.tpl';
            $this->response->setOutput($this->render());
            return;
        }

        $this->document->addStyle('//fonts.googleapis.com/css?family=Oswald');

        $this->document->addStyle('view/journal2/lib/bootstrap/css/bootstrap.css');
        $this->document->addScript('view/journal2/lib/bootstrap/js/bootstrap.min.js');

        $this->document->addScript('view/journal2/lib/css_browser_selector.js');

        $this->document->addScript('//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js');

        $this->document->addScript('view/journal2/lib/ckeditor/ckeditor.js');

        $this->document->addStyle('view/journal2/lib/css-toggle-switch/toggle-switch.css');

        $this->document->addStyle('view/journal2/lib/select2/select2.css');

        $this->document->addStyle('view/journal2/lib/angular-table/ng-table.min.css');

        $this->document->addStyle('view/journal2/lib/simple-slider/css/simple-slider.css');
        $this->document->addStyle('view/journal2/css/hint.min.css');
        $this->document->addStyle('view/journal2/css/main.css');

        $this->document->addScript('view/journal2/lib/spectrum/spectrum.js');
        $this->document->addStyle('view/journal2/lib/spectrum/spectrum.css');

        $this->document->addScript('view/journal2/lib/angular-file-upload/angular-file-upload-shim.min.js');

        $this->document->addStyle('../catalog/view/theme/journal2/css/icons/style.css');

        /* get success message */
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        /* get warning message */
        if (isset($this->session->data['warning'])) {
            $this->data['warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        } else {
            $this->data['warning'] = '';
        }

        /* get stores */
        $this->load->model('setting/store');
        $stores = $this->model_setting_store->getStores();
        array_unshift($stores, array(
            'store_id' => "0",
            'name'     => $this->config->get('config_name'),
        ));
        $this->data['journal2_config']['stores'] = $stores;

        /* get active skin */
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "journal2_config WHERE store_id = '0' AND `key` = 'active_skin'");
        $this->data['journal2_config']['active_skin'] = $query->num_rows ? $query->row['value'] : 1;

        /* get languages */
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $default_language = null;
        foreach ($languages as $language) {
            if ($language['language_id'] == $this->config->get('config_language_id')) {
                $default_language = $language['language_id'];
                break;
            }
        }
        $this->data['journal2_config']['languages'] = array(
            'languages' => $languages,
            'default'   => $default_language
        );

        /* get layouts */
        $this->load->model('design/layout');
        $this->data['journal2_config']['layouts'] = $this->model_design_layout->getLayouts();

        /* img folder */
        $this->data['journal2_config']['img_folder'] = (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) ? HTTPS_CATALOG . 'image/' : HTTP_CATALOG . 'image/';

        /* render template */
        $this->template = '../journal2/tpl/cp_index.tpl';

        $this->response->setOutput($this->render());
    }

    public function transition_gallery() {
        $this->template = '../journal2/tpl/layer_slider/transition_gallery.tpl';
        $this->response->setOutput($this->render());
    }

    public function tpl() {
        $tpl = isset($this->request->get['tpl']) ? $this->request->get['tpl'] : 'not_found.tpl';

        $this->data['tpl'] = $tpl;

        if (file_exists(DIR_TEMPLATE . '../journal2/tpl/' . $tpl . '.tpl')) {
            $this->template = '../journal2/tpl/' . $tpl . '.tpl';
        } else {
            $this->template = '../journal2/tpl/not_found.tpl';
        }

        $this->response->setOutput($this->render());
    }

    /*
     * handles ajax http requests as:
     * ----------------------------------------------------------------------------------
     * module/journal2/rest/modules/all                 GET module_type
     * module/journal2/rest/modules/get                 GET module_id
     * module/journal2/rest/modules/add                 POST module_data
     * module/journal2/rest/modules/edit                GET module_id, POST module_data
     * module/journal2/rest/modules/remove              GET module_id
     * module/journal2/rest/modules/load                GET module_type
     * module/journal2/rest/modules/save                GET module_type, POST module_data
     * ----------------------------------------------------------------------------------
     * module/journal2/rest/catalog/find_products       GET filter
     * module/journal2/rest/catalog/find_categories     GET filter
     * module/journal2/rest/catalog/find_manufacturers  GET filter
     * module/journal2/rest/catalog/find_information    GET filter
     * ----------------------------------------------------------------------------------
     * module/journal2/rest/misc/fonts
     * module/journal2/rest/misc/layouts
     * module/journal2/rest/misc/languages
     * ----------------------------------------------------------------------------------
     * module/journal2/rest/settings/get                GET category, subcategory
     * module/journal2/rest/settings/set                POST module_data
     */
    public function rest() {
        $response = array();

        $route = $this->request->get['route'];
        $parts = explode('/', $route);

        try {
            if (count($parts) < 5) {
                throw new Exception('Invalid REST route');
            }

            $model_file = 'journal2/' . $parts[3];
            $model_obj = 'model_' . str_replace('/', '_', $model_file);
            $model_method = $parts[4];

            if (!file_exists(DIR_APPLICATION . 'model/' . $model_file . '.php')) {
                throw new Exception('Invalid REST endpoint');
            }

            $this->load->model($model_file);

            if (!method_exists($this->$model_obj, $model_method)) {
                throw new Exception('Invalid REST action');
            }

            $response['status'] = 'success';
            $response['response'] = $this->$model_obj->$model_method();
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['error'] = $e->getMessage();
        }

        $response['route'] = str_replace('module/journal2/rest/', '', $route);

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function clear_cache() {
        if ($this->user->hasPermission('modify', 'module/journal2')) {
            Journal2Cache::deleteCache();
            $this->session->data['success'] = 'Journal Cache has been successfully cleared.';
        } else {
            $this->session->data['warning'] = 'You do not have permissions to modify Journal2 module.';
        }
        if (isset($this->session->data['j2_redirect'])) {
            $redirect = $this->url->link($this->session->data['j2_redirect'], 'token=' . $this->session->data['token'], 'SSL');
            unset($this->session->data['j2_redirect']);
        } else {
            $redirect = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');
        }
        $this->redirect($redirect);
    }

    public function check_version() {
        if (!isset($this->session->data['journal_version'])) {
            $defaults = array(
                CURLOPT_HEADER => 0,
                CURLOPT_URL => 'https://journal.digital-atelier.com/version.php?v=' . JOURNAL_VERSION,
                CURLOPT_FRESH_CONNECT => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FORBID_REUSE => 1,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0
            );

            $ch = curl_init();
            curl_setopt_array($ch, $defaults);
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            $version = is_array($result) && isset($result['version']) ? $result['version'] : null;
            $this->session->data['journal_version'] = $version;
        }

        $version = $this->session->data['journal_version'];

        if ($version !== null && version_compare(JOURNAL_VERSION, $version) === -1) {
            $response = array(
                'status'    => 'success',
                'response'  => array(
                    'current'   => JOURNAL_VERSION,
                    'latest'    => $version,
                    'upgrade'   => true
                )
            );
        } else {
            $response = array(
                'status'    => 'success',
                'response'  => array(
                    'upgrade'   => false
                )
            );
        }
        $this->response->setOutput(json_encode($response));
    }

    public function install() {
        $this->uninstall();

        /* create tables */
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_modules` (
          `module_id` int(11) NOT NULL AUTO_INCREMENT,
          `module_type` varchar(64) NOT NULL,
          `module_data` mediumtext NOT NULL,
          PRIMARY KEY (`module_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8');

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_config` (
          `key` varchar(64) NOT NULL,
          `store_id` int(11) NOT NULL DEFAULT 0,
          `value` mediumtext NOT NULL,
          `serialized` tinyint(1) NOT NULL,
          PRIMARY KEY `pk` (`key`,`store_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_settings` (
          `theme_id` int(11) NOT NULL DEFAULT 0,
          `key` varchar(64) NOT NULL,
          `category` varchar(64) NOT NULL,
          `value` mediumtext NOT NULL,
          `serialized` tinyint(1) NOT NULL,
          PRIMARY KEY `pk` (`key`,`theme_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'journal2_skins` (
          `skin_id` int(11) NOT NULL AUTO_INCREMENT,
          `skin_name` varchar(64) NOT NULL,
          `parent_id` int(11) NOT NULL,
          PRIMARY KEY (`skin_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=100;');
    }

    public function uninstall() {
        $this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'journal2_skins`');
        $this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'journal2_settings`');
        $this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'journal2_config`');
        $this->db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . 'journal2_modules`');
        $this->db->query('DELETE FROM `' . DB_PREFIX . 'setting` WHERE `group` like "journal2_%"');
    }
}
?>