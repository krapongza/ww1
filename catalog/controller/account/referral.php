<?php

class ControllerAccountReferral extends Controller {
  public function index() {
    if ($this->config->get('customer_referral_status')) {
      if (!$this->customer->isLogged()) {
        $this->session->data['redirect'] = $this->url->link('account/referral', '', 'SSL');
        $this->redirect($this->url->link('account/login', '', 'SSL'));
      }

      $this->load->language('account/referral');
      $this->document->setTitle($this->language->get('heading_title'));
      $this->data['breadcrumbs'] = array();
      $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
        'href'      => $this->url->link('common/home'),
        'separator' => false
      );
      $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_account'),
        'href'      => $this->url->link('account/account', '', 'SSL'),
        'separator' => $this->language->get('text_separator')
      );
      $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('heading_title'),
        'href'      => $this->url->link('account/referral', '', 'SSL'),
        'separator' => $this->language->get('text_separator')
      );
      $this->data['heading_title'] = $this->language->get('heading_title');
      $this->data['button_resend']          = $this->language->get('button_resend');
      $this->data['button_send']            = $this->language->get('button_send');
      $this->data['button_share_facebook']  = $this->language->get('button_share_facebook');

      $this->data['column_credit']  = $this->language->get('column_credit');
      $this->data['column_date']    = $this->language->get('column_date');
      $this->data['column_email']   = $this->language->get('column_email');
      $this->data['column_status']  = $this->language->get('column_status');

      $this->data['entry_email']                = $this->language->get('entry_email');
      $this->data['entry_message']              = $this->language->get('entry_message');
      $this->data['entry_total_earned_credit']  = $this->language->get('entry_total_earned_credit');
      $this->data['entry_total_earned_points']  = $this->language->get('entry_total_earned_points');

      $this->data['text_referrals']       = $this->language->get('text_referrals');
      $this->data['text_no_referrals']    = $this->language->get('text_no_referrals');
      $this->data['text_or_social']       = $this->language->get('text_or_social');
      $this->data['text_points']          = $this->language->get('text_points');
      $this->data['text_send_referral']   = $this->language->get('text_send_referral');
      $this->data['text_share']           = $this->language->get('text_share');
      $this->data['text_view_available']  = $this->language->get('text_view_available');

      $this->data['email']    = $this->url->link('account/referral/email', '', 'SSL');
      $this->data['facebook'] = $this->url->link('account/referral/facebook', '', 'SSL');

      $this->data['transactions'] = $this->url->link('account/transaction', '', 'SSL');
      $this->data['rewards']      = $this->url->link('account/reward', '', 'SSL');

      $this->data['email_description'] = $this->config->get('customer_referral_email_description');

      $this->data['referrals'] = array();

      $this->load->model('account/referral');

      $results = $this->model_account_referral->getReferrals();

      foreach ($results as $result) {
        if ($result['email'] != 'facebook-referral') {
          if ($result['status']) {
            $href = $this->url->link('account/referral/email', 'code=' . $result['code'], 'SSL');
          } else {
            $href = '';
          }

          $this->data['referrals'][] = array(
            'date_added'  => date($this->language->get('date_format'), strtotime($result['date_added'])),
            'email'       => $result['email'],
            'status'      => ($result['status'] ? $this->language->get('text_pending') : $this->language->get('text_complete')),
            'credit'      => $this->currency->format($result['credit']),
            'points'      => $result['points'],
            'href'        => $href
          );
        }
      }

      $facebook_referral_info = $this->model_account_referral->getReferralByEmail('facebook-referral');

      if (!$facebook_referral_info) {
        $customer_referral_id = $this->model_account_referral->addReferral(array(
          'email'       => 'facebook-referral',
          'single_use'  => false
        ));

        $facebook_referral_info = $this->model_account_referral->getReferral($customer_referral_id);
      }

      $this->data['facebook_href']  = $this->url->link('common/referral', 'referral=' . $facebook_referral_info['code']);

      $total_earned_credit = $this->currency->format($this->model_account_referral->getReferralCreditTotal());

      if ($total_earned_credit || ($this->config->get('customer_referral_credit') && $this->config->get('customer_referral_credit') > 0)) {
        $this->data['total_earned_credit'] = $this->currency->format($total_earned_credit);
      } else {
        $this->data['total_earned_credit'] = '';
      }

      $total_earned_points = $this->model_account_referral->getReferralPointsTotal();

      if ($total_earned_points || ($this->config->get('customer_referral_points') && $this->config->get('customer_referral_points') > 0)) {
        $this->data['total_earned_points'] = $this->model_account_referral->getReferralPointsTotal();
      } else {
        $this->data['total_earned_points'] = '';
      }


		$this->load->model('account/customer');
		$point_credit = $this->model_account_customer->getPointCredit();
		$this->data['point'] = $point_credit['point'];
		$this->data['credit'] =  $point_credit['credit'];


      $this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
      $this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');

      if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/referral.tpl')) {
        $this->template = $this->config->get('config_template') . '/template/account/referral.tpl';
      } else {
        $this->template = 'default/template/account/referral.tpl';
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

  public function email() {
    $json = array();

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/referral', '', 'SSL');

      $json['redirect'] = $this->url->link('account/login', '', 'SSL');
    }

    if (!$json) {
      $this->load->language('account/referral');

      $this->load->model('account/referral');

      $customer_referrals = array();

      if (!empty($this->request->get['code'])) {
        $customer_referral_info = $this->model_account_referral->getReferralByCode($this->request->get['code']);

        if ($customer_referral_info) {
          if ($customer_referral_info['status']) {
            $customer_referrals[] = $customer_referral_info;
          } else {
            $json['error'] = $this->language->get('error_used');
          }
        } else {
          $json['error'] = $this->language->get('error_exist');
        }
      } else if (!empty($this->request->post['emails'])) {
        $json['referrals'] = array();

        $emails = explode(',', $this->request->post['emails']);

        foreach ($emails as $email) {
          $email = trim($email);

          $customer_referral_info = $this->model_account_referral->getReferralByEmail($email);

          if ($customer_referral_info) {
            if ($customer_referral_info['status']) {
              $customer_referrals[] = $customer_referral_info;
            } else {
              $json['error'] = $this->language->get('error_used');
            }
          } else {
            $customer_referral_id = $this->model_account_referral->addReferral(array(
              'email'       => $email,
              'single_use'  => true
            ));

            $customer_referral_info = $this->model_account_referral->getReferral($customer_referral_id);

            $customer_referrals[] = $customer_referral_info;

            $json['referrals'][] = array(
              'date_added'  => date($this->language->get('date_format'), strtotime($customer_referral_info['date_added'])),
              'email'       => $customer_referral_info['email'],
              'status'      => $this->language->get('text_pending'),
              'credit'      => $this->currency->format($customer_referral_info['credit']),
              'points'      => $customer_referral_info['points'],
              'href'        => $this->url->link('account/referral/email', 'code=' . $customer_referral_info['code'], 'SSL')
            );
          }
        }
      } else {
        $json['error'] = $this->language->get('error_email');
      }

      if (empty($json['error'])) {
        $emails_sent = array();

        foreach ($customer_referrals as $customer_referral_info) {
          $customer = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();

          $url = $this->url->link('common/referral', 'referral=' . $customer_referral_info['code']);

          $text_data = array();

          $text_data[] = sprintf($this->language->get('email_prefix'), $customer_referral_info['email']);

			$msg = "";
          if (!empty($this->request->post['message'])) {
            $text_data[] = $this->request->post['message'];
			$msg = $this->request->post['message'];
          } else if ($this->config->get('customer_referral_email_description')) {
            $text_data[] = $this->config->get('customer_referral_email_description');
			$msg = $this->config->get('customer_referral_email_description');
          }

          $text_data[] = sprintf($this->language->get('email_suffix'), $this->config->get('config_name'), $url);
		 // $text_data[] = "<a href='".$url."'><img src='http://mayroses.veerawit.com/image/data/logo.png'></a>";

$text = "เพื่อนคุณ ".$customer_referral_info['email']." ได้ส่งข้อความมาหาคุณ : ".$msg." <br>ติดตามเรา Mayroses ได้ที่: <a href='".$url."'><img src='http://mayroses.veerawit.com/image/data/logo.png'></a>";


require 'phpmail/PHPMailerAutoload.php';
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';
$mail->Debugoutput = 'html';
$mail->Host       = MAILIP; // SMTP server example
$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Port       = 25;                    // set the SMTP port for the GMAIL server
$mail->Username   = MAILUSER; // SMTP account username example
$mail->Password   = MAILPASSWORD;        // SMTP account password example
$mail->setFrom( MAILUSER, 'mayroses');
$mail->addAddress($customer_referral_info['email'], '');
$mail->addReplyTo($this->customer->getEmail(), '');
$mail->Subject = html_entity_decode(sprintf($this->language->get('email_subject'), $this->config->get('config_name'), $customer) )    ;
$body = strip_tags(html_entity_decode(implode("\n\n", $text_data)));
$mail->MsgHTML($text);
         // $mail = new Mail();
         /* $mail->protocol   = $this->config->get('config_mail_protocol');
          $mail->parameter  = '';//$this->config->get('config_mail_parameter');
          $mail->hostname   = "27.254.96.11"; //$this->config->get('config_smtp_host');
          $mail->username   = "info@cimbthaionlinecampaign.com"; //$this->config->get('config_smtp_username');
          $mail->password   = "Cim1213!"; //$this->config->get('config_smtp_password');
          $mail->port       = 25; //$this->config->get('config_smtp_port');
          $mail->timeout    = 5; //$this->config->get('config_smtp_timeout');*/

          //$mail->setTo($customer_referral_info['email']);
          //$mail->setFrom($this->config->get('config_email'));
		  //$mail->setFrom('info@cimbthaionlinecampaign.com', 'mayroses');
          //$mail->setSender($this->config->get('config_name'));
         // $mail->setReplyTo($this->customer->getEmail());
          //$mail->setReplyToSender($customer);
          //$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->config->get('config_name'), $customer), ENT_QUOTES, 'UTF-8'));
          //$mail->setText(strip_tags(html_entity_decode(implode("\n\n", $text_data), ENT_QUOTES, 'UTF-8')));
          $mail->send();

          $emails_sent[] = $customer_referral_info['email'];
        }

        $json['success'] = sprintf($this->language->get('text_success_email'), implode(', ', $emails_sent));
      }
    }

    $this->response->setOutput(json_encode($json));
  }

  public function facebook() {
    $json = array();

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/referral', '', 'SSL');

      $json['redirect'] = $this->url->link('account/login', '', 'SSL');
    }

    if (!$json) {
      $this->load->language('account/referral');

      $this->load->model('account/referral');

      $email = 'facebook-referral';

      if (!empty($this->request->get['code'])) {
        $customer_referral_info = $this->model_account_referral->getReferralByCode($this->request->get['code']);

        if ($customer_referral_info && $customer_referral_info['email'] == $email) {
          if (!$customer_referral_info['status']) {
            $json['error'] = $this->language->get('error_used');
          }
        } else {
          $json['error'] = $this->language->get('error_exist');
        }
      } else {
        $customer_referral_info = $this->model_account_referral->getReferralByEmail($email);

        if ($customer_referral_info) {
          if (!$customer_referral_info['status']) {
            $json['error'] = $this->language->get('error_used');
          }
        } else {
          $customer_referral_id = $this->model_account_referral->addReferral(array(
            'email'       => $email,
            'single_use'  => false
          ));

          $customer_referral_info = $this->model_account_referral->getReferral($customer_referral_id);

          $json['referral'] = array(
            'date_added'  => date($this->language->get('date_format'), strtotime($customer_referral_info['date_added'])),
            'email'       => $customer_referral_info['email'],
            'status'      => $this->language->get('text_pending'),
            'credit'      => $this->currency->format($customer_referral_info['credit']),
            'points'      => $customer_referral_info['points'],
            'href'        => $this->url->link('account/referral/facebook', 'code=' . $customer_referral_info['code'], 'SSL')
          );
        }
      }

      if (empty($json['error'])) {
        $json['success'] = $this->language->get('text_success_facebook');

        $json['url'] = $this->url->link('common/referral', 'referral=' . $customer_referral_info['code']);
      }
    }

    $this->response->setOutput(json_encode($json));
  }
}
