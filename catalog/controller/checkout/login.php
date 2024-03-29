<?php


class ControllerCheckoutLogin extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');

		$data['checkout_guest'] = ($this->config->get('config_checkout_guest') && !$this->config->get('config_customer_price') && !$this->cart->hasDownload());

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = 'register';
		}

		$data['action'] = $this->url->link('checkout/login', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$this->response->setOutput($this->load->view('checkout/login', $data));
	}

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		if ($this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		
		if (!$json) {
			$this->load->model('account/customer');
			
			if (!isset($this->request->post['email']) || $this->request->post['email'] === '') {
				$json['error']['email'] = $this->language->get('error_email');
			}
			if (!isset($this->request->post['password']) || $this->request->post['password'] === '') {
				$json['error']['password'] = $this->language->get('error_login');
			}
			// Check how many login attempts have been made.
			if (isset($this->request->post['email'])) {
				$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);
	
				if ($login_info && ($login_info['total'] >= 10) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
					$json['error']['warning'] = $this->language->get('error_attempts');
				}
	
				// Check if customer has been approved.
				$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
	
				if ($customer_info && !$customer_info['status']) {
					$json['error']['warning'] = $this->language->get('error_approved');
				}
			}

			if (!isset($json['error'])) {
				if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
					$json['error']['email'] = $this->language->get('error_login');
					$json['error']['password'] = $this->language->get('error_login');

					$this->model_account_customer->addLoginAttempt($this->request->post['email']);
				} else {
					$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
				}
			}
		}

		if (!$json) {
			// Unset guest
			unset($this->session->data['guest']);

			// Default Shipping Address
			$this->load->model('account/address');

			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			// Wishlist
			if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
				$this->load->model('account/wishlist');

				// Save wishlist
				foreach ($this->session->data['wishlist'] as $key => $product_id) {
					$this->model_account_wishlist->addWishlist((int)$product_id);

					unset($this->session->data['wishlist'][$key]);
				}
			}

			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function fetchLoginForm() {
		$data = [];
		$json = [];
		$this->load->language('checkout/checkout');

		$data['checkout_guest'] = ($this->config->get('config_checkout_guest') && !$this->config->get('config_customer_price') && !$this->cart->hasDownload());

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = 'register';
		}

		$data['action'] = $this->url->link('checkout/login', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);

		$json['html']['replace']['.collapse-login'] = $this->load->view('checkout/login', $data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
