<?php
class ControllerJournal2Search extends Controller {

    private static $SHOW_PRICE = true;
    private static $SHOW_IMAGES = true;
    private static $IMAGE_WIDTH = 50;
    private static $IMAGE_HEIGHT = 50;

    public function __construct($reg) {
        parent::__construct($reg);
    }

    public function index() {
        $this->load->model('journal2/search');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $json = array('results' => array());

        if(isset($this->request->get['search'])) {

            $results = $this->model_journal2_search->search($this->request->get['search'], $this->journal2->settings->get('autosuggest_limit', 0), $this->journal2->settings->get('search_autocomplete_include_description', '1') === '1');

            foreach($results as $result) {
                $result = $this->model_catalog_product->getProduct($result['product_id']);
                if (self::$SHOW_IMAGES) {
                    $image = $this->model_tool_image->resize($result['image'] ? $result['image'] : 'no_image.jpg', self::$IMAGE_WIDTH, self::$IMAGE_HEIGHT);
                } else {
                    $image = null;
                }

                if (self::$SHOW_PRICE && (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price'))) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $price = null;
                }

                if (self::$SHOW_PRICE && (float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $special = null;
                }

                $json['results'][] = array(
                    'name'  => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
                    'url'   => htmlspecialchars_decode($this->url->link('product/product', '&product_id=' . $result['product_id'])),
                    'image' => $image,
                    'price' => $price,
                    'special' => $special
                );
            }

            $json['view_more_text'] = $this->journal2->settings->get('autosuggest_view_more_text', 'View More');
            $json['view_more_url'] = $this->url->link('product/search', '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8')));
        }

        $this->response->setOutput(json_encode($json));
    }

}
