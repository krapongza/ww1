<?php  
class ControllerModuleGallimage extends Controller {
	protected function index($setting) {
		static $module = 0;	
		
		$this->language->load('module/gallimage'); 
		
		$this->load->model('catalog/gallimage');
		$this->load->model('tool/image');
				
		$this->document->addScript('catalog/view/javascript/jquery/gallery-colorbox/jquery.gallbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/gallery-colorbox/' . $this->config->get('gallimage_popupstyle') . '/' . $this->config->get('gallimage_popupstyle') . '.css');
		$this->document->addStyle('catalog/view/javascript/jquery/gallery-colorbox/gallery.css');
		
		if ($setting['gallimagecar'] == '1') {
		$this->document->addScript('catalog/view/javascript/jquery/gallery-colorbox/jquery.carouFredSel-6.2.1-packed.js');
		$this->document->addScript('catalog/view/javascript/jquery/gallery-colorbox/jquery.touchSwipe.min.js');
		}
		
		$this->data['gallboxstyle'] = $setting['gallboxstyle'];	
		
		$this->data['boxgallcol']	= $this->config->get('gallimage_boxgallcol');
		$this->data['namecol']		= $this->config->get('gallimage_namecol');
		$this->data['namefontsize']	= $this->config->get('gallimage_namefontsize');
        $this->data['namepos'] 		= $this->config->get('gallimage_namepos');
		$this->data['bordercol'] 	= $this->config->get('gallimage_bordercol');
        $this->data['borderthick']	= $this->config->get('gallimage_borderthick');
				
		$this->data['gallimages'] = array();
		
		$gallimage_title = $this->model_catalog_gallimage->getGallName($setting['gallimage_id']);
	    $this->data['heading_title'] = $gallimage_title;
		
		$results = $this->model_catalog_gallimage->getGallimage($setting['gallimage_id']);	
		
		$results = array_slice($results, 0, $setting['limit']); 

		foreach ($results as $result) {
			if (file_exists(DIR_IMAGE . $result['image'])) {
				$this->data['gallimages'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']),
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('gallimage_popupwidth'), $this->config->get('gallimage_popupheight'))
				);
			}
		}
		
		$this->data['popimgwidth'] = $this->config->get('gallimage_popupwidth');
		$this->data['popimgheight'] = $this->config->get('gallimage_popupheight');
		$this->data['boxwidth'] = $setting['image_width'];
		$this->data['imgheight'] = $setting['image_height'];
		$this->data['marginh'] = $setting['boxgall_margin'];
		$this->data['marginv'] = $setting['boxgall_margin'] + 3;
		$this->data['gallimagecar'] = $setting['gallimagecar'];
		
		$this->data['module'] = $module++;
				
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/gallimage.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/gallimage.tpl';
		} else {
			$this->template = 'default/template/module/gallimage.tpl';
		}
		
		$this->render();
	}
}
?>