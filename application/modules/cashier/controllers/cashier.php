<?php   if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Cashier extends MX_Controller
{ 
	
	function __construct()
	{
		parent:: __construct();
		$this->load->model('admin/users_model');
		$this->load->model('inventory_management/products_model');
		$this->load->model('cashier_model');
		// $this->load->model('account/suppliers_model');
		// $this->load->model('accounts/categories_model');
		$this->load->model('site/site_model');
		$this->load->model('admin/sections_model');
		$this->load->model('admin/admin_model');
		$this->load->model('administration/personnel_model');
		$this->load->model('hr/personnel_model');
		$this->load->model('database');
	}
    public function payments($order_id, $order_number)
	{
		$v_data = array('order_id'=>$order_id,'order_number'=>$order_number);

		$service_charge_order = 'service_charge.service_charge_name';
		$service_charge_table = 'service_charge,product,category';
		$consumble_where = 'service_charge.product_id = product.product_id AND category.category_id = product.category_id AND service_charge.service_charge_status = 1';

		$service_charge_query = $this->cashier_model->get_service_charge_list($service_charge_table, $consumble_where, $service_charge_order);
		$rs8 = $service_charge_query->result();
		$service_charges = '';
		foreach ($rs8 as $service_charge_rs) :


		$service_charge_id = $service_charge_rs->service_charge_id;
		$service_charge_name = $service_charge_rs->service_charge_name;

		$service_charge_name_stud = $service_charge_rs->service_charge_amount;

		    $service_charges .="<option value='".$service_charge_id."'>".$service_charge_name." KES.".$service_charge_name_stud."</option>";

		endforeach;
		$v_data['service_charges'] = $service_charges;
		$v_data['cancel_actions'] = $this->cashier_model->get_cancel_actions();
		$v_data['going_to'] = $this->cashier_model->get_going_to($order_id);
		$v_data['credit_note_amount'] = $this->cashier_model->get_sum_credit_notes($order_id);
		$v_data['debit_note_amount'] = $this->cashier_model->get_sum_debit_notes($order_id);
		$v_data['item_invoiced_rs'] = $this->cashier_model->get_patient_visit_charge_items($order_id);
		$v_data['total'] = 0;
		$v_data['s']=0;

		$data['content'] = $this->load->view('payments', $v_data, true);
		
		$data['title'] = 'Payments';
		$data['sidebar'] = 'accounts_sidebar';
		$this->load->view('admin/templates/general_page', $data);
	}
	public function charged_items($order_id)
	{
		$data = array('order_id'=>$order_id);
		$this->load->view('show_charged_items',$data);	
	}
	public function show_services_charged($order_id)
	{
		$data = array('order_id'=>$order_id);
		$data['item_invoiced_rs'] = $this->cashier_model->get_patient_visit_charge_items($order_id);
		$data['payments_rs'] = $this->cashier_model->payments($order_id);
		$this->load->view('services_charged',$data);	
	}
	public function show_invoiced_items($order_id)
	{
		$data = array('order_id'=>$order_id);
		$this->load->view('invoiced_charges',$data);	
	}
	public function charge_total($charge_id,$units,$amount){
		

		$visit_data = array('visit_charge_units'=>$units,'modified_by'=>$this->session->userdata("personnel_id"),'date_modified'=>date("Y-m-d"));
		$this->db->where(array("visit_charge_id"=>$charge_id));
		$this->db->update('visit_charge', $visit_data);
	}
	function order_service_charges($service_charge_id,$order_id,$suck)
	{
		$data = array('service_charge_id'=>$service_charge_id,'order_id'=>$order_id,'suck'=>$suck);
		$this->cashier_model->submitorderservicecharge($service_charge_id,$order_id,$suck);		
		$this->cashier_model->visit_charge_insert($order_id,$service_charge_id,$suck);
	}
	public function service_charge_total($service_charge_id,$units,$amount){
		

		$visit_data = array('visit_charge_units'=>$units,'modified_by'=>$this->session->userdata("personnel_id"),'date_modified'=>date("Y-m-d"));
		$this->db->where(array("visit_charge_id"=>$service_charge_id));
		$this->db->update('visit_charge', $visit_data);


	}
	public function delete_order_service_charge($id)
	{
		$visit_data = array('visit_charge_delete'=>1,'deleted_by'=>$this->session->userdata("personnel_id"),'deleted_on'=>date("Y-m-d"),'modified_by'=>$this->session->userdata("personnel_id"),'date_modified'=>date("Y-m-d"));

		$this->db->where(array("visit_charge_id"=>$id));
		$this->db->update('visit_charge', $visit_data);

	}
	public function make_payments($order_id, $order_number)
	{
		$this->form_validation->set_rules('payment_method', 'Payment Method', 'trim|required|xss_clean');
		$this->form_validation->set_rules('amount_paid', 'Amount', 'trim|required|xss_clean');
		$this->form_validation->set_rules('type_payment', 'Type of payment', 'trim|required|xss_clean');
		$payment_method = $this->input->post('payment_method');
		// normal or credit note or debit note
		$type_payment = $this->input->post('type_payment');
		
		// Normal
		if($type_payment == 1)
		{
			$this->form_validation->set_rules('service_id', 'Service', 'xss_clean');
			if(!empty($payment_method))
			{
				if($payment_method == 1)
				{
					// check for cheque number if inserted
					$this->form_validation->set_rules('cheque_number', 'Cheque Number', 'trim|required|xss_clean');
				}
				else if($payment_method == 3)
				{
					// check for insuarance number if inserted
					$this->form_validation->set_rules('insuarance_number', 'Insurance Number', 'trim|required|xss_clean');
				}
				else if($payment_method == 5)
				{
					//  check for mpesa code if inserted
					$this->form_validation->set_rules('mpesa_code', 'Amount', 'trim|required|xss_clean');
				}
			}
		}
		else if($type_payment == 2)
		{
			// debit note
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('payment_service_id', 'Service', 'required|xss_clean');
		}
		else if($type_payment == 3)
		{
			$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
			$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
			$this->form_validation->set_rules('payment_service_id', 'Service', 'required|xss_clean');
		}
		//if form conatins invalid data
		if ($this->form_validation->run())
		{
			
			if($type_payment == 3 || $type_payment == 2)
			{
				$username=$this->input->post('username');
				$password=$this->input->post('password');
				// check if the username and password is for an administrator
				$checker_response = $this->cashier_model->check_admin_person($username, $password);
				// end of checker function
				if(($checker_response > 0))
				{
					$this->cashier_model->receipt_payment($order_id, $checker_response);
				}
				else
				{
					$this->session->set_userdata("error_message","Seems like you dont have the priviledges to effect this event. Please contact your administrator.");
				}
			}
			else
			{
				$this->cashier_model->receipt_payment($order_id);

				//$this->sync_model->syn_up_on_closing_visit($order_id);
			}
			
			//sync data
			//$response = $this->sync_model->syn_up_on_closing_visit($order_id);
			
			redirect('cashier/payments/'.$order_id.'/'.$order_number);
		}
		else
		{
			$this->session->set_userdata("error_message", validation_errors());
			redirect('cashier/payments/'.$visit_id.'/'.$order_number);
		}
	}
	public function print_invoice_new($order_id,$order_number,$department_name = NULL)
	{
		$data = array('order_id'=>$order_id,'order_number'=>$order_number);
		$data['contacts'] = $this->site_model->get_contacts();
		
		
		$this->load->view('invoice', $data);
		
	}
	public function print_single_receipt($payment_id,$order_id,$order_number)
	{
		$data = array('payment_id' => $payment_id,'order_id'=>$order_id,'order_number'=>$order_number);
		$data['contacts'] = $this->site_model->get_contacts();
		$data['receipt_payment_id'] = $payment_id;
		$this->load->view('single_receipt', $data);
	}
	public function print_receipt_new($order_id,$order_number)
	{
		$data = array('order_id'=>$order_id,'order_number'=>$order_number);
		$data['contacts'] = $this->site_model->get_contacts();
		
		$this->load->view('receipt', $data);
	}
	public function cancel_payment($payment_id, $order_id,$order_number)
	{
		$this->form_validation->set_rules('cancel_description', 'Description', 'trim|required|xss_clean');
		$this->form_validation->set_rules('cancel_action_id', 'Action', 'trim|required|xss_clean');
		
		//if form conatins invalid data
		if ($this->form_validation->run())
		{
			// end of checker function
			if($this->cashier_model->cancel_payment($payment_id))
			{
				$this->session->set_userdata("success_message", "Payment action saved successfully");
			}
			else
			{
				$this->session->set_userdata("error_message", "Oops something went wrong. Please try again");
			}
			redirect('cashier/payments/'.$order_id.'/'.$order_number);
		}
		else
		{
			$this->session->set_userdata("error_message", validation_errors());
			redirect('cashier/payments/'.$order_id.'/'.$order_number);
		}
	}
}
?>