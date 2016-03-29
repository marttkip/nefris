<?php

class Inventory_model extends CI_Model 
{
	/*
	*	Validate a personnel's login request
	*
	*/
	public function validate_personnel()
	{
		//select the personnel by username from the database
		$this->db->select('*');
		$this->db->where(array('personnel_username' => $this->input->post('personnel_username'), 'personnel_status' => 1, 'personnel_password' => md5($this->input->post('personnel_password'))));
		$query = $this->db->get('personnel');
		
		//if personnel exists
		if ($query->num_rows() > 0)
		{
			$result = $query->result();
			//create personnel's login session
			$newdata = array(
                   'login_status'     => TRUE,
                   'first_name'     => $result[0]->personnel_fname,
                   'username'     => $result[0]->personnel_username,
                   'personnel_id'  => $result[0]->personnel_id
               );

			$this->session->set_userdata($newdata);
			
			//update personnel's last login date time
			$this->update_personnel_login($result[0]->personnel_id);
			return TRUE;
		}
		
		//if personnel doesn't exist
		else
		{
			return FALSE;
		}
	}
	
	/*
	*	Update personnel's last login date
	*
	*/
	private function update_personnel_login($personnel_id)
	{
		$data['last_login'] = date('Y-m-d H:i:s');
		$this->db->where('personnel_id', $personnel_id);
		$this->db->update('personnel', $data); 
	}
	
	/*
	*	Reset a personnel's password
	*
	*/
	public function reset_password($personnel_id)
	{
		$new_password = substr(md5(date('Y-m-d H:i:s')), 0, 6);
		
		$data['personnel_password'] = md5($new_password);
		$this->db->where('personnel_id', $personnel_id);
		$this->db->update('personnel', $data); 
		
		return $new_password;
	}
	
	/*
	*	Check if a has logged in
	*
	*/
	public function check_login()
	{
		if($this->session->userdata('login_status'))
		{
			return TRUE;
		}
		
		else
		{
			return FALSE;
		}
	}


	/*
	*	Import Template
	*
	*/
	function import_template()
	{
		$this->load->library('Excel');
		
		$title = 'Nefris Product Import Template';
		$count=1;
		$row_count=0;
		
		$report[$row_count][0] = 'Product Name';
		$report[$row_count][1] = 'Brand Name (For Only Drugs)';
		$report[$row_count][2] = 'Drug Type (i.e Tablet) (For Only Drugs)';
		$report[$row_count][3] = 'Unit Cost';
		$report[$row_count][4] = 'Dose Unit (For Only Drugs)';
		$report[$row_count][5] = 'Admin Route (For Only Drugs)';
		$report[$row_count][6] = 'Product Category (either 1 or 2)';
		
		$row_count++;
		
		//create the excel document
		$this->excel->addArray ( $report );
		$this->excel->generateXML ($title);
	}
	public function import_csv_products($upload_path)
	{
		//load the file model
		$this->load->model('admin/file_model');
		/*
			-----------------------------------------------------------------------------------------
			Upload csv
			-----------------------------------------------------------------------------------------
		*/
		$response = $this->file_model->upload_csv($upload_path, 'import_csv');
		
		if($response['check'])
		{
			$file_name = $response['file_name'];
			
			$array = $this->file_model->get_array_from_csv($upload_path.'/'.$file_name);
			//var_dump($array); die();
			$response2 = $this->sort_csv_data($array);
		
			if($this->file_model->delete_file($upload_path."\\".$file_name, $upload_path))
			{
			}
			
			return $response2;
		}
		
		else
		{
			$this->session->set_userdata('error_message', $response['error']);
			return FALSE;
		}
	}
	public function check_if_code_exisits($code)
	{
		$this->db->where('branch_code = "'.$this->session->userdata('branch_code').'" AND drug_code = "'.$code.'"');
		
		$query = $this->db->get('drugs');
		
		if($query->num_rows() > 0)
		{
			return TRUE;
		}
		
		else
		{
			return FALSE;
		}

	}
	public function sort_csv_data($array)
	{
		//count total rows
		$total_rows = count($array);
		$total_columns = count($array[0]);//var_dump($array);die();
		
		//if products exist in array
		if(($total_rows > 0) && ($total_columns == 7))
		{
			$response = '
				<table class="table table-hover table-bordered ">
					  <thead>
						<tr>
						  <th>#</th>
						  <th>Product Name</th>
						  <th>Product Price</th>
						  <th>Mark up Price</th>
						</tr>
					  </thead>
					  <tbody>
			';
			
			//retrieve the data from array
			for($r = 1; $r < $total_rows; $r++)
			{
				$product_name_first = $array[$r][0];
				$items['product_unitprice'] = $array[$r][3];
				$items['product_unitprice_insurance'] = $items['product_unitprice'] * 1.5;
				$items['store_id'] = 1;
				$items['quantity'] = 0;
				$items['category_id'] = $array[$r][6];;
				$items['product_status'] = 1;
				$items['branch_code'] = $this->session->userdata('branch_code');
				$items['product_name'] = $product_name_first;
				$items['is_synced'] = 0;
				// check drug type if exist

				//$items['generic_id'] = $this->get_generic_id($generic_name);
				//$items['brand_id'] = $this->get_brand_id($brand_name);
				$comment = '';
				
				
				if(!empty($items['product_name']))
				{
						if($this->db->insert('product', $items))
						{
							//calculate the price of the drug
							$product_id = $this->db->insert_id();
							// insert into store product
							$comment .= '<br/>Product successfully added to the database';
							$class = 'success';
						}
						
						else
						{
							$comment .= '<br/>Internal error. Could not add patient to the database. Please contact the site administrator. Product code '.$items['product_name'];
							$class = 'warning';
						}
					
				}else
				{
					$comment .= '<br/>Not saved ensure you have a patient number entered'.$items['product_name'];
					$class = 'danger';
				}
				
				
				$response .= '
					
						<tr class="'.$class.'">
							<td>'.$r.'</td>
							<td>'.$items['product_name'].'</td>
							<td>'.$items['product_unitprice'].'</td>
							<td>'.$items['product_unitprice_insurance'].'</td>
						</tr> 
				';
			}
			
			$response .= '</table>';
			
			$return['response'] = $response;
			$return['check'] = TRUE;
		}
		
		//if no products exist
		else
		{
			$return['response'] = 'Product data not found';
			$return['check'] = FALSE;
		}
		
		return $return;
	}
}
?>