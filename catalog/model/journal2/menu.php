<?php
class ModelJournal2Menu extends Model {

    public function getMenuName($page) {
        $this->language->load('common/header');
        $text_wishlist = $this->language->get('text_wishlist');
        $this->language->load('common/footer');
        $text_manufacturer = $this->language->get('text_manufacturer');
        $this->language->load('account/login');
        $this->language->load('account/logout');
        $this->language->load('account/register');
        $this->language->load('product/category');

        $menus = array(
            'common/home'                   => 'text_home',
            'account/wishlist'              => sprintf($text_wishlist, '<span class="product-count">' . (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0) . '</span>'),
            'product/compare'               => sprintf($this->language->get('text_compare'), '<span class="product-count">' . (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0)  . '</span>'),
            'account/account'               => 'text_account',
            'checkout/cart'                 => 'text_shopping_cart',
            'checkout/checkout'             => 'text_checkout',

            'information/contact'           => 'text_contact',
            'account/return/insert'         => 'text_return',
            'information/sitemap'           => 'text_sitemap',
            'product/manufacturer'          => $text_manufacturer,
            'account/voucher'               => 'text_voucher',
            'affiliate/account'             => 'text_affiliate',
            'product/special'               => 'text_special',
            'product/search'                => 'text_search',
            'account/order'                 => 'text_order',
            'account/newsletter'            => 'text_newsletter',

            'account/login'                 => $this->language->get('text_login'),
            'account/register'              => $this->language->get('text_register'),
            'account/logout'                => $this->language->get('text_logout'),
        );
        return (isset($menus[$page])) ? $this->language->get($menus[$page]) : $page;
    }

    public function getLink($link) {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('catalog/information');
        $href = null;
        /* menu type */
        switch ($link['menu_type']) {
            case 'category':
                $category_info = $this->model_catalog_category->getCategory(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$category_info) continue;
                $href = $this->url->link('product/category', 'path=' . $category_info['category_id']);
                break;
            case 'product':
                $product_info = $this->model_catalog_product->getProduct(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$product_info) continue;
                $href = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                break;
            case 'manufacturer':
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$manufacturer_info) continue;
                $href = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']);
                break;
            case 'information':
                $information_info = $this->model_catalog_information->getInformation(Journal2Utils::getProperty($link, 'menu_item.id', -1));
                if (!$information_info) continue;
                $href = $this->url->link('information/information', 'information_id=' .  $information_info['information_id']);
                break;
            case 'popup':
                $href = "javascript:Journal.openPopup('{$link['menu_item']}')";
                break;
            case 'opencart':
                $customer_name = null;
                switch ($link['menu_item']['page']) {
                    case 'login':
                        $link['menu_item']['page'] = $this->customer->isLogged() ? 'account/account' : 'account/login';
                        $customer_name = $this->customer->getFirstName();
                        break;
                    case 'register':
                        $link['menu_item']['page'] = $this->customer->isLogged() ? 'account/logout' : 'account/register';
                        break;
                    case 'account/wishlist':
                        break;
                    default:
                }
                $href = $link['menu_item']['page'] === 'common/home' ? $this->journal2->config->base_url : $this->url->link($link['menu_item']['page'], '', 'SSL');
                break;
            case 'custom':
                $href = Journal2Utils::getProperty($link, 'menu_item.url');
                break;
        }

        return $href;
    }

}
?>