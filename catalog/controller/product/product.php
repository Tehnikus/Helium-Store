<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');
		$this->document->setRobots('index,follow');
		$data = array();
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		// TODO Remove category model and use main product category instead
		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';
			$parts = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($parts);

			// foreach ($parts as $path_id) {
			// 	if (!$path) {
			// 		$path = $path_id;
			// 	} else {
			// 		$path .= '_' . $path_id;
			// 	}

			// 	$category_info = $this->model_catalog_category->getCategory($path_id);

			// 	if ($category_info) {
			// 		$data['breadcrumbs'][] = array(
			// 			'text' => $category_info['name'],
			// 			'href' => $this->url->link('product/category', 'path=' . $path)
			// 		);
			// 	}
			// }

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
				);
			}
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		// Get product data
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {

			// Load models
			$this->load->model('tool/image');

			// Flags
			$product_flags = array();
			$product_flags = $this->model_catalog_product->renderFlags((array)$product_info, (int)$product_info['main_category']);
			$data['product_flags'] = $product_flags;

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', '&product_id=' . $this->request->get['product_id'])
			);

			// Images
			// Main image
			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('image_popup_width'), $this->config->get('image_popup_height'));
				$data['thumb']['link'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('image_thumb_width'), $this->config->get('image_thumb_height'));
				
			} else {
				$data['popup'] = $this->model_tool_image->resize('no_image.webp', $this->config->get('image_popup_width'), $this->config->get('image_popup_height'));
				$data['thumb']['link'] = $this->model_tool_image->resize('no_image.webp', $this->config->get('image_thumb_width'), $this->config->get('image_thumb_height'));
			}
			$data['thumb']['width'] = $this->config->get('image_thumb_width');
			$data['thumb']['height'] = $this->config->get('image_thumb_height');
			
			// Additional images
			$data['images'] = array();
			$additional_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
			foreach ($additional_images as $img) {
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($img['image'], $this->config->get('image_popup_width'), $this->config->get('image_popup_height')),
					'thumb' => $this->model_tool_image->resize($img['image'], $this->config->get('image_additional_width'), $this->config->get('image_additional_height')),
					'width' => $this->config->get('image_additional_width'),
					'height' => $this->config->get('image_additional_height'),
				);
			}

			$data['text_minimum'] 		= sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] 		= sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));
			$data['product_id'] 		= (int)$this->request->get['product_id'];
			$data['manufacturer'] 		= $product_info['manufacturer'];
			$data['manufacturers'] 		= $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] 				= $product_info['model'];
			$data['reward'] 			= $product_info['reward'];
			$data['points'] 			= $product_info['points'];
			$data['special_date_end'] 	= $product_info['special_date_end'];
			
			// Set seo data
			$data['meta_title'] 		= $product_info['meta_title'];
			$data['name'] 				= $product_info['name'];
			$data['meta_h1'] 			= $product_info['meta_h1'];
			$data['meta_description'] 	= $product_info['meta_description'];
			$data['meta_keyword'] 		= $product_info['meta_keyword'];
			$this->seoData($data);
			
			
			$data['description'] 		= html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			// Stock status
			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}




			// Prices
			// Base price
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			// Special price
			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}
			// Base price for animating price change when product option is selected
			$data['base_price'] = $tax_price;
			

			// Wholesale discounts
			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
			$data['discounts'] = array();
			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => (int)$discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'discount_date_end' => $discount['date_end']
				);
			}

			// Options
			$data['options'] = array();
			$options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
			foreach ($options as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					// TODO Здесь сделать опцию невыбираемой если она закончилась на складе
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
							$price_value = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency'], '', false);
						} else {
							$price = false;
							$price_value = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							// TODO make option image size configurable
							'image'                   => $option_value['image'] !== '' ? $this->model_tool_image->resize($option_value['image'], 120, 120) : '',
							'price'                   => $price,
							'price_value'			  => $price_value ? $option_value['price_prefix'].$price_value : '',
							'price_prefix'            => $option_value['price_prefix'],
							'default_option'		  => $option_value['default_option']
						);

						// if ($price_value) {
						// 	$data['json_prices'][$product_id]['options'][] = array(
						// 		'option_id' 		=> (int)$option_value['option_value_id'],
						// 		'option_price' 		=> $option_value['price_prefix'].$price_value,
						// 	);
						// } else {
						// 	$data['json_prices'][$product_id]['options'][] = array(
						// 		'option_id' 		=> (int)$option_value['option_value_id'],
						// 		'option_price' 		=> (float)$option_value['price'],
						// 	);
						// }
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required'],
					'default_option_isset' => $option['default_option_isset']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			//////////////////////////////////////////////////
			// JSON object to calculate product price when option is selected or quantity discounts present
			//////////////////////////////////////////////////
			$json_prices = array();
			// $json_prices[$product_id] = array();
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$json_prices[$product_id]['base_price'] = (float)$product_info['price'];
			}
			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$json_prices[$product_id]['base_price'] = (float)$product_info['special'];
			}

			// Discounts
			$json_prices[$product_id]['discounts'][$data['minimum']] = round((float)$json_prices[$product_id]['base_price'], 2);
			
			foreach ($discounts as $discount) {
				$json_prices[$product_id]['discounts'][(int)$discount['quantity']] = round((float)$discount['price'], 2);
			}

			foreach ($options as $option) {
				foreach ($option['product_option_value'] as $option_value) {
					if ($option_value['price'] > 0) {
						$json_prices[$product_id]['options'][] = array(
							(int)$option_value['product_option_value_id'] => $option_value['price_prefix'].$option_value['price'],
						);
					}
				}
			}
			$data['json_prices'] = json_encode($json_prices);
			////////////////////////////////////////////////////

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}


			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);
			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
			
			// Related products
			$data['related_products'] = array();
			$related_products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
			$data['related_products'] = $this->model_catalog_product->prepareProductList($related_products, (int)$product_info['main_category']);


			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			$this->model_catalog_product->addViewedProduct($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			/////////////////
			// Review data //
			/////////////////
			$data['review_status'] = $this->config->get('config_review_status');
			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}
			$data['reviews_header'] = $this->language->get('tab_review');
			$data['reviews_count'] = (int)$product_info['reviews'];
			$data['rating'] = round((float)$product_info['rating'], 2);
			$data['reviews'] = $this->reviews();
			if ((int)$product_info['reviews'] > 0) {
				$data['tab_review'] = $this->language->get('tab_review').' ('.(int)$product_info['reviews'].')';
			}

			$this->microdata($product_info, $data);

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			// 404 Not found
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	// Show reviews list statically
	public function reviews() {
		$data = [];
		$this->load->language('product/product');
		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();
		$data['reviews_count'] = sprintf($this->language->get('text_review_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		return $data;
	}

	// Отдельная функция для подгрузки отзывов Аяксом
	// Получает отзывы из функции reviews()
	// И отображает файл шаблона с отзывами
	public function review() {
		$data = array();
		$data['reviews'] = $this->reviews();
		$this->response->setOutput($this->load->view('common/review_grid', $data));
	}

	// Display review modal window 
	public function showReviewModal() {
		$data = [];
		$response = [];
		$this->load->language('product/product');
		$data['entity_id'] = (int)$this->request->get['entity_id'];
		$data['type'] = 'product/product';
		$response['dialog'] = $this->load->view('common/review_form', $data);
		$this->response->setOutput(json_encode($response));
	}

	public function sendReview() {
		$this->load->language('common/errors');

		$json = array();

		if (isset($this->request->get['entity_id']) && $this->request->get['entity_id']) {
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if (!isset($this->request->post['name']) || (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
					$json['error']['name'] = $this->language->get('error_firstname');
				}

				if (!isset($this->request->post['text']) || (utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
					$json['error']['text'] = $this->language->get('error_review_text');
				}

				if (!isset($this->request->post['rating']) || empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
					$json['error']['rating'] = $this->language->get('error_review_rating');
				}

				// Captcha
				if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
					$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

					if ($captcha) {
						$json['error'] = $captcha;
					}
				}

				if (!isset($json['error'])) {
					$this->load->model('catalog/review');

					$this->model_catalog_review->addReview($this->request->get['entity_id'], $this->request->post);

					$json['dialog'] = $this->language->get('text_success_reviews');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_product');
		} 

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Set SEO data
	public function seoData(&$data) {
		// Meta titles
		!empty($data['meta_title']) ? $this->document->setTitle($data['meta_title']) : $this->document->setTitle($data['name']); 
		// H1 tag
		!empty($data['meta_h1']) ? ($data['heading_title'] = $data['meta_h1']) : ($data['heading_title'] = $data['name']); 
		// Meta Description
		$this->document->setDescription($data['meta_description']);
		// Keywords
		$this->document->setKeywords($data['meta_keyword']);
		// Canonical link
		$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
		// Preload main image
		$this->document->addLink($data['thumb']['link'], 'preload', 'image');
	}

	public function microdata($product_info, &$data) {
		$microdata = array();
		$microdata = [
			'@context' 		=> 'https://schema.org/',
			'@type'			=> 'Product',
			'sku' 			=> $product_info['sku'],
			'ean13' 		=> $product_info['ean'],
			'name' 			=> $product_info['name'],
			'description' 	=> mb_substr(strip_tags($product_info['description']), 0, 260),
			'image' 		=> [],
			'offers' => [
				'@type' 			=> 'Offer',
				'url' 				=> $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']),
				'itemCondition' 	=> 'https://schema.org/NewCondition',
				'availability' 		=> 'https://schema.org/InStock',
				'price' 			=> !empty($product_info['special']) ? $product_info['special'] : $product_info['price'],
				'priceCurrency' 	=> $this->config->get('config_currency'),
				'priceValidUntil' 	=> !empty($product_info['special_date_end']) ? $product_info['special_date_end'] : date('Y-m-d', strtotime('+1 month')),
			],

			'aggregateRating' => [
				'@type' 		=> 'AggregateRating',
				'ratingValue' 	=> $product_info['rating'],
				'reviewCount' 	=> $product_info['reviews']
			]
		];

		// Microdata images
		// Main image
		if ($product_info['image'] && $data['thumb']['link']) {
			$microdata['image'][] = $data['thumb']['link'];
		}
		if (!empty($data['images'])) {
			foreach ($data['images'] as $additional_image) {
				$microdata['image'][] = $additional_image['popup'];
			}
		}
		// Microdata latest reviews
		if (isset($data['reviews']) && !empty($data['reviews']['reviews'])) {
			$microdata['review'] = [];
			foreach ($data['reviews']['reviews'] as $review) {
				$microdata['review'] = array(
					'@type' => 'Review',
					'datePublished' => $review['date_added'],
					'reviewRating' => [
						'@type' => 'Rating',
						'ratingValue' => $review['rating'],
						'bestRating' => 5
					],
					'author' => [
						'@type' => 'Person',
						'name' => $review['author']
					]
				);
			}
		}
		// Microdata brand
		if (!empty($product_info['manufacturer'])) {
			$microdata['brand'] = array(
				'@type' => 'Brand',
				'name' => $product_info['manufacturer']
			);
		}
		$data['microdata'] = json_encode($microdata, JSON_UNESCAPED_UNICODE);

		// $microdata['shippingDetails'] = array(
		// 	'@type' => 'OfferShippingDetails',
		// 	'shippingRate' => [
		// 		'@type' => 'MonetaryAmount',
		// 		'value' => 3.49,
		// 		'currency' => 'USD'
		// 	],
		// 	'shippingDestination' => [
		// 		'@type' => 'DefinedRegion',
		// 		'addressCountry' => 'US'
		// 	],
		// 	'deliveryTime' => [
		// 		'@type' => 'ShippingDeliveryTime',
		// 		'handlingTime' => [
		// 			'@type' => 'QuantitativeValue',
		// 			'minValue' => 0,
		// 			'maxValue' => 1,
		// 			'unitCode' => 'DAY'
		// 		],
		// 		'transitTime' => [
		// 			'@type' => 'QuantitativeValue',
		// 			'minValue' => 1,
		// 			'maxValue' => 5,
		// 			'unitCode' => 'DAY'
		// 		]
		// 	]
		// );
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
