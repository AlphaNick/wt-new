<?php
class ControllerExtensionWinterProductData extends Controller {
    // public function index() {
    //     $this->load->model('catalog/product');
    // }
    public function option_price() {
        $json = array();
  
        if (isset($this->request->post['product_id'])) {
            $product_id = (int)$this->request->post['product_id'];
            $json['product_id'] = $product_id;
            $this->load->model('catalog/product');
            $option_ids = 0;
            if (isset($this->request->post['option'])) {
                $options = array_filter($this->request->post['option']);
                foreach($options as $option) {
                    if(is_array($option)) {
                        foreach($option as $opt) {
                            $option_ids .= ",".$opt;
                        }
                    }
                    else {
                        $option_ids .= ",".$option;
                    }
                }
            }
            else {
                $option = array();
            }

            $product_info = $this->model_catalog_product->getProduct($product_id);
            $json['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],null,false);
            
            if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
                $json['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'],null,false);
                $tax_price = (float)$product_info['special'];
                $json['discount'] = (($json['price'] - $json['special']) * 100);
            } else {    
                $json['special'] = false;
                $tax_price = (float)$product_info['price'];
            }

            if ($this->config->get('config_tax')) {
                $json['without_tax'] = $this->currency->format($tax_price, $this->session->data['currency'],null,false);
            } else {
                $json['without_tax'] = false;
            }
            $option_ids = trim($option_ids,",");

            if($option_ids) {
                $option_values_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id IN (".$option_ids.")");
                foreach ($option_values_query->rows as $option_value) {
                    $option_price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency'],null,false);
                    $option_price_without_tax = $this->currency->format($option_value['price'], $this->session->data['currency'],null,false);
                    
                    if($option_value['price_prefix'] == "+") {
                        $json['special'] = $json['special'] ? $json['special'] += $option_price : false;
                        $json['price'] += $option_price;
                        $json['without_tax'] += $option_price_without_tax;
                    }
                    elseif($option_value['price_prefix'] == "-") {
                        $json['special'] = $json['special'] ? $json['special'] -= $option_price : false;
                        $json['price'] -= $option_price;
                        $json['without_tax'] -= $option_price_without_tax;
                    }   
                }                
            }
            $json['discount'] = $json['special'] ? "-".round(($json['price'] - $json['special']) * 100 / $json['price'])."%" : false;
            $json['price'] = number_format($this->currency->format($json['price'],$this->session->data['currency'],null,false),2);
            $json['without_tax'] = $this->currency->format($json['without_tax'],$this->session->data['currency']);
            $json['special'] = $json['special'] ? number_format($this->currency->format($json['special'],$this->session->data['currency'],null,false),2) : false;
            //$json['currency_symbols'] = $this->session->data['currency']; 
            //$json['raw_special'] = $json['special'] ? $this->currency->format($json['special'],$this->session->data['currency']) : false;
            // $json['option_price'] = $option_price;
            // $json['option_price_without_tax'] = $option_price_without_tax;
            
        } else {
            $json['error'] = "null";
        }    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>