<?php
namespace Opencart\Admin\Controller\Extension\Wbcountdown\Module;
use \Opencart\System\Helper AS Helper;
class wbcountdown extends \Opencart\System\Engine\Controller {
	private $error = array();


	public function index() {
		$this->load->language('extension/wbcountdown/module/wbcountdown');

		$this->document->setTitle($this->language->get('page_title'));		

		$this->load->model('setting/module');
		
		$this->load->model('tool/image');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('wbcountdown.wbcountdown', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
		$this->load->model('localisation/language');
		
		$data['languages'] = array();
		
		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language){
			if ($language['status']) {
				$data['languages'][] = array(
					'name'  => $language['name'],
					'language_id' => $language['language_id'],
					'code' => $language['code']
				);
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->request->post['class'])) {
			$data['class'] = $this->request->post['class'];
		} elseif (!empty($module_info)) {
			$data['class'] = $module_info['class'];
		} else {
			$data['class'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}
		if (isset($this->error['category'])) {
			$data['error_category'] = $this->error['category'];
		} else {
			$data['error_category'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/wbcountdown', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/wbcountdown', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/wbcountdown/module/wbcountdown', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/wbcountdown/module/wbcountdown', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		

		// All /category / auto proudtcs
		if (isset($this->request->post['option'])) {
			$data['option'] = $this->request->post['option'];
		} elseif (!empty($module_info)) {
			$data['option'] = $module_info['option'];
		} else {
			$data['option'] = '';
		}


		//Show category
		$this->load->model('catalog/category');
		$data['cate_ids'] = array();
		
		if (isset($this->request->post['cate_id'])) {
			$cate_id = $this->request->post['cate_id'];
		} elseif (!empty($module_info)) {
			$cate_id = $module_info['cate_id'];
		} else {
			$cate_id = 20; 
		}

		if (isset($this->request->post['cate_name'])) {
			$cate_name = $this->request->post['cate_name'];
		} elseif (!empty($module_info)) {
			$cate_name = $module_info['cate_name'];
		} else {
			$cate_name = 20;
		}

		//echo $cate_id; die;
		$category_info = $this->model_catalog_category->getCategory($cate_id);
		if ($category_info) {
		 $data['cate_ids'] = array(
			 'category_id' => $category_info['category_id'],
			 'name'       => $category_info['name']
		 );
		}

		// All products or select products
		if (isset($this->request->post['productfrom'])) {
			$data['productfrom'] = $this->request->post['productfrom'];
		} elseif (!empty($module_info)) {
			$data['productfrom'] = $module_info['productfrom'];
		} else {
			$data['productfrom'] = '';
		}
		
		if (isset($this->request->post['input_specific_product'])) {
			$data['input_specific_product'] = $this->request->post['input_specific_product'];
		} elseif (!empty($module_info)) {
			$data['input_specific_product'] = $module_info['input_specific_product'];
		} else {
			$data['input_specific_product'] = '';
		}

		// new / special / bestsale / most view
		if (isset($this->request->post['autoproduct'])) {
			$data['autoproduct'] = $this->request->post['autoproduct'];
		} elseif (!empty($module_info)) {
			$data['autoproduct'] = $module_info['autoproduct'];
		} else {
			$data['autoproduct'] = '';
		}
		
		$this->load->model('catalog/product');

		$data['products'] = array();

		if (isset($this->request->post['product'])) {
			$products = $this->request->post['product'];
		} elseif (!empty($module_info)) {
			$products = $module_info['product'];
		} else {
			$products = array();
		}

		if (is_array($products)){
			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);
				$image = $this->model_tool_image->resize($product_info['image'],40,40);
				if ($product_info) {
					$data['products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name'],
						'image'      => $image
					);
				}
			}
		}

		$data['productcates'] = array();

		if (isset($this->request->post['productcate'])) {
			$productcates = $this->request->post['productcate'];
		} elseif (!empty($module_info)) {
			$productcates = $module_info['productcate'];
		} else {
			$productcates = array();
		}

		if (is_array($productcates)){
			foreach ($productcates as $productcate) {
				$product_info = $this->model_catalog_product->getProduct($productcate);
				//echo '<pre>'; print_r($product_info); die;
				$image = $this->model_tool_image->resize($product_info['image'],40,40);
				if ($product_info) {
					$data['productcates'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => $product_info['name'],
						'image'      => $image
					);
				}
			}
		}


		if (isset($this->request->post['countdown'])) {
			$data['countdown'] = $this->request->post['countdown'];
		} elseif (!empty($module_info)) {
			$data['countdown'] = $module_info['countdown'];
		} else {
			$data['countdown'] = 0;
		}

		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = 12;
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		
		$this->document->addStyle('view/stylesheet/tt_admin.css');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/wbcountdown/module/wbcountdown', $data));
	}
	
	
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);
					if ($option_info) {
						$product_option_value_data = array();
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_product->getProductOptionValue($result['product_id'],$product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix'],
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$this->load->model('tool/image');
				$image = $this->model_tool_image->resize($result['image'],40,40);
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price'],
					'image'      => $image,
				);
			}
		}
         
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getProductCategory() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');
			//$this->load->model('ttmodules/product');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['category_id'])) {
				$category_id = $this->request->get['category_id'];
			} else {
				$category_id = '';
			}

			if(!$category_id) return;

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);
					if ($option_info) {
						$product_option_value_data = array();
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_product->getProductOptionValue($result['product_id'],$product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix'],
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$this->load->model('tool/image');
				$image = $this->model_tool_image->resize($result['image'],40,40);
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price'],
					'image'      => $image,
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocompleteCategory() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validate() { 
		if (!$this->user->hasPermission('modify', 'extension/wbcountdown/module/wbcountdown')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((Helper\Utf8\strlen($this->request->post['name']) < 3) || (Helper\Utf8\strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}

		if ($this->request->post['option'] == 1 && $this->request->post['cate_name'] == '') {
			$this->error['category'] = $this->language->get('error_height');
		}

		return !$this->error;
	}
}