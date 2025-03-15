<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
use \Opencart\System\Helper as Helper;
class BestSeller extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/bestseller');

		$this->load->model('extension/opencart/module/bestseller');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['axis'] = $setting['axis'];

		$data['products'] = [];

		$results = $this->model_extension_opencart_module_bestseller->getBestSeller($setting['limit']);

		if ($results) {
			foreach ($results as $result) {
				// Webibazaar Custom 15-12-2022
				$images = [];
				if ($result['image']) {					
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
					$images[] = [
						'additional' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
						'thumb' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					$images[] = [
						'additional' => $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']),
						'thumb' => $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				}
				$image_results = $this->model_catalog_product->getImages($result['product_id']);

				foreach ($image_results as $image_result) {
					if (is_file(DIR_IMAGE . html_entity_decode($image_result['image'], ENT_QUOTES, 'UTF-8'))) {
						$images[] = [
							'additional' => $this->model_tool_image->resize(html_entity_decode($image_result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
							'thumb' => $this->model_tool_image->resize(html_entity_decode($image_result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
						];
						break;
					}
				}
				// Webibazaar Custom end 

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					// Webibazaar Custom Code 15-12-2022
					$after_tex_special = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					$after_tex_price = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					$discount = round((($after_tex_price - $after_tex_special) * 100) / $after_tex_price);
					$save = $this->currency->format($after_tex_price - $after_tex_special, $this->session->data['currency']);
					// End
				} else {
					$special = false;
					$discount = false; // Webibazaar Custom Code 15-12-2022
					$save = false; // Webibazaar Custom Code 15-12-2022
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
				// option data start  01-07-23
				$product_options = $this->model_catalog_product->getOptions((int)$result['product_id']);
				//print_r($product_options);
				$option_data = [];
				foreach ($product_options as $option) {
					if ((int)$result['product_id'] && !isset($result['override']['variant'][$option['product_option_id']])) {
						$product_option_value_data = [];
						//echo $result['product_id']."Hello"."<br>";
						foreach ($option['product_option_value'] as $option_value) {
							if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
								if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$price = $this->currency->format($this->tax->calculate($option_value['price'], $result['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
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
					'manufacturer'=> $result['manufacturer'],
					'manufacturer_id' => $this->url->link('product/manufacturer|info', 'manufacturer_id=' . $result['manufacturer_id']),
					'tab_review'  => $result['reviews'],
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'images'      => $images,
					'name'        => $result['name'],
					'description' => Helper\Utf8\substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'discount'    => $discount, // Webibazaar Custom Code 15-12-2022
					'save'        => $save, // Webibazaar Custom Code 15-12-2022
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'options'	  => $option_data,
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
				];
				unset($images,$option_data);
				$data['products'][] = $this->load->controller('product/thumb', $product_data);
			}

            return $this->load->view('extension/opencart/module/bestseller', $data);
		} else {
			return '';
		}
	}
}
