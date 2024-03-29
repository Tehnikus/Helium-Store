<?php


class ControllerAccountWishList extends Controller {
	public function index() {
		// if (!$this->customer->isLogged()) {
		// 	$this->session->data['redirect'] = $this->url->link('account/wishlist', '', true);

		// 	$this->response->redirect($this->url->link('account/login', '', true));
		// }
		if (!isset($this->session->data['wishlist'])) {
			$this->session->data['wishlist'] = array();
		}
		

		$this->load->model('catalog/product');
		$this->load->language('account/wishlist');
		$this->load->model('account/wishlist');
		$this->load->model('tool/image');

		if (isset($this->request->get['remove'])) {
			// Remove Wishlist
			$this->model_account_wishlist->deleteWishlist($this->request->get['remove']);

			$this->session->data['success'] = $this->language->get('text_remove');

			$this->response->redirect($this->url->link('account/wishlist'));
		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,nofollow');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			if(!$this->customer->isLogged()) {
				$data['success'] = sprintf(
					$this->language->get('text_login_notice'), 
					$this->url->link('account/login', '', true), 
					$this->url->link('account/register', '', true), 
					
				);
			} else {
				$data['success'] = '';
			}
		}

		$data['products'] = $this->renderWishlistProducts();

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}

	public function add() {
		$this->load->language('account/wishlist');
		$this->load->model('catalog/product');

		$data = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			die;
			// $product_id = 0;
		}

		$this->load->model('account/wishlist');
		$product_info = $this->model_catalog_product->getProduct($product_id);
		

		if ($product_info) {
			if ($this->customer->isLogged()) {
				// DONE Save wishlist upon login
				$this->model_account_wishlist->addWishlist($this->request->post['product_id']);

				$data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
				$total_wishlist_products = $this->customer->isLogged() ? $this->model_account_wishlist->getTotalWishlist() : (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);
				$data['html']['replace']['#wishlist-total'] = sprintf($this->language->get('text_wishlist'), $total_wishlist_products);
			} else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$this->session->data['wishlist'][] = $this->request->post['product_id'];
				$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

				$data['success'] = $this->language->get('text_success').sprintf(
					$this->language->get('text_login_notice'), 
					$this->url->link('account/login', '', true), 
					$this->url->link('account/register', '', true), 
				);
				$total_wishlist_products = $this->customer->isLogged() ? $this->model_account_wishlist->getTotalWishlist() : (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);
				$data['html']['replace']['#wishlist-total'] = sprintf($this->language->get('text_wishlist'), $total_wishlist_products);
			}
		}

		$data['products'] = $this->renderWishlistProducts();
		$data['dialog'] = $this->load->view('account/wishlist_table', $data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function remove() {

		$this->load->language('account/wishlist');
		$this->load->model('account/wishlist');


		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];

			if ($this->customer->isLogged()) {
				$this->model_account_wishlist->deleteWishlist($product_id);
			} else {
				$key = array_search($product_id, $this->session->data['wishlist']);
				if ($key !== false) {
					unset($this->session->data['wishlist'][$key]);
				}
			}
			$response['toasts']['success'][] = $this->language->get('text_remove');
			$total_wishlist_products = $this->customer->isLogged() ? $this->model_account_wishlist->getTotalWishlist() : (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);
			if ($total_wishlist_products > 0) {
				$response['html']['replace']['#wishlist-total'] = sprintf($this->language->get('text_wishlist'), ($total_wishlist_products > 0) ?: '');
				// The "#product_id_'.$product_id" selector will be removed by js
				$response['html']['remove']['#product_id_'.$product_id] = ''; 
			} else {
				// If no products, just display empty template
				$data = [];
				$response['dialog'] = $this->load->view('account/wishlist_table', $data);
				$response['html']['replace']['#wishlist-total'] = sprintf($this->language->get('text_wishlist'), '');
			}
		} else {
			$response['toasts']['error'][] = $this->language->get('text_no_such_product');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function renderWishlistProducts()
	{
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('account/wishlist');
		if (!isset($this->session->data['wishlist'])) {
			$this->session->data['wishlist'] = array();
		}

		$products = array();
		if ($this->customer->isLogged()) {
			$results = $this->model_account_wishlist->getWishlist();
		} else {
			$results = $this->session->data['wishlist'];
		}

		foreach ($results as $key => $result) {
			$product_id = '';
			if (is_array($result) && isset($result['product_id'])) {
				$product_id = (int)$result['product_id'];
			} elseif (is_string($result)) {
				$product_id = (int)$result;
			} else {
				return;
			}
			// $results[$key] = Array ( [customer_id] => 3 [product_id] => 1 [date_added] => 2023-04-08 17:52:26 );
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('image_product_width'), $this->config->get('image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('no_image.webp', $this->config->get('image_product_width'), $this->config->get('image_product_height'));
				}

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $product_info['quantity'];
				} else {
					$stock = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$products[] = array(
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'stock'      => $stock,
					'price'      => $price,
					'special'    => $special,
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
				);
			} else {
				$this->model_account_wishlist->deleteWishlist($product_id);
			}
		}

		return $products;
	}

	public function showWishlistModal() {
		$data = [];
		$response = [];
		$this->load->language('account/wishlist');
		$data['products'] = $this->renderWishlistProducts();
		$response['dialog'] = $this->load->view('account/wishlist_table', $data);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}
}
