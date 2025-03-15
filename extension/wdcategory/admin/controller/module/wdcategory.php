<?php
namespace Opencart\Admin\Controller\Extension\Wdcategory\Module;
use \Opencart\System\Helper AS Helper;

class Wdcategory extends \Opencart\System\Engine\Controller {
	private $error = array();
	private string $extensionPath = 'extension/wdcategory/module/wdcategory';
	
		public function index() {
		$this->load->language($this->extensionPath);

		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/information');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('wdcategory.wdcategory', $this->request->post);
			} else {
				
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
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
				'href' => $this->url->link('extension/module/wdcategory', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/wdcategory', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/wdcategory/module/wdcategory', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/wdcategory/module/wdcategory', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info)) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = array();
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

		if (isset($this->request->post['items'])) {
			$items = $this->request->post['items'];
		} elseif (!empty($module_info)) {
			$items = $module_info['items'];
		} else {
			$items = array();
		}

		$data['items'] = array();
        
		foreach ((array)$items as $item) {
			$link = $item['link'];

			$link_name = '';

			if ($item['type'] == 'category') {
				$category_info = $this->model_catalog_category->getCategory($link);

				if ($category_info) {
					$link_name = $category_info['name'];
				}
			} elseif ($item['type'] == 'product') {
				$product_info = $this->model_catalog_product->getProduct($link);

				if ($product_info) {
					$link_name = $product_info['name'];
				}
			} elseif ($item['type'] == 'manufacturer') {
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($link);

				if ($manufacturer_info) {
					$link_name = $manufacturer_info['name'];
				}
			} elseif ($item['type'] == 'information') {
				$information_info = $this->model_catalog_information->getInformationDescriptions($link);

				if ($information_info) {
					$link_name = $information_info[$this->config->get('config_language_id')]['title'];
				}
			}

			if (is_file(DIR_IMAGE . $item['image'])) {
				$thumb = $this->model_tool_image->resize($item['image'], 100, 100);
			} else {
				$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
			}

			$data['items'][] = array(
				'description'	=> $item['description'],
				'type'			=> $item['type'],
				'link'			=> $link,
				'link_name'		=> $link_name,
				'thumb'			=> $thumb,
				'image'			=> $item['image'],
				'sort_order'	=> $item['sort_order'],
				'status'		=> !empty($item['status'])
			);
		}



		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$sort_order = array();

		foreach ($data['items'] as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $data['items']);

		$this->load->model('localisation/language');

		$json = [];

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->extensionPath, $data));
	}

	
			protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/opencart/module/account')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if ((Helper\Utf8\strlen($this->request->post['name']) < 3) || (Helper\Utf8\strlen($this->request->post['name']) > 64)) {
			$json['error']['name'] = $this->language->get('error_name');
		}


		foreach ($this->request->post['title'] as $language_id => $value) {
			if ((Helper\Utf8\strlen($value) < 3) || (Helper\Utf8\strlen($value) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_section_name');
			}
		}

		if (!$this->request->post['width']) {
			$json['error']['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$json['error']
			['height'] = $this->language->get('error_height');
		}

		return !$this->error;
	}

	public function install(): void {
		// Add extension permissions
		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', $this->extensionPath);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', $this->extensionPath);
	}

	public function uninstall(): void {
		// Remove extension permissions
		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', $this->extensionPath);
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', $this->extensionPath);
	}
}