<?php
class ControllerExtensionPaymentPixx extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/pixx');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->files['payment_pixx_default_image']) && $this->imageValidation($this->request->files['payment_pixx_default_image']) && $this->validate()) {

			if(isset($this->request->files['payment_pixx_default_image']) && $this->request->files['payment_pixx_default_image']['name']) {
				move_uploaded_file($this->request->files['payment_pixx_default_image']["tmp_name"], DIR_IMAGE . "payment/pixx/" . $this->request->files['payment_pixx_default_image']["name"]);
				$this->request->post['payment_pixx_default_image_name'] = "payment/pixx/".$this->request->files['payment_pixx_default_image']["name"];
			}

			if (!isset($this->request->post['payment_pixx_default_image_name']) || !trim($this->request->post['payment_pixx_default_image_name'])) {
				$this->request->post['payment_pixx_default_image_name'] = 'no_image.png';
			  } else {
				$this->request->post['payment_pixx_default_image_name'] = trim($this->request->post['payment_pixx_default_image_name']);
			}

			$this->model_setting_setting->editSetting('payment_pixx', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['payment_pixx_default_image_name'] = $this->config->get('payment_pixx_default_image_name');
		if(!$data['payment_pixx_default_image_name']){
			$data['payment_pixx_default_image_name'] = 'no_image.png';
		}

		$this->load->model('tool/image');

		if(isset($data['payment_pixx_default_image_name']) && $data['payment_pixx_default_image_name']) {
			$data['payment_pixx_default_image_name'] = $data['payment_pixx_default_image_name'];
			$data['payment_pixx_default_image'] = $this->model_tool_image->resize($data['payment_pixx_default_image_name'], 90, 90);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['payable'])) {
			$data['error_payable'] = $this->error['payable'];
		} else {
			$data['error_payable'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/pixx', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/pixx', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_pixx_payable'])) {
			$data['payment_pixx_payable'] = $this->request->post['payment_pixx_payable'];
		} else {
			$data['payment_pixx_payable'] = $this->config->get('payment_pixx_payable');
		}

		if (isset($this->request->post['payment_pixx_total'])) {
			$data['payment_pixx_total'] = $this->request->post['payment_pixx_total'];
		} else {
			$data['payment_pixx_total'] = $this->config->get('payment_pixx_total');
		}

		if (isset($this->request->post['payment_pixx_order_status_id'])) {
			$data['payment_pixx_order_status_id'] = $this->request->post['payment_pixx_order_status_id'];
		} else {
			$data['payment_pixx_order_status_id'] = $this->config->get('payment_pixx_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_pixx_geo_zone_id'])) {
			$data['payment_pixx_geo_zone_id'] = $this->request->post['payment_pixx_geo_zone_id'];
		} else {
			$data['payment_pixx_geo_zone_id'] = $this->config->get('payment_pixx_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_pixx_status'])) {
			$data['payment_pixx_status'] = $this->request->post['payment_pixx_status'];
		} else {
			$data['payment_pixx_status'] = $this->config->get('payment_pixx_status');
		}

		if (isset($this->request->post['payment_pixx_sort_order'])) {
			$data['payment_pixx_sort_order'] = $this->request->post['payment_pixx_sort_order'];
		} else {
			$data['payment_pixx_sort_order'] = $this->config->get('payment_pixx_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/pixx', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/pixx')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_pixx_payable']) {
			$this->error['payable'] = $this->language->get('error_payable');
		}

		return !$this->error;
	}

	private function imageValidation($value){

        $this->load->language('extension/module/customerpartner');
		$error = true;

  		if (isset($value['name']) && !empty($value['name']) && is_file($value['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(html_entity_decode($value['name'], ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
				$this->error['warning'] = $this->language->get('error_filename');
				$error = false;
			}

			// Allowed file extension types
			$allowed = array(
				'jpg',
				'jpeg',
				'gif',
				'png'
			);

			if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
				$this->error['warning'] = $this->language->get('error_filetype');
				$error = false;
			}

			// Allowed file mime types
			$allowed = array(
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/x-png',
				'image/gif'
			);

			if (!in_array($value['type'], $allowed)) {
				$this->error['warning'] = $this->language->get('error_filetype');
				$error = false;
			}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($value['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$this->error['warning'] = $this->language->get('error_filetype');
				$error = false;
			}

			// Return any upload error
			if ($value['error'] != UPLOAD_ERR_OK) {
				$this->error['warning'] = $this->language->get('error_upload_' . $value['error']);
				$error = false;
			}
		}

		return $error;
	}
	
}