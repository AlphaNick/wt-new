<?php
namespace Opencart\Catalog\Controller\Extension\Wdonsale\Module;
use \Opencart\System\Helper AS Helper;
class Wdonsale extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/wdonsale/module/wdonsale');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['products'] = [];

		$data['special'] = $this->url->link('product/special');

		if (!empty($setting['product'])) {
			$products = [];
			$product_data = [];

			foreach ($setting['product'] as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					$products[] = $product_info;
				}
			}

			foreach ($products as $product) {					
				// Webibazaar Custom 15-12-2022
				$images = [];
				if ($product['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
					$images[] = [
						'additional' => $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
						'thumb' => $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					$images[] = [
						'additional' => $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']),
						'thumb' => $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				}
				
				$results = $this->model_catalog_product->getImages($product['product_id']);
				
				foreach ($results as $result) {
					if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
						$images[] = [
							'additional' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
							'thumb' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
						];
						break;
					}
				}
				// Webibazaar Custom end 

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					// Webibazaar Custom Code 15-12-2022
					$after_tex_special = $this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
					$after_tex_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					$discount = round((($after_tex_price - $after_tex_special) * 100) / $after_tex_price);
					$save = $this->currency->format($after_tex_price - $after_tex_special, $this->session->data['currency']);
					// End
				} else {
					$special = false;
					$discount = false; // Webibazaar Custom Code 15-12-2022
					$save = false; // Webibazaar Custom Code 15-12-2022
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $product['rating'];
				} else {
					$rating = false;
				}
				// option data start  01-07-23
				$product_options = $this->model_catalog_product->getOptions((int)$product['product_id']);
				//print_r($product_options);
				$option_data = [];
				foreach ($product_options as $option) {
					if ((int)$product['product_id'] && !isset($product['override']['variant'][$option['product_option_id']])) {
						$product_option_value_data = [];
						//echo $product['product_id']."Hello"."<br>";
						foreach ($option['product_option_value'] as $option_value) {
							if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
								if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
								} else {
									$price = false;
								}

								if (is_file(DIR_IMAGE . html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'))) {
									$image = $this->model_tool_image->resize(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'), 50, 50);
								} else {
									$image = '';
								}

								$product_option_value_data[] = [
									'product_option_value_id' => $option_value['product_option_value_id'],
									'option_value_id'         => $option_value['option_value_id'],
									'name'                    => $option_value['name'],
									'image'                   => $image,
									'price'                   => $price,
									'price_prefix'            => $option_value['price_prefix']
								];
							}
						}
						$option_data[] = [
							'product_option_id'    => $option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $option['option_id'],
							'name'                 => $option['name'],
							'type'                 => $option['type'],
							'value'                => $option['value'],
							'required'             => $option['required']
						];
					}
				}
				// option data end 1-7-23

				$product_data = [
					'manufacturer'=> $product['manufacturer'],
					'manufacturer_id' => $this->url->link('product/manufacturer|info', 'manufacturer_id=' . $product['manufacturer_id']),
					'tab_review'  => $product['reviews'],
					'product_id'  => $product['product_id'],					
					'thumb'       => $image,
					'images'      => $images,
					'name'        => $product['name'],
					'description' => Helper\Utf8\substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'discount'    => $discount, // Webibazaar Custom Code 15-12-2022
					'save'        => $save, // Webibazaar Custom Code 15-12-2022
					'tax'         => $tax,
					'minimum'     => $product['minimum'] > 0 ? $product['minimum'] : 1,
					'rating'      => (int)$product['rating'],
					'options'	  => $option_data,
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
				];
				unset($images,$option_data);
				$data['products'][] = $this->load->controller('product/thumb', $product_data);
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/wdonsale/module/wdonsale', $data);
		} else {
			return '';
		}
	}
}
