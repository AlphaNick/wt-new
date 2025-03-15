<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Module;
use \Opencart\System\Helper AS Helper;
class Latest extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/opencart/module/latest');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['products'] = [];

		$results = $this->model_catalog_product->getLatest($setting['limit']);

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
				//$results = $this->model_catalog_product->getImages($product['product_id']);

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
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $result['product_id'])
				];
				unset($images);
				$data['products'][] = $this->load->controller('product/thumb', $product_data);
			}

			return $this->load->view('extension/opencart/module/latest', $data);
		} else {
			return '';
		}
	}
}
