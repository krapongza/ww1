<?php
class ControllerJournal2Category extends Controller {

    public function refine_images() {
        if (!in_array($this->journal2->settings->get('refine_category'), array('grid', 'carousel'))) return;
        if (!isset($this->request->get['route']) || $this->request->get['route'] !== 'product/category') return;

        $path = isset($this->request->get['path']) ? $this->request->get['path'] : false;
        if ($path) {
            $this->load->model('catalog/category');

            $parts = explode('_', (string)$path);
            $category_id = (int)array_pop($parts);

            $categories = $this->model_catalog_category->getCategories($category_id);

            $data = array();
            foreach ($categories as $category) {
                $filters = array(
                    'filter_category_id'  => $category['category_id'],
                    'filter_sub_category' => true
                );

                if ($this->config->get('config_product_count')) {
                    $product_total = ' (' . $this->model_catalog_product->getTotalProducts($filters) . ')';
                } else {
                    $product_total = '';
                }

                $data[] = array(
                    'name'  => $category['name'] . $product_total,
                    'href'  => $this->url->link('product/category', 'path=' . $path . '_' . $category['category_id']),
                    'thumb'	=> $this->model_tool_image->resize($category['image'] ? $category['image'] : 'no_image.jpg', $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'))
                );
            }
            $this->document->addStyle('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.css');
            $this->document->addScript('catalog/view/theme/journal2/lib/owl-carousel/owl.carousel.js');
            //$this->document->addStyle('catalog/view/theme/journal2/lib/owl-carousel/owl.theme.css');
            $this->journal2->settings->set('refine_category_images', $data);
        }
    }

}
?>