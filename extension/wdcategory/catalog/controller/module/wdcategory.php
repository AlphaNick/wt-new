<?php 
namespace Opencart\Catalog\Controller\Extension\Wdcategory\Module;
class Wdcategory extends \Opencart\System\Engine\Controller  {
	public function index($setting) {
		
		$this->load->language('extension/opencart/module/wdcategory');

		$this->load->model('tool/image');

		$data['items'] = array();

		$data['special'] = $this->url->link('product/special');

		if ($setting['items']) {
			$language_id = $this->config->get('config_language_id');

			if (!empty($setting['title'][$language_id])) {
				$data['heading_title'] = $setting['title'][$language_id];
			}

            
			foreach ($setting['items'] as $result) {

				if (isset($result['description'][$language_id])) {
					$title 		= $result['description'][$language_id]['title'];
					$subtitle	= $result['description'][$language_id]['subtitle'];
				} else {
					$title		= '';
					$subtitle	= '';
				}
				

				if (!empty($result)) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					$link = $result['link'];
                    
					if ($result['type'] == 'category') {
						$link = $this->url->link('product/category', 'path=' . $link);
					} elseif ($result['type'] == 'product') {
						$link = $this->url->link('product/product', 'product_id=' . $link);
					} elseif ($result['type'] == 'manufacturer') {
						$link = $this->url->link('product/manufacturer/info', 'manufacturer_id=' .  $link);
					} elseif ($result['type'] == 'information') {
						$link = $this->url->link('information/information', 'information_id=' . $link);
					}

					$data['items'][] = array(
						'title'			=> $title,
						// 'totalp'	    => $this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data) : '',
						'subtitle'		=> $subtitle,
						'thumb'			=> $image,
						'href'			=> $link,
						'sort_order'	=> $result['sort_order']
					);
				}
			}

			$sort_order = array();

			foreach ($data['items'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $data['items']);

			return $this->load->view('extension/wdcategory/module/wdcategory', $data);
		}
	}
}
