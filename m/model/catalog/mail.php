<?php
class ModelCatalogMail extends Model {
		

	public function sendingProduct($order_info) {

			require DIR_APPLICATION.'phpmail/PHPMailerAutoload.php';
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
			$mail->setFrom(MAILUSER, 'mayroses');
			$mail->addAddress($order_info['email'], '');
			$subject = 'Order# '.$order_info['order_id'].' ส่งสินค้าเรียบร้อยแล้ว';
			$message = '<span style="font-size:13px">
			เรียน '.$order_info['firstname'].' '.$order_info['lastname'].'<BR>
			&nbsp;&nbsp;&nbsp;ตอนนี้ทางทีมงานได้ส่งสินค้าให้คุณเรียบร้อยแล้ว<BR>
			หมายเลข Track คือ '.$order_info['tack_code'].'<BR>
			คุณสามารถเช็ครายการได้ที่นี่ <a href="'.HTTP_CATALOG.'order_info.html?order_id='.$order_info['order_id'].'">'.HTTP_CATALOG.'order_info.html?order_id='.$order_info['order_id'].'</a></span>';
			$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$body = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML($body);
			$mail->send();
	}
}
?>
