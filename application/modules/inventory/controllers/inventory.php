<?php   if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inventory extends MX_Controller 
{
	var $csv_path;
	function __construct()
	{
		parent:: __construct();
		
		$this->load->model('auth/auth_model');
		$this->load->model('site/site_model');
		$this->load->model('admin/users_model');
		$this->load->model('admin/sections_model');
		$this->load->model('admin/admin_model');
		$this->load->model('inventory_model');
		
		if(!$this->auth_model->check_login())
		{
			redirect('login');
		}
		$this->csv_path = realpath(APPPATH . '../assets/csv');
	}
    
	/*
	*
	*	Dashboard
	*
	*/
	public function dashboard() 
	{
		$data['title'] = $this->site_model->display_page_title();
		$v_data['title'] = $data['title'];
		
		$data['content'] = $this->load->view('dashboard', $v_data, true);
		
		$this->load->view('templates/general_page', $data);
	}

	function import_template()
	{
		//export products template in excel 
		 $this->inventory_model->import_template();
	}
	function import_drugs()
	{
		//open the add new product
		$v_data['title'] = 'Import products';
		$data['title'] = 'Import products';
		$data['content'] = $this->load->view('products/import_products', $v_data, true);
		$this->load->view('admin/templates/general_page', $data);
	}
	
	function do_drugs_import()
	{
		if(isset($_FILES['import_csv']))
		{
			if(is_uploaded_file($_FILES['import_csv']['tmp_name']))
			{
				//import products from excel 
				$response = $this->inventory_model->import_csv_products($this->csv_path);
				
				if($response == FALSE)
				{
				}
				
				else
				{
					if($response['check'])
					{
						$v_data['import_response'] = $response['response'];
					}
					
					else
					{
						$v_data['import_response_error'] = $response['response'];
					}
				}
			}
			
			else
			{
				$v_data['import_response_error'] = 'Please select a file to import.';
			}
		}
		
		else
		{
			$v_data['import_response_error'] = 'Please select a file to import.';
		}
		
		//open the add new product
		$v_data['title'] = 'Import product';
		$data['title'] = 'Import product';
		$data['content'] = $this->load->view('products/import_products', $v_data, true);
		$this->load->view('admin/templates/general_page', $data);
	}
}
?>