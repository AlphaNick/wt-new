<?php

namespace Opencart\Catalog\Controller\Extension\Blogger\Module;
use \Opencart\System\Helper AS Helper; 
class Blogger extends \Opencart\System\Engine\Controller {
	public function index($setting) { 
	
		
		$this->load->language('extension/blogger/module/blogger');
		$this->load->model('extension/blogger/module/blogger');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/css/owl.carousel.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/css/owl.theme.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/owl.carousel.min.js');


		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_read_more'] = $this->language->get('text_read_more');
		$data['text_comment'] = $this->language->get('text_comment');
		$data['text_date_added'] = $this->language->get('text_date_added');
		$data['entry_comment'] = $this->language->get('entry_comment');

		$data['button_all_blogs'] = $this->language->get('button_all_blogs');

		$data['all_blogs'] = $this->url->link('extension/blogger/information/blogger|blogs');

		$data['blogs'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}   

		foreach ($this->model_extension_blogger_module_blogger->getBlogsByModule($setting['module_id'], $setting['limit']) as $result)
		{
			
			$total_comments = $this->model_extension_blogger_module_blogger->getTotalBlogComments($result['blogger_id']);
			$data['blogs'][] = array(
				'blogger_id'  => $result['blogger_id'], 
				'image' 	  => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']),
				'title'       => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'),
				'description' => Helper\Utf8\substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['char_limit']) . '...',
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				// 'date_added'  => date($this->language->get('date_format_shortd'), strtotime($result['date_added'])),
				// 'date_addedm'  => date($this->language->get('date_format_shortm'), strtotime($result['date_added'])),
				// 'date_addedY'  => date($this->language->get('date_format_shortY'), strtotime($result['date_added'])),
				'total_comments' => number_format($total_comments),
				'href'        => $this->url->link('extension/blogger/information/blogger', 'blogger_id=' . $result['blogger_id'])
			);
		}
		
		return $this->load->view('extension/blogger/module/blogger', $data);
	}
}
