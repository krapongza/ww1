<?php

class ControllerModuleCustomerReferral extends Controller {
  private $error = array();

  public function index() {
    $this->language->load('module/customer_referral');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('customer_referral', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
    }

    $this->data['heading_title'] = $this->language->get('heading_title');

    $this->data['text_disabled']      = $this->language->get('text_disabled');
    $this->data['text_enabled']       = $this->language->get('text_enabled');
    $this->data['text_fixed_amount']  = sprintf($this->language->get('text_fixed_amount'), $this->currency->getSymbolLeft());
    $this->data['text_percent']       = $this->language->get('text_percent');
    $this->data['text_no']            = $this->language->get('text_no');
    $this->data['text_yes']           = $this->language->get('text_yes');

    $this->data['entry_credit']               = $this->language->get('entry_credit');
    $this->data['entry_credit_all_orders']    = $this->language->get('entry_credit_all_orders');
    $this->data['entry_credit_type']          = $this->language->get('entry_credit_type');
    $this->data['entry_discount']             = $this->language->get('entry_discount');
    $this->data['entry_points']               = $this->language->get('entry_points');
    $this->data['entry_email_description']    = $this->language->get('entry_email_description');
    $this->data['entry_facebook_title']       = $this->language->get('entry_facebook_title');
    $this->data['entry_facebook_description'] = $this->language->get('entry_facebook_description');
    $this->data['entry_status']               = $this->language->get('entry_status');

    $this->data['button_save']    = $this->language->get('button_save');
    $this->data['button_cancel']  = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $this->data['error_warning'] = $this->error['warning'];
    } else {
      $this->data['error_warning'] = '';
    }

    if (isset($this->error['image'])) {
      $this->data['error_image'] = $this->error['image'];
    } else {
      $this->data['error_image'] = '';
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
      'text'      => $this->language->get('heading_title'),
      'href'      => $this->url->link('module/customer_referral', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->data['action'] = $this->url->link('module/customer_referral', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['token'] = $this->session->data['token'];

    if (isset($this->request->post['customer_referral_status'])) {
      $this->data['customer_referral_status'] = $this->request->post['customer_referral_status'];
    } else {
      $this->data['customer_referral_status'] = $this->config->get('customer_referral_status');
    }

    if (isset($this->request->post['customer_referral_discount'])) {
      $this->data['customer_referral_discount'] = $this->request->post['customer_referral_discount'];
    } else {
      $this->data['customer_referral_discount'] = $this->config->get('customer_referral_discount');
    }

    if (isset($this->request->post['customer_referral_credit_type'])) {
      $this->data['customer_referral_credit_type'] = $this->request->post['customer_referral_credit_type'];
    } else {
      $this->data['customer_referral_credit_type'] = $this->config->get('customer_referral_credit_type');
    }

    if (isset($this->request->post['customer_referral_credit'])) {
      $this->data['customer_referral_credit'] = $this->request->post['customer_referral_credit'];
    } else {
      $this->data['customer_referral_credit'] = $this->config->get('customer_referral_credit');
    }

    if (isset($this->request->post['customer_referral_points'])) {
      $this->data['customer_referral_points'] = $this->request->post['customer_referral_points'];
    } else {
      $this->data['customer_referral_points'] = $this->config->get('customer_referral_points');
    }

    if (isset($this->request->post['customer_referral_credit_all_orders'])) {
      $this->data['customer_referral_credit_all_orders'] = $this->request->post['customer_referral_credit_all_orders'];
    } else {
      $this->data['customer_referral_credit_all_orders'] = $this->config->get('customer_referral_credit_all_orders');
    }

    if (isset($this->request->post['customer_referral_email_description'])) {
      $this->data['customer_referral_email_description'] = $this->request->post['customer_referral_email_description'];
    } else {
      $this->data['customer_referral_email_description'] = $this->config->get('customer_referral_email_description');
    }

    if (isset($this->request->post['customer_referral_facebook_title'])) {
      $this->data['customer_referral_facebook_title'] = $this->request->post['customer_referral_facebook_title'];
    } else {
      $this->data['customer_referral_facebook_title'] = $this->config->get('customer_referral_facebook_title');
    }

    if (isset($this->request->post['customer_referral_facebook_description'])) {
      $this->data['customer_referral_facebook_description'] = $this->request->post['customer_referral_facebook_description'];
    } else {
      $this->data['customer_referral_facebook_description'] = $this->config->get('customer_referral_facebook_description');
    }

    $this->template = 'module/customer_referral.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput($this->render());
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'module/customer_referral')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->error) {
      return true;
    } else {
      return false;
    }
  }
}

?>
