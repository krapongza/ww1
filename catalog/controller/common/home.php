<?php  
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));

		$this->data['heading_title'] = $this->config->get('config_title');
		
		$this->load->model('account/gallery');

	 
		$this->data['products'] = array();
		$gallery_list = $this->model_account_gallery->getGalleryHome();
		foreach($gallery_list as $key => $product_info){
			$image = ($product_info['image']) ? HTTP_SERVER."image/".$product_info['image'] : false;		
			$this->data['products'][] = array(
				'thumb'      => $image,
				'href'       => $this->url->link('information/information/gallery', 'gallery=' . $product_info['id'])
			);
		}



		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/home.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/home.tpl';
		} else {
			$this->template = 'default/template/common/home.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
										
		$this->response->setOutput($this->render());
	}
}
?>