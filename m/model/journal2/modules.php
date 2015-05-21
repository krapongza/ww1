<?php
require_once DIR_SYSTEM . 'journal2/classes/journal2_cache.php';

class ModelJournal2Modules extends Model{

    private $post_data;
    private $get_data;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->post_data = json_decode(file_get_contents('php://input'), true);
        $this->get_data = $this->request->get;
    }

    /*
     *
     * get existing Layouts
     *
     */
    public function layouts() {
        $this->load->model('design/layout');
        return $this->model_design_layout->getLayouts();
    }

    /*
     *
     * get Languages
     *
     */
    public function languages() {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $default_language = null;
        foreach ($languages as $language) {
            if ($language['language_id'] == $this->config->get('config_language_id')) {
                $default_language = $language['language_id'];
                break;
            }
        }
        return array(
            'languages' => $languages,
            'default'   => $default_language
        );
    }

    /*
     *
     * get all modules (if module_type exists, will return all modules for that type)
     *
     */
    public function all() {
        if (isset($this->get_data['module_type'])) {
            $module_type = $this->db->escape('journal2_' . $this->get_data['module_type']);
            $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_type = "' . $module_type . '"');
        } else {
            $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules');
        }

        foreach ($query->rows as &$row) {
            $row['module_data'] = json_decode($row['module_data'], true);
            if (is_array($row['module_data'])) {
                foreach($row['module_data'] as $key => &$value) {
                    if (!in_array($key, array('module_name', 'module_type'))) {
                        unset($row['module_data'][$key]);
                    }
                }
            }
        }

        return $query->rows;
    }

    /*
     *
     * get all available multi_modules
     *
     */
    public function multi_modules() {
        $types = array(
            'journal2_slider'           => 'Revolution Slider',
            'journal2_static_banners'   => 'Banners'
        );

        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_type IN ("journal2_slider", "journal2_static_banners")');

        $modules = array();

        foreach ($query->rows as $row) {
            if (!isset($modules[$row['module_type']])) {
                $modules[$row['module_type']] = array(
                    'module_type'   => isset($types[$row['module_type']]) ? $types[$row['module_type']] : $row['module_type'],
                    'modules'       => array()
                );
            }
            $row['module_data'] = json_decode($row['module_data'], true);
            $modules[$row['module_type']]['modules'][] = array(
                'module_id'     => $row['module_id'],
                'module_name'   => $row['module_data']['module_name']
            );
        }

        return $modules;
    }

    /*
     *
     * get module by module_id
     *
     */
    public function get() {
        if (!isset($this->get_data['module_id'])) {
            throw new Exception('Parameter module_id was not found');
        }

        $module_id = (int)$this->get_data['module_id'];

        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'journal2_modules WHERE module_id = ' . $module_id);
        if (isset($query->row['module_data'])) {
            $query->row['module_data'] = json_decode($query->row['module_data'], true);
        }
        return $query->row;
    }

    /*
     *
     * add module to database
     *
     */
    public function add() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        if (!isset($this->get_data['module_type'])) {
            throw new Exception('Parameter module_type was not found');
        }

        if (!isset($this->post_data['module_data'])) {
            throw new Exception('Parameter module_data was not found');
        }

        $data = $this->db->escape(json_encode($this->post_data['module_data']));
        $type = $this->db->escape('journal2_' . $this->get_data['module_type']);

        $this->db->query('INSERT INTO ' . DB_PREFIX . 'journal2_modules (module_type, module_data) VALUES ("' . $type . '", "' . $data . '")');

        $this->get_data['module_id'] = $this->db->getLastId();
        return $this->get();
    }

    /*
     *
     * edit module to database
     *
     */
    public function edit() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        if (!isset($this->get_data['module_id'])) {
            throw new Exception('Parameter module_id was not found');
        }

        if (!isset($this->post_data['module_data'])) {
            throw new Exception('Parameter module_data was not found');
        }

        $data = $this->db->escape(json_encode($this->post_data['module_data']));
        $id = (int)$this->get_data['module_id'];

        $this->db->query('UPDATE ' . DB_PREFIX . 'journal2_modules SET module_data = "' . $data . '" WHERE module_id = ' . $id);

        Journal2Cache::deleteModuleCache("module_journal");

        return $this->get();
    }

    /*
     *
     * remove module from database
     *
     */
    public function remove() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        if (!isset($this->get_data['module_id'])) {
            throw new Exception('Parameter module_id was not found');
        }

        $id = (int)$this->get_data['module_id'];

        $this->db->query('DELETE FROM ' . DB_PREFIX . 'journal2_modules WHERE module_id = ' . $id);

        return null;
    }

    /*
     *
     * get module placement
     *
     */
    public function load() {
        if (!isset($this->get_data['module_type'])) {
            throw new Exception('Parameter module_type was not found');
        }

        $module_type = $this->get_data['module_type'];
        $modules = $this->config->get('journal2_' . $module_type . '_module');

        return is_array($modules) ? $modules : array();
    }

    /*
     *
     * save module placement
     *
     */
    public function save() {
        if (!$this->user->hasPermission('modify', 'module/journal2')) {
            throw new Exception('You do not have permissions to modify Journal2 module');
        }

        if (!isset($this->get_data['module_type'])) {
            throw new Exception('Parameter module_type was not found');
        }

        if (!isset($this->post_data['module_data'])) {
            throw new Exception('Parameter module_data was not found');
        }

        $module_type = 'journal2_' . $this->get_data['module_type'];
        $module_data = $this->post_data['module_data'];
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstallJ2Extension('module', $module_type);
        $this->model_setting_extension->install('module', $module_type);
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting($module_type, array($module_type . '_module' => $module_data));

        Journal2Cache::deleteModuleCache("module_journal_{$this->get_data['module_type']}");

        return null;
    }

}