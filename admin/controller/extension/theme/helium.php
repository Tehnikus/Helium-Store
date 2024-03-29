<?php
class ControllerExtensionThemeHelium extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/theme/helium');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('theme_helium', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			// $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['product_limit'])) {
			$data['error_product_limit'] = $this->error['product_limit'];
		} else {
			$data['error_product_limit'] = '';
		}

		if (isset($this->error['product_description_length'])) {
			$data['error_product_description_length'] = $this->error['product_description_length'];
		} else {
			$data['error_product_description_length'] = '';
		}

		if (isset($this->error['image_category'])) {
			$data['error_image_category'] = $this->error['image_category'];
		} else {
			$data['error_image_category'] = '';
		}

		if (isset($this->error['image_thumb'])) {
			$data['error_image_thumb'] = $this->error['image_thumb'];
		} else {
			$data['error_image_thumb'] = '';
		}

		if (isset($this->error['image_popup'])) {
			$data['error_image_popup'] = $this->error['image_popup'];
		} else {
			$data['error_image_popup'] = '';
		}

		if (isset($this->error['image_product'])) {
			$data['error_image_product'] = $this->error['image_product'];
		} else {
			$data['error_image_product'] = '';
		}

		if (isset($this->error['image_additional'])) {
			$data['error_image_additional'] = $this->error['image_additional'];
		} else {
			$data['error_image_additional'] = '';
		}

		if (isset($this->error['image_related'])) {
			$data['error_image_related'] = $this->error['image_related'];
		} else {
			$data['error_image_related'] = '';
		}

		if (isset($this->error['image_compare'])) {
			$data['error_image_compare'] = $this->error['image_compare'];
		} else {
			$data['error_image_compare'] = '';
		}

		if (isset($this->error['image_wishlist'])) {
			$data['error_image_wishlist'] = $this->error['image_wishlist'];
		} else {
			$data['error_image_wishlist'] = '';
		}

		if (isset($this->error['image_cart'])) {
			$data['error_image_cart'] = $this->error['image_cart'];
		} else {
			$data['error_image_cart'] = '';
		}

		if (isset($this->error['image_location'])) {
			$data['error_image_location'] = $this->error['image_location'];
		} else {
			$data['error_image_location'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/theme/helium', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		);

		$data['action'] = $this->url->link('extension/theme/helium', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_setting_setting->getSetting('theme_helium', $this->request->get['store_id']);
		}
		
		if (isset($this->request->post['theme_helium_directory'])) {
			$data['theme_helium_directory'] = $this->request->post['theme_helium_directory'];
		} elseif (isset($setting_info['theme_helium_directory'])) {
			$data['theme_helium_directory'] = $setting_info['theme_helium_directory'];
		} else {
			$data['theme_helium_directory'] = 'helium';
		}		

		$data['directories'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);

		foreach ($directories as $directory) {
			$data['directories'][] = basename($directory);
		}

	
		
		if (isset($this->request->post['theme_helium_status'])) {
			$data['theme_helium_status'] = $this->request->post['theme_helium_status'];
		} elseif (isset($setting_info['theme_helium_status'])) {
			$data['theme_helium_status'] = $setting_info['theme_helium_status'];
		} else {
			$data['theme_helium_status'] = '';
		}

		// Caching settings
		if (isset($this->request->post['theme_helium_header_caching'])) {
			$data['theme_helium_header_caching'] = $this->request->post['theme_helium_header_caching'];
		} elseif (isset($setting_info['theme_helium_header_caching'])) {
			$data['theme_helium_header_caching'] = $setting_info['theme_helium_header_caching'];
		} else {
			$data['theme_helium_header_caching'] = '';
		}
		if (isset($this->request->post['theme_helium_category_caching'])) {
			$data['theme_helium_category_caching'] = $this->request->post['theme_helium_category_caching'];
		} elseif (isset($setting_info['theme_helium_category_caching'])) {
			$data['theme_helium_category_caching'] = $setting_info['theme_helium_category_caching'];
		} else {
			$data['theme_helium_category_caching'] = '';
		}
		if (isset($this->request->post['theme_helium_product_caching'])) {
			$data['theme_helium_product_caching'] = $this->request->post['theme_helium_product_caching'];
		} elseif (isset($setting_info['theme_helium_product_caching'])) {
			$data['theme_helium_product_caching'] = $setting_info['theme_helium_product_caching'];
		} else {
			$data['theme_helium_product_caching'] = '';
		}
		if (isset($this->request->post['theme_helium_footer_caching'])) {
			$data['theme_helium_footer_caching'] = $this->request->post['theme_helium_footer_caching'];
		} elseif (isset($setting_info['theme_helium_footer_caching'])) {
			$data['theme_helium_footer_caching'] = $setting_info['theme_helium_footer_caching'];
		} else {
			$data['theme_helium_footer_caching'] = '';
		}
		
		if (isset($this->request->post['theme_helium_product_description_length'])) {
			$data['theme_helium_product_description_length'] = $this->request->post['theme_helium_product_description_length'];
		} elseif (isset($setting_info['theme_helium_product_description_length'])) {
			$data['theme_helium_product_description_length'] = $setting_info['theme_helium_product_description_length'];
		} else {
			$data['theme_helium_product_description_length'] = 150;
		}

		if (isset($this->request->post['theme_helium_product_limit'])) {
			$data['theme_helium_product_limit'] = $this->request->post['theme_helium_product_limit'];
		} elseif (isset($setting_info['theme_helium_product_limit'])) {
			$data['theme_helium_product_limit'] = $setting_info['theme_helium_product_limit'];
		} else {
			$data['theme_helium_product_limit'] = 15;
		}	

		// Bestseller limit
		if (isset($this->request->post['bestsellers_limit'])) {
			$data['bestsellers_limit'] = $this->request->post['bestsellers_limit'];
		} elseif (isset($setting_info['bestsellers_limit'])) {
			$data['bestsellers_limit'] = $setting_info['bestsellers_limit'];
		} else {
			$data['bestsellers_limit'] = 15;
		}

		// Best reviews limit
		if (isset($this->request->post['best_reviews_limit'])) {
			$data['best_reviews_limit'] = $this->request->post['best_reviews_limit'];
		} elseif (isset($setting_info['best_reviews_limit'])) {
			$data['best_reviews_limit'] = $setting_info['best_reviews_limit'];
		} else {
			$data['best_reviews_limit'] = 15;
		}

		// Most viewed limit
		if (isset($this->request->post['most_viewed_limit'])) {
			$data['most_viewed_limit'] = $this->request->post['most_viewed_limit'];
		} elseif (isset($setting_info['most_viewed_limit'])) {
			$data['most_viewed_limit'] = $setting_info['most_viewed_limit'];
		} else {
			$data['most_viewed_limit'] = 15;
		}
		
		// Logo		
		if (isset($this->request->post['theme_helium_image_logo_width'])) {
			$data['theme_helium_image_logo_width'] = $this->request->post['theme_helium_image_logo_width'];
		} elseif (isset($setting_info['theme_helium_image_logo_width'])) {
			$data['theme_helium_image_logo_width'] = $setting_info['theme_helium_image_logo_width'];
		} else {
			$data['theme_helium_image_logo_width'] = 400;		
		}
		if (isset($this->request->post['theme_helium_image_logo_height'])) {
			$data['theme_helium_image_logo_height'] = $this->request->post['theme_helium_image_logo_height'];
		} elseif (isset($setting_info['theme_helium_image_logo_height'])) {
			$data['theme_helium_image_logo_height'] = $setting_info['theme_helium_image_logo_height'];
		} else {
			$data['theme_helium_image_logo_height'] = 100;
		}

		// Category miniature
		if (isset($this->request->post['image_category_width'])) {
			$data['image_category_width'] = $this->request->post['image_category_width'];
		} elseif (isset($setting_info['image_category_width'])) {
			$data['image_category_width'] = $setting_info['image_category_width'];
		} else {
			$data['image_category_width'] = 160;		
		}
		
		if (isset($this->request->post['image_category_height'])) {
			$data['image_category_height'] = $this->request->post['image_category_height'];
		} elseif (isset($setting_info['image_category_height'])) {
			$data['image_category_height'] = $setting_info['image_category_height'];
		} else {
			$data['image_category_height'] = 160;
		}


		
		// Product images
		if (isset($this->request->post['image_thumb_width'])) {
			$data['image_thumb_width'] = $this->request->post['image_thumb_width'];
		} elseif (isset($setting_info['image_thumb_width'])) {
			$data['image_thumb_width'] = $setting_info['image_thumb_width'];
		} else {
			$data['image_thumb_width'] = 800;
		}
		
		if (isset($this->request->post['image_thumb_height'])) {
			$data['image_thumb_height'] = $this->request->post['image_thumb_height'];
		} elseif (isset($setting_info['image_thumb_height'])) {
			$data['image_thumb_height'] = $setting_info['image_thumb_height'];
		} else {
			$data['image_thumb_height'] = 800;		
		}
		
		if (isset($this->request->post['image_popup_width'])) {
			$data['image_popup_width'] = $this->request->post['image_popup_width'];
		} elseif (isset($setting_info['image_popup_width'])) {
			$data['image_popup_width'] = $setting_info['image_popup_width'];
		} else {
			$data['image_popup_width'] = 1200;
		}
		
		if (isset($this->request->post['image_popup_height'])) {
			$data['image_popup_height'] = $this->request->post['image_popup_height'];
		} elseif (isset($setting_info['image_popup_height'])) {
			$data['image_popup_height'] = $setting_info['image_popup_height'];
		} else {
			$data['image_popup_height'] = 1200;
		}
		
		if (isset($this->request->post['image_product_width'])) {
			$data['image_product_width'] = $this->request->post['image_product_width'];
		} elseif (isset($setting_info['image_product_width'])) {
			$data['image_product_width'] = $setting_info['image_product_width'];
		} else {
			$data['image_product_width'] = 400;
		}
		
		if (isset($this->request->post['image_product_height'])) {
			$data['image_product_height'] = $this->request->post['image_product_height'];
		} elseif (isset($setting_info['image_product_height'])) {
			$data['image_product_height'] = $setting_info['image_product_height'];
		} else {
			$data['image_product_height'] = 400;
		}
		
		if (isset($this->request->post['image_additional_width'])) {
			$data['image_additional_width'] = $this->request->post['image_additional_width'];
		} elseif (isset($setting_info['image_additional_width'])) {
			$data['image_additional_width'] = $setting_info['image_additional_width'];
		} else {
			$data['image_additional_width'] = 160;
		}
		
		if (isset($this->request->post['image_additional_height'])) {
			$data['image_additional_height'] = $this->request->post['image_additional_height'];
		} elseif (isset($setting_info['image_additional_height'])) {
			$data['image_additional_height'] = $setting_info['image_additional_height'];
		} else {
			$data['image_additional_height'] = 160;
		}
		
		if (isset($this->request->post['theme_helium_image_related_width'])) {
			$data['theme_helium_image_related_width'] = $this->request->post['theme_helium_image_related_width'];
		} elseif (isset($setting_info['theme_helium_image_related_width'])) {
			$data['theme_helium_image_related_width'] = $setting_info['theme_helium_image_related_width'];
		} else {
			$data['theme_helium_image_related_width'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_related_height'])) {
			$data['theme_helium_image_related_height'] = $this->request->post['theme_helium_image_related_height'];
		} elseif (isset($setting_info['theme_helium_image_related_height'])) {
			$data['theme_helium_image_related_height'] = $setting_info['theme_helium_image_related_height'];
		} else {
			$data['theme_helium_image_related_height'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_compare_width'])) {
			$data['theme_helium_image_compare_width'] = $this->request->post['theme_helium_image_compare_width'];
		} elseif (isset($setting_info['theme_helium_image_compare_width'])) {
			$data['theme_helium_image_compare_width'] = $setting_info['theme_helium_image_compare_width'];
		} else {
			$data['theme_helium_image_compare_width'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_compare_height'])) {
			$data['theme_helium_image_compare_height'] = $this->request->post['theme_helium_image_compare_height'];
		} elseif (isset($setting_info['theme_helium_image_compare_height'])) {
			$data['theme_helium_image_compare_height'] = $setting_info['theme_helium_image_compare_height'];
		} else {
			$data['theme_helium_image_compare_height'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_wishlist_width'])) {
			$data['theme_helium_image_wishlist_width'] = $this->request->post['theme_helium_image_wishlist_width'];
		} elseif (isset($setting_info['theme_helium_image_wishlist_width'])) {
			$data['theme_helium_image_wishlist_width'] = $setting_info['theme_helium_image_wishlist_width'];
		} else {
			$data['theme_helium_image_wishlist_width'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_wishlist_height'])) {
			$data['theme_helium_image_wishlist_height'] = $this->request->post['theme_helium_image_wishlist_height'];
		} elseif (isset($setting_info['theme_helium_image_wishlist_height'])) {
			$data['theme_helium_image_wishlist_height'] = $setting_info['theme_helium_image_wishlist_height'];
		} else {
			$data['theme_helium_image_wishlist_height'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_cart_width'])) {
			$data['theme_helium_image_cart_width'] = $this->request->post['theme_helium_image_cart_width'];
		} elseif (isset($setting_info['theme_helium_image_cart_width'])) {
			$data['theme_helium_image_cart_width'] = $setting_info['theme_helium_image_cart_width'];
		} else {
			$data['theme_helium_image_cart_width'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_cart_height'])) {
			$data['theme_helium_image_cart_height'] = $this->request->post['theme_helium_image_cart_height'];
		} elseif (isset($setting_info['theme_helium_image_cart_height'])) {
			$data['theme_helium_image_cart_height'] = $setting_info['theme_helium_image_cart_height'];
		} else {
			$data['theme_helium_image_cart_height'] = 400;
		}
		
		if (isset($this->request->post['theme_helium_image_location_width'])) {
			$data['theme_helium_image_location_width'] = $this->request->post['theme_helium_image_location_width'];
		} elseif (isset($setting_info['theme_helium_image_location_width'])) {
			$data['theme_helium_image_location_width'] = $setting_info['theme_helium_image_location_width'];
		} else {
			$data['theme_helium_image_location_width'] = 800;
		}
		
		if (isset($this->request->post['theme_helium_image_location_height'])) {
			$data['theme_helium_image_location_height'] = $this->request->post['theme_helium_image_location_height'];
		} elseif (isset($setting_info['theme_helium_image_location_height'])) {
			$data['theme_helium_image_location_height'] = $setting_info['theme_helium_image_location_height'];
		} else {
			$data['theme_helium_image_location_height'] = 800;
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/theme/helium', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/theme/helium')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['theme_helium_product_limit']) {
			$this->error['product_limit'] = $this->language->get('error_limit');
		}

		if (!$this->request->post['theme_helium_product_description_length']) {
			$this->error['product_description_length'] = $this->language->get('error_limit');
		}

		if (!$this->request->post['image_category_width'] || !$this->request->post['image_category_height']) {
			$this->error['image_category'] = $this->language->get('error_image_category');
		}
		
		if (!$this->request->post['theme_helium_image_logo_width'] || !$this->request->post['theme_helium_image_logo_height']) {
			$this->error['image_logo'] = $this->language->get('error_image_category');
		}

		if (!$this->request->post['image_thumb_width'] || !$this->request->post['image_thumb_height']) {
			$this->error['image_thumb'] = $this->language->get('error_image_thumb');
		}

		if (!$this->request->post['image_popup_width'] || !$this->request->post['image_popup_height']) {
			$this->error['image_popup'] = $this->language->get('error_image_popup');
		}

		if (!$this->request->post['image_product_width'] || !$this->request->post['image_product_height']) {
			$this->error['image_product'] = $this->language->get('error_image_product');
		}

		if (!$this->request->post['image_additional_width'] || !$this->request->post['image_additional_height']) {
			$this->error['image_additional'] = $this->language->get('error_image_additional');
		}

		if (!$this->request->post['theme_helium_image_related_width'] || !$this->request->post['theme_helium_image_related_height']) {
			$this->error['image_related'] = $this->language->get('error_image_related');
		}

		if (!$this->request->post['theme_helium_image_compare_width'] || !$this->request->post['theme_helium_image_compare_height']) {
			$this->error['image_compare'] = $this->language->get('error_image_compare');
		}

		if (!$this->request->post['theme_helium_image_wishlist_width'] || !$this->request->post['theme_helium_image_wishlist_height']) {
			$this->error['image_wishlist'] = $this->language->get('error_image_wishlist');
		}

		if (!$this->request->post['theme_helium_image_cart_width'] || !$this->request->post['theme_helium_image_cart_height']) {
			$this->error['image_cart'] = $this->language->get('error_image_cart');
		}

		if (!$this->request->post['theme_helium_image_location_width'] || !$this->request->post['theme_helium_image_location_height']) {
			$this->error['image_location'] = $this->language->get('error_image_location');
		}

		return !$this->error;
	}
}
