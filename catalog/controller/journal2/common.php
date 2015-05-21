<?php
class ControllerJournal2Common extends Controller {

    public function index() {
        $this->load->language('journal2/common');
        $this->helpers();
        $this->languageVars();
        $this->adminWarnings();
    }

    private function helpers() {
        $this->journal2->is_https = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'));
        $this->journal2->store_url = $this->journal2->is_https ? $this->config->get('config_ssl') : $this->config->get('config_url');
        $this->journal2->img_url = $this->journal2->store_url . 'image/';
    }

    private function languageVars() {
        $this->journal2->settings->set('welcome_text', sprintf($this->language->get('j2_welcome_text'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL')));
        $this->journal2->settings->set('logged_in_text', sprintf($this->language->get('j2_logged_in_text'), $this->url->link('account/account', '', 'SSL'), $this->customer->getFirstName(), $this->url->link('account/logout', '', 'SSL')));
    }

    private function adminWarnings() {
        $this->load->library('user');

        $user = new User($this->registry);

        if ($user->isLogged()) {
            if ($this->journal2->is_https) {
                $current_url = parse_url('https://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
                $config_url = parse_url(HTTPS_SERVER);
            } else {
                $current_url = parse_url('http://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI']);
                $config_url = parse_url(HTTP_SERVER);
            }

            if ($config_url['scheme'] . $config_url['host'] !== $current_url['scheme'] . $current_url['host']) {
                $this->journal2->admin_warnings = 'Store address conflict!';
            }
        }
    }

}
