<?php
namespace Opencart\Catalog\Controller\Extension\Wbcountdown\Module;
use \Opencart\System\Helper AS Helper;
class Wbcountdown extends \Opencart\System\Engine\Controller {
	public function index($setting) {
		
	 
		$this->load->language('extension/wbcountdown/module/wbcountdown');
		
		$this->load->model('catalog/product');
		$this->load->model('extension/wbcountdown/module/wbcountdown');

		$this->load->model('tool/image');

		$data['products'] = array();

		$data['special'] = $this->url->link('product/special');

		$this->load->model('localisation/language');
		
		//$data['code'] = $this->session->data['language'];

		$store_id = $this->config->get('config_store_id');
		
		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if($setting['option'] == 0) {
			if (!empty($setting['product'])) {
				$results = array();
				$products = array_slice($setting['product'], 0, (int)$setting['limit']);
				foreach ($products as $product_id) {
					$results[] = $this->model_catalog_product->getProduct($product_id);
				}
			}
			
		} else if ($setting['option']==1){
			if($setting['productfrom']==1){
				$data['filter_category_id'] = $setting['cate_id'];
				$results = $this->model_catalog_product->getProducts($data);
				
			} else if($setting['productfrom']==0) {
				if (!empty($setting['productcate'])) {
					$products = array_slice($setting['productcate'], 0, (int)$setting['limit']);
					foreach ($products as $product_id) {
						$results[] = $this->model_catalog_product->getProduct($product_id);
					}
				}			
			} else {
				if ($setting['input_specific_product']==0){
					$data['products'] = array();
					$filter_data = array(
						'filter_category_id' => $setting['cate_id'],
						'sort'  => 'p.date_added',
						'order' => 'DESC',
						'start' => 0,
						'limit' => $setting['limit'],
					);
					$results = $this->model_catalog_product->getProducts($filter_data);
						
				} else if ($setting['input_specific_product']==1){
					$filter_data = array(
					'sort'  => 'pd.name',
					'order' => 'ASC',
					'start' => 0,
					'limit' => $setting['limit']
					);
					$results = $this->model_extension_wbcountdown_module_wbcountdown->getProductSpecialsCategory($filter_data, $setting['cate_id']);		
				} else if ($setting['input_specific_product']==2){
					$data['products'] = array();
					$results = $this->model_extension_wbcountdown_module_wbcountdown->getBestSellerProductsCategory($setting['limit'], $setting['cate_id']);				
				} else{
					$data['products'] = array();
					$results = $this->model_extension_wbcountdown_module_wbcountdown->getMostViewedProductsCategory($setting['limit'],  $setting['cate_id']);		
				}
			}
	
		} else {

			if ($setting['autoproduct']==0){
				$data['products'] = array();

				$filter_data = array(
					'sort'  => 'p.date_added',
					'order' => 'DESC',
					'start' => 0,
					'limit' => $setting['limit']
				);
				$results = $this->model_catalog_product->getProducts($filter_data);
					
			} else if ($setting['autoproduct']==1){
				$filter_data = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => $setting['limit']
				);

				$results = $this->model_catalog_product->getProductSpecials($filter_data);
					
			} else if ($setting['autoproduct']==2){
					$data['products'] = array();

				$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
				
			} else if ($setting['autoproduct']==3){
					$data['products'] = array();

				$results = $this->model_catalog_product->getPopularProducts($setting['limit']);
				
			} else{
				$data['products'] = array();

				$results = $this->model_extension_wbcountdown_module_wbcountdown->getDealProducts($setting['limit']);		
				
			}		
		}

		$data['use_quickview'] = (int) $this->config->get('module_octhemeoption_quickview');
		$data['use_catalog'] = (int) $this->config->get('module_octhemeoption_catalog');

		$product_rotator_status = (int) $this->config->get('module_octhemeoption_rotator');

		/* Get new product */
		$this->load->model('catalog/product');

