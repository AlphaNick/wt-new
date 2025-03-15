<?php
namespace Opencart\Catalog\Controller\Product;
use \Opencart\System\Helper as Helper;
class Category extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit']) && (int)$this->request->get['limit']) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_pagination');
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = [
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $path . $url)
					];
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = [
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'])
			];

			if (is_file(DIR_IMAGE . html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['thumb'] = $this->model_tool_image->resize(html_entity_decode($category_info['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare', 'language=' . $this->config->get('config_language'));

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = [];

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = [
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				];

				$data['categories'][] = [
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				];
			}

			$data['products'] = [];

			$filter_data = [
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $this->config->get('config_product_category') ? true : false,
				'filter_filter'       => $filter,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			];

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				// Webibazaar Custom 15-12-2022
				$images = [];
				if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					$images[] = [
						'additional' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
						'thumb' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];

				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
					$images[] = [
						'additional' => $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']),
						'thumb' => $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height'))
					];
				}
				$image_results = $this->model_catalog_product->getImages($result['product_id']);
				foreach ($image_results as $image_result) {
					if (is_file(DIR_IMAGE . html_entity_decode($image_result['image'], ENT_QUOTES, 'UTF-8'))) {
						$images[] = [
							'additional' => $this->model_tool_image->resize(html_entity_decode($image_result['image'], ENT_QUOTES, 'UTF-8'),  $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
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
					'href'        => $this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)
				];
				unset($images,$option_data);
				$data['products'][] = $this->load->controller('product/thumb', $product_data);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = [];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			];

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = [
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				];

				$data['sorts'][] = [
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				];
			}

			$data['sorts'][] = [
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			];

			$data['sorts'][] = [
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			];

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = [];

			$limits = array_unique([$this->config->get('config_pagination'), 25, 50, 75, 100]);

			sort($limits);

			foreach ($limits as $value) {
				$data['limits'][] = [
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				];
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['pagination'] = $this->load->controller('common/pagination', [
				'total' => $product_total,
				'page'  => $page,
				'limit' => $limit,
				'url'   => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . $url . '&page={page}')
			]);

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&page='. $page), 'canonical');
			}

			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'language=' . $this->config->get('config_language') . '&path=' . $this->request->get['path'] . '&page='. ($page + 1)), 'next');
			}

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', 'language=' . $this->config->get('config_language') . $url)
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home', 'language=' . $this->config->get('config_language'));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
