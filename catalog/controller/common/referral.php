<?php

class ControllerCommonReferral extends Controller {
  public function index() {
    if ($this->config->get('customer_referral_status')) {
      if (isset($this->request->get['referral'])) {
        setcookie('referral', $this->request->get['referral'], time() + 3600 * 24 * 1000, '/');
      }

      if (method_exists($this->document, 'addMeta')) {
        if ($this->config->get('customer_referral_facebook_title')) {
          $this->document->addMeta('og:title', $this->config->get('customer_referral_facebook_title'));
        } else {
          $this->document->addMeta('og:title', $this->config->get('config_title'));
        }

        if ($this->config->get('customer_referral_facebook_description')) {
          $this->document->addMeta('og:description', $this->config->get('customer_referral_facebook_description'));
        } else {
          $this->document->addMeta('og:description', $this->config->get('config_meta_description'));
        }

        if (isset($this->request->get['referral'])) {
          $this->document->addMeta('og:url', $this->url->link('common/referral', 'referral=' . $this->request->get['referral']));
        }

        $this->load->model('tool/image');

        $this->document->addMeta('og:site_name', $this->config->get('config_name'));

        if (!array_key_exists(md5('og:image'), $this->document->getMetas())) {
          $this->document->addMeta('og:image', $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100));
        }
      }

      $this->request->get['route'] = 'common/home';

      $this->response->setOutput($this->getChild('common/home'));
    } else {
      $this->redirect($this->url->link('common/home'));
    }
  }
}

?>
