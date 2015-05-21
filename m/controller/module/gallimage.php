<?php
class ControllerModuleGallimage extends Controller {
	private $error = array(); 

	public function index() {   
		$this->language->load('module/gallimage');

		$this->document->setTitle($this->language->get('heading_title_main'));

		$this->load->model('setting/setting');
		
		$this->document->addScript('view/javascript/jquery/jscolor/jscolor.js');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('gallimage', $this->request->post);		

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title_main');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
		
		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['tab_basicset'] = $this->language->get('tab_basicset');
		$this->data['tab_advanceset'] = $this->language->get('tab_advanceset');
		$this->data['tab_popup'] = $this->language->get('tab_popup');
		$this->data['entry_boxgallcol'] = $this->language->get('entry_boxgallcol');
		$this->data['entry_namecol'] = $this->language->get('entry_namecol'); 
		$this->data['entry_namefontsize'] = $this->language->get('entry_namefontsize'); 
		$this->data['entry_namepos'] = $this->language->get('entry_namepos'); 
		$this->data['entry_bordergall'] = $this->language->get('entry_bordergall');
		$this->data['entry_borderthick'] = $this->language->get('entry_borderthick');
		$this->data['entry_popupstyle'] = $this->language->get('entry_popupstyle');
		$this->data['entry_popupsize'] = $this->language->get('entry_popupsize');

		$this->data['entry_gallimage'] = $this->language->get('entry_gallimage');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension'); 
		$this->data['entry_box_margin'] = $this->language->get('entry_box_margin');
		$this->data['entry_box_style'] = $this->language->get('entry_box_style');
		$this->data['text_pleaseselect'] = $this->language->get('text_pleaseselect');	
		$this->data['text_style1'] = $this->language->get('text_style1');
		$this->data['text_style2'] = $this->language->get('text_style2');
		$this->data['entry_carstat'] = $this->language->get('entry_carstat');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->data['error_dimension'] = $this->error['dimension'];
		} else {
			$this->data['error_dimension'] = array();
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title_main'),
			'href'      => $this->url->link('module/gallimage', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('module/gallimage', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['gallimage_boxgallcol'])) {
			$this->data['gallimage_boxgallcol'] = $this->request->post['gallimage_boxgallcol'];
		} else {
			$this->data['gallimage_boxgallcol'] = $this->config->get('gallimage_boxgallcol');
		}
		
		if (isset($this->request->post['gallimage_namecol'])) {
			$this->data['gallimage_namecol'] = $this->request->post['gallimage_namecol'];
		} else {
			$this->data['gallimage_namecol'] = $this->config->get('gallimage_namecol');
		}
		
		if (isset($this->request->post['gallimage_namefontsize'])) {
			$this->data['gallimage_namefontsize'] = $this->request->post['gallimage_namefontsize'];
		} else {
			$this->data['gallimage_namefontsize'] = $this->config->get('gallimage_namefontsize');
		}
		
		if (isset($this->request->post['gallimage_namepos'])) {
			$this->data['gallimage_namepos'] = $this->request->post['gallimage_namepos'];
		} else {
			$this->data['gallimage_namepos'] = $this->config->get('gallimage_namepos');
		}
		
		if (isset($this->request->post['gallimage_bordercol'])) {
			$this->data['gallimage_bordercol'] = $this->request->post['gallimage_bordercol'];
		} else {
			$this->data['gallimage_bordercol'] = $this->config->get('gallimage_bordercol');
		}
		
		if (isset($this->request->post['gallimage_borderthick'])) {
			$this->data['gallimage_borderthick'] = $this->request->post['gallimage_borderthick'];
		} else {
			$this->data['gallimage_borderthick'] = $this->config->get('gallimage_borderthick');
		}
		
		if (isset($this->request->post['gallimage_popupstyle'])) {
			$this->data['gallimage_popupstyle'] = $this->request->post['gallimage_popupstyle'];
		} else {
			$this->data['gallimage_popupstyle'] = $this->config->get('gallimage_popupstyle');
		}
		
		if (isset($this->request->post['gallimage_popupwidth'])) {
			$this->data['gallimage_popupwidth'] = $this->request->post['gallimage_popupwidth'];
		} else {
			$this->data['gallimage_popupwidth'] = $this->config->get('gallimage_popupwidth');
		}
		
		if (isset($this->request->post['gallimage_popupheight'])) {
			$this->data['gallimage_popupheight'] = $this->request->post['gallimage_popupheight'];
		} else {
			$this->data['gallimage_popupheight'] = $this->config->get('gallimage_popupheight');
		}

		$this->data['modules'] = array();

		if (isset($this->request->post['gallimage_module'])) {
			$this->data['modules'] = $this->request->post['gallimage_module'];
		} elseif ($this->config->get('gallimage_module')) { 
			$this->data['modules'] = $this->config->get('gallimage_module');
		}	

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->load->model('catalog/gallimage');

		$this->data['gallimages'] = $this->model_catalog_gallimage->getgallimages();

		$this->template = 'module/gallimage.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
	
	public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gallimage` (`gallimage_id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(64) NOT NULL, `status` tinyint(1) NOT NULL, PRIMARY KEY (`gallimage_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3");
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gallimage_image` (`gallimage_image_id` int(11) NOT NULL AUTO_INCREMENT, `gallimage_id` int(11) NOT NULL, `name` varchar(64) NOT NULL, `link` varchar(255) NOT NULL, `image` varchar(255) NOT NULL, `sort_order` int(3) NOT NULL DEFAULT '0', PRIMARY KEY (`gallimage_image_id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7");
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gallimage_image_description` (`gallimage_image_id` int(11) NOT NULL, `language_id` int(11) NOT NULL, `gallimage_id` int(11) NOT NULL, `title` varchar(128) NOT NULL, PRIMARY KEY (`gallimage_image_id`,`language_id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }
	
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "gallimage");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "gallimage_image");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "gallimage_image_description");
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/gallimage')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['gallimage_module'])) {
			foreach ($this->request->post['gallimage_module'] as $key => $value) {
				if (!$value['image_width'] || !$value['image_height']) {
					$this->error['dimension'][$key] = $this->language->get('error_dimension');
				}			
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>