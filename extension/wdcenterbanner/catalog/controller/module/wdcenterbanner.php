<?php
namespace Opencart\Catalog\Controller\Extension\Wdcenterbanner\Module;
class Wdcenterbanner extends \Opencart\System\Engine\Controller {
	public function index(array $setting): string {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$data['banners'] = [];

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['banners'][] = [
					'description' => html_entity_decode($result['description'],  ENT_QUOTES, 'UTF-8'),
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize(html_entity_decode($result['image'], ENT_QUOTES, 'UTF-8'), $setting['width'], $setting['height'])
				];
			}
		}

		if ($data['banners']) {
			$data['module'] = $module++;

			$data['effect'] = $setting['effect'];
			$data['controls'] = $setting['controls'];
			$data['indicators'] = $setting['indicators'];
			$data['items'] = $setting['items'];
			$data['interval'] = $setting['interval'];
			$data['width'] = $setting['width'];
			$data['height'] = $setting['height'];

			return $this->load->view('extension/wdcenterbanner/module/wdcenterbanner', $data);
		} else {
			return '';
		}
	}
}
