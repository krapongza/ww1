<?php
class ControllerJournal2Ajax extends Controller {

    public function __construct($reg) {
        parent::__construct($reg);
    }

    public function price() {
        $this->load->model('catalog/product');
        $this->language->load('product/product');

        $product_id = isset($this->request->post['product_id']) ? $this->request->post['product_id'] : 0;
        $product_info = $this->model_catalog_product->getProduct($product_id);

        if (!$product_info) {
            $this->response->setOutput(json_encode(array(
                'error' => 'Product not found'
            )));
            return;
        }

        if (!isset($product_info['tax_class_id'])) {
            $product_info['tax_class_id'] = '';
        }

        $price = 0;
        $special = 0;
        $extra = 0;
        $quantity = $product_info['quantity'];

        if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
            $price = $product_info['price'];
        }

        if ((float)$product_info['special']) {
            $special = $product_info['special'];
        }

        $product_options = $this->model_catalog_product->getProductOptions($product_id);

        foreach ($product_options as $option) {
            if (!in_array($option['type'], array('select', 'radio', 'checkbox', 'image'))) continue;

            $option_ids = Journal2Utils::getProperty($this->request->post, 'option.' . $option['product_option_id'], array());

            if (is_scalar($option_ids)) {
                $option_ids = array($option_ids);
            }

            foreach ($option_ids as $option_id) {
                foreach (Journal2Utils::getProperty($option, 'option_value', array()) as $option_value) {
                    if ($option_id == $option_value['product_option_value_id']) {
                        $quantity = min($quantity, (int)$option_value['quantity']);
                        if ($option_value['price_prefix'] === '+') {
                            $extra += $option_value['price'];
                        } else {
                            $extra -= $option_value['price'];
                        }
                    }
                }
            }
        }

        $tax = $special ? $special : $price;

        $price += $extra;
        $special += $extra;
        $tax += $extra;

        if ($quantity <= 0) {
            $stock = $product_info['stock_status'];
        } elseif ($this->config->get('config_stock_display')) {
            $stock = $quantity;
        } else {
            $stock = $this->language->get('text_instock');
        }

        $this->response->setOutput(json_encode(array(
            'price'     => $this->currency->format($this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax'))),
            'special'   => $this->currency->format($this->tax->calculate($special, $product_info['tax_class_id'], $this->config->get('config_tax'))),
            'tax'       => $this->language->get('text_tax') . ' ' . $this->currency->format($tax),
            'stock'     => $stock
        )));
    }

}
