<?php   
namespace Opencart\Catalog\Controller\Extension\Wbcategorytab\Module;
use \Opencart\System\Helper AS Helper;
class Wbcategorytab extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		$this->load->language('extension/wbcategorytab/module/wbcategorytab');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');
		
		$this->load->model('tool/image');

		$data['categories'] = array();		
		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
		if (!empty($setting['category'])) {
			//$categories = array_slice($setting['category'], 0, (int)$setting['limit']);

			$categories = $setting['category'];
			$data['template_name'] = $setting['name'];
            
			foreach ($categories as $category_id) {

				$category = $this->model_catalog_category->getCategory($category_id);

				$data['category'] = $category['name'];

				$filter_data = array(
					'filter_category_id'  => $category_id,
					'filter_sub_category' => true,
					'limit'               => (int) $setting['limit'],
					'start'               => 0
				);	
							
				$category_info = $this->model_catalog_product->getProducts($filter_data);

				if ($category_info) {
					$data['products'] = array(); // сбрасываем datainfo['products'] чтобы не было дубликата
					foreach ($category_info as $key => $value) {
 						// Webibazaar Custom 15-12-2022
		 $images = [];
		 if ($value['image']) {					
			 $image = $this->model_tool_image->resize(html_entity_decode($value['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']);
			 $images[] = [
				 'additional' => $this->model_tool_image->resize(html_entity_decode($value['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
				 'thumb' => $this->model_tool_image->resize(html_entity_decode($value['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
			 ];
		 } else {
			 $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
			 $images[] = [
				 'additional' => $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']),
				 'thumb' => $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
			 ];
		 }
		 $image_values = $this->model_catalog_product->getImages($value['product_id']);

		 foreach ($image_values as $image_value) {
			 if (is_file(DIR_IMAGE . html_entity_decode($image_value['image'], ENT_QUOTES, 'UTF-8'))) {
				 $images[] = [
					 'additional' => $this->model_tool_image->resize(html_entity_decode($image_value['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height']),
					 'thumb' => $this->model_tool_image->resize(html_entity_decode($image_value['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
				 ];
				 break;
			 }
		 }
		 // workdo Custom end 
		 

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($value['price'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$value['special']) {
							$special = $this->currency->format($this->tax->calculate($value['special'], $value['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							// workdo Custom Code 15-12-2022
							$after_tex_special = $this->tax->calculate($value['special'], $value['tax_class_id'], $this->config->get('config_tax'));
							$after_tex_price = $this->tax->calculate($value['price'], $value['tax_class_id'], $this->config->get('config_tax'));
							$discount = round((($after_tex_price - $after_tex_special) * 100) / $after_tex_price);
					$save = $this->currency->format($after_tex_price - $after_tex_special, $this->session->data['currency']);
					// End
						} else {
							$special = false;
							$discount = false; // Webibazaar Custom Code 15-12-2022
							$save = false; // Webibazaar Custom Code 15-12-2022
						}		
						
					// option data start  01-07-23
					$product_options = $this->model_catalog_product->getOptions((int)$value['product_id']);
					//print_r($product_options);
					$option_data = [];
					foreach ($product_options as $option) {
						if ((int)$value['product_id'] && !isset($product['override']['variant'][$option['product_option_id']])) {
							$product_option_value_data = [];
							//echo $value['product_id']."Hello"."<br>";
							foreach ($option['product_option_value'] as $option_value) {
								if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
									if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
									$price = $this->currency->format($this->tax->calculate($option_value['price'], $value['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
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
							'manufacturer'=> $value['manufacturer'],
							'product_id'  => $value['product_id'],
							'tab_review'  => $value['reviews'],
							'thumb'       => $image,
							'images'      => $images,
							'name'        => $value['name'],
							'description' => Helper\Utf8\substr(strip_tags(html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8')), 0, 80) . '..',
							'price'       => $price,
							'special'     => $special,
							'rating'      => $value['rating'],
							'discount'    => $discount, // Webibazaar Custom Code 15-12-2022
							'save'        => $save, // Webibazaar Custom Code 15-12-2022
							'options'	  => $option_data,
							'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $value['product_id'])

						];
						unset($images,$option_data);
						$data['products'][] = $this->load->controller('product/thumb', $product_data);
					}
				}
				$data['categories'][] = $data;
			}
		}

		if ($data['categories']) {
			return $this->load->view('extension/wbcategorytab/module/wbcategorytab', $data);
		}
	}
}