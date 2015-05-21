<?php
class ControllerJournal2Snippets extends Controller {

    private $s_title = null;
    private $s_description = null;
    private $s_url = null;
    private $s_image = null;
    private $s_rating = null;

    const IMG_WIDTH = 200;
    const IMG_HEIGHT = 200;

    public function index() {
        $this->load->model('tool/image');

        /* blog manager compatibility */
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : null;
        if ($route !== null && in_array($route, array('blog/article', 'blog/category'))) {
            return;
        }
        /* end of blog manager compatibility */

        switch ($this->journal2->page->getType()) {
            case 'product':
                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($this->journal2->page->getId());
                if ($product_info) {
                    $this->s_title = $product_info['name'];
                    $this->s_description = trim(utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 150));
                    $url = '';
                    if (isset($this->request->get['path'])) {
                        $url .= '&path=' . $this->request->get['path'];
                    }
                    $url .= '&product_id=' . $product_info['product_id'];
                    $this->s_url = $this->url->link('product/product', $url);
                    $this->s_image = $this->model_tool_image->resize($product_info['image'], self::IMG_WIDTH, self::IMG_HEIGHT);
                    $this->s_rating = (int)$product_info['rating'];

                    $this->journal2->settings->set('product_google_snippet', 'itemscope itemtype="http://schema.org/Product"');
                    $this->journal2->settings->set('product_price_currency', $this->currency->getCode());
                    $this->journal2->settings->set('product_num_reviews', $product_info['reviews']);
                    $this->journal2->settings->set('product_in_stock', $product_info['quantity'] > 0 ? 'yes' : 'no');
                    /* review ratings */
                    $this->language->load('product/product');

                    $this->load->model('catalog/review');

                    $this->data['text_on'] = $this->language->get('text_on');
                    $this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

                    if (isset($this->request->get['page'])) {
                        $page = $this->request->get['page'];
                    } else {
                        $page = 1;
                    }

                    $this->data['reviews'] = array();

                    $review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

                    $results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

                    foreach ($results as $result) {
                        $this->data['reviews'][] = array(
                            'author'     => $result['author'],
                            'text'       => $result['text'],
                            'rating'     => (int)$result['rating'],
                            'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
                            'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
                        );
                    }

                    $pagination = new Pagination();
                    $pagination->total = $review_total;
                    $pagination->page = $page;
                    $pagination->limit = 5;
                    $pagination->text = $this->language->get('text_pagination');
                    $pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

                    $this->data['pagination'] = $pagination->render();

                    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/review.tpl')) {
                        $this->template = $this->config->get('config_template') . '/template/product/review.tpl';
                    } else {
                        $this->template = 'default/template/product/review.tpl';
                    }

                    $this->journal2->settings->set('product_reviews', $this->render());
                }
                break;

            case 'category':
                $this->load->model('catalog/category');
                $parts = explode('_', $this->journal2->page->getId());
                $category_id = (int)array_pop($parts);
                $category_info = $this->model_catalog_category->getCategory($category_id);
                if ($category_info) {
                    $this->s_title = $category_info['name'];
                    $this->s_description = trim(utf8_substr(strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8')), 0, 150));
                    $this->s_url = $this->url->link('product/category', '&path=' . $this->journal2->page->getId());
                    $this->s_image = $this->model_tool_image->resize($category_info['image'], self::IMG_WIDTH, self::IMG_HEIGHT);
                }
                break;
            default:
                $this->s_title = $this->config->get('config_name');
                $meta_description = $this->config->get('config_meta_description');
                if (is_array($meta_description)) {
                    $lang_id = $this->config->get('config_language_id');
                    if (isset($meta_description[$lang_id])) {
                        $this->s_description = $meta_description[$lang_id] . '...';
                    }
                } else {
                    $this->s_description = $meta_description  . '...';
                }
                $this->s_url = $this->config->get('config_url');
                $this->s_image = $this->model_tool_image->resize($this->config->get('config_logo'), self::IMG_WIDTH, self::IMG_HEIGHT);
                break;
        }

        $this->journal2->settings->set('fb_meta', array(
            'og:title'          => $this->s_title,
            'og:description'    => $this->s_description,
            'og:url'            => $this->s_url,
            'og:image'          => $this->s_image
        ));
    }

}
?>