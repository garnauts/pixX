<?php
class ControllerExtensionPaymentPixx extends Controller {
	public function index() {
		$this->load->language('extension/payment/pixx');

		$data['payable'] = $this->config->get('payment_pixx_payable');
		$data['payment_pixx_default_image_name'] = $this->config->get('payment_pixx_default_image_name');

		return $this->load->view('extension/payment/pixx', $data);
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'pixx') {
			$this->load->language('extension/payment/pixx');

			$this->load->model('checkout/order');

			$comment  = $this->language->get('text_payable') . "\n";
			$comment .= $this->config->get('payment_pixx_payable') . "\n\n";
			
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_pixx_order_status_id'), $comment, true);
			
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}