<?php
namespace Opencart\Catalog\Controller\Common;
class Menu extends \Opencart\System\Engine\Controller {
	public function index(): string {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = [];

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = [];

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					// Level 3 
                        $children_data_3 = [];

                        $children_3 = $this->model_catalog_category->getCategories($child['category_id']);

                        foreach ($children_3 as $child_3) {

                            $filter_data_3 = [
                                'filter_category_id'  => $child_3['category_id'],
                                'filter_sub_category' => true
                            ];

                            $children_data_3[] = [
                                'name'  => $child_3['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data_3) . ')' : ''),
                                'href'  => $this->url->link('product/category', 'path=' . $child['category_id'] . '_' . $child_3['category_id']),
                            ];
                        }
                    //end of level 3
					$filter_data = [
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					];

					$children_data[] = [
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'] . '_' . $child['category_id']),
						'grand_childs' => $children_data_3//for level 3
					];
				}

				//Webibazaar Custom Code 15-12-2022 Ex: (W X H) = 40, 40 
				$this->load->model('tool/image');
				if (is_file(DIR_IMAGE . html_entity_decode($category['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($category['image'], ENT_QUOTES, 'UTF-8'), 14, 14);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 14, 14);
				}
				// End

				// Level 1
				$data['categories'][] = [
					'name'     => $category['name'],
					'image'    => $image, //Webibazaar Custom Code 15-12-2022
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $category['category_id'])
				];
			}
		}

		return $this->load->view('common/menu', $data);
	}
}