		$filter_data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 10
		);

		$new_results = $this->model_catalog_product->getProducts($filter_data);
		/* End */

		if ($results) {
			foreach ($results as $result) {
				$text_percent = '';
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$price_num = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$price = false;
					$price_num= false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$special_num = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					// Webibazaar Custom Code 15-12-2022
					$after_tex_special = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					$after_tex_price = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					$discount = round((($after_tex_price - $after_tex_special) * 100) / $after_tex_price);
					$save = $this->currency->format($after_tex_price - $after_tex_special, $this->session->data['currency']);
					// End
				} else {
					$special = false;
					$special_num = false;
					$discount = false; // Webibazaar Custom Code 15-12-2022
					$save = false; // Webibazaar Custom Code 15-12-2022
				}
				
				if ($special_num && $price_num) {
					$percent =  round(($price_num-$special_num)/$price_num*100,0);
					$text_percent =  sprintf($this->language->get('text_label_sale'), $percent);
					
				}
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}
				$date_end = false;
				if ($setting['countdown']){
					$date_end = $this->model_extension_wbcountdown_module_wbcountdown->getSpecialwbcountdown($result['product_id']);
					if ($date_end === '0000-00-00') {
						$date_end = false;
					}
				}
				/* Product Rotator */
				if($product_rotator_status == 1) {
				  $this->load->model('catalog/wbcountdownrotator');
				  $this->load->model('tool/image');

				  $product_id = $result['product_id'];
				  $product_rotator_image = $this->model_catalog_wbcountdownrotator->getProductRotatorImage($product_id);

				  if($product_rotator_image) {
				    $rotator_image_width = $setting['width'];
				    $rotator_image_height = $setting['height'];
				    $data['rotator_image'] = $this->model_tool_image->resize($product_rotator_image, $rotator_image_width, $rotator_image_height);  
				  } else {
				    $data['rotator_image'] = false;
				  } 
				} else {
				  $data['rotator_image'] = false;       
				}
				/* End Product Rotator */

				$is_new = false;
				if ($new_results) { 
					foreach($new_results as $new_r) {
						if($result['product_id'] == $new_r['product_id']) {
							$is_new = true;
						}
					}
				}
				
				$c_words = 50;
				$result['name'] = strlen($result['name']) > $c_words ? substr($result['name'],0,$c_words)."..." : $result['name'];
				
				$data['products'][] = array(
					'manufacturer'=> $result['manufacturer'],
					'manufacturer_id' => $this->url->link('product/manufacturer|info', 'manufacturer_id=' . $result['manufacturer_id']),
					'tab_review'  => $result['reviews'],
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => Helper\Utf8\substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 80) . '..',
					'price'       => $price,
					'special'     => $special,
					'discount'    => $discount, // Webibazaar Custom Code 15-12-2022
					'save'        => $save, // Webibazaar Custom Code 15-12-2022
					'text_percent'  => $text_percent,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					'date_end'    => $date_end,
					'is_new'      => $is_new,
					'rotator_image' => $data['rotator_image'],
					'manufacturer' => $result['manufacturer'],
					'manufacturers' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
				);
			}
		}
		$number_random = rand ( 1 , 1000 );
		$data['config_module'] = array(
				'name' => $setting['name'],
				'wbcountdown' => (int) $setting['countdown'],
				'module_id' => $number_random
			);
			if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
				$data['module_description'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
				if ($data['module_description'] == '<p><br><p>') $data['module_description']= '';
		   }
		    $data['cart'] = $this->url->link('common/cart|info', 'language=' . $this->config->get('config_language'));

		$data['add_to_cart'] = $this->url->link('checkout/cart|add', 'language=' . $this->config->get('config_language'));
		$data['add_to_wishlist'] = $this->url->link('account/wishlist|add', 'language=' . $this->config->get('config_language'));
		$data['add_to_compare'] = $this->url->link('product/compare|add', 'language=' . $this->config->get('config_language'));
			//echo '<pre>'; print_r($data['config_module']); die;

		if ($data['products']) {
			return $this->load->view('extension/wbcountdown/module/wbcountdown', $data);
		}
		
	}
	
	
}