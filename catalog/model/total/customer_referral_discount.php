<?php

class ModelTotalCustomerReferralDiscount extends Model {
  public function getTotal(&$total_data, &$total, &$taxes) {
 
    if ($this->config->get('customer_referral_status') && $this->config->get('customer_referral_discount') && isset($this->request->cookie['referral']) && !isset($this->session->data['coupon'])) {
      $this->language->load('total/customer_referral_discount');
 
      $this->load->model('checkout/customer_referral');

      $customer_referral_info = $this->model_checkout_customer_referral->getCustomerReferralByCode($this->request->cookie['referral']);
//print_r($customer_referral_info);
      if ($customer_referral_info) {
        if (!$this->customer->isLogged() || $customer_referral_info['customer_id'] != $this->customer->getId()) {
          $discount_total = $total / 100 * (float)$this->config->get('customer_referral_discount');

          $total_data[] = array(
            'code'        => 'customer_referral_discount',
            'title'       => sprintf($this->language->get('text_customer_referral_discount'), $this->config->get('customer_referral_discount')),
            'text'        => $this->currency->format(-$discount_total),
            'value'       => -$discount_total,
            'sort_order'  => $this->config->get('customer_referral_discount_sort_order')
          );

          $total -= $discount_total;
        } else {
          setcookie('referral', '', (time() - 3600), '/');
        }
      }
    }
  }
}

?>
