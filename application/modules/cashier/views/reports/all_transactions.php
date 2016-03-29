<!-- search -->
<?php echo $this->load->view('search/transactions', '', TRUE);?>
<!-- end search -->
<?php echo $this->load->view('transaction_statistics', '', TRUE);?>
 
<div class="row">
    <div class="col-md-12">

        <section class="panel panel-featured panel-featured-info">
            <header class="panel-heading">
            	 <h2 class="panel-title"><?php echo $title;?></h2>
            </header>             

          <!-- Widget content -->
                <div class="panel-body">
          <h5 class="center-align"><?php echo $this->session->userdata('search_title');?></h5>
<?php
		// $result = '<a href="'.site_url().'cashier/reports/export_transactions" class="btn btn-sm btn-success pull-right">Export</a>';
		$result ='';
		if(!empty($search))
		{
			echo '<a href="'.site_url().'cashier/reports/close_search/'.$module.'" class="btn btn-sm btn-warning">Close Search</a>';
		}
		
		//if users exist display them
		if ($query->num_rows() > 0)
		{
			$count = $page;
			
			$result .= 
				'
					<table class="table table-hover table-bordered table-striped table-responsive col-md-12">
					  <thead>
						<tr>
						  <th>#</th>
						  <th>Visit Date</th>
						  <th>Order Number</th>
						  <th>Cash</th>
						  
				';
				
			$result .= '
			
						  <th>Invoice Total</th>
						  <th>Balance</th>
						  <th></th>
						</tr>
					  </thead>
					  <tbody>
			';
			
			$personnel_query = $this->reports_model->get_all_personnel();
			
			foreach ($query->result() as $row)
			{
				$total_invoiced = 0;
				$orders_date = date('jS M Y',strtotime($row->orders_date));
				
				
				$order_id = $row->order_id;
				$order_number = $row->order_number;

				// this is to check for any credit note or debit notes
				$payments_value = $this->cashier_model->total_payments($order_id);
				$invoice_total = $this->cashier_model->total_invoice($order_id);
				$balance = $this->cashier_model->balance($payments_value,$invoice_total);
				// end of the debit and credit notes

				
				$count++;
				
				//payment data
				$cash = $this->reports_model->get_all_visit_payments($order_id);
				$charges = '';
				
				foreach($services_query->result() as $service)
				{
					$service_id = $service->service_id;
					$visit_charge = $this->reports_model->get_all_visit_charges($order_id, $service_id);
					$total_invoiced += $visit_charge;
					
					//$charges .= '<td>'.$visit_charge.'</td>';
				}

				// payment value ///
				
				//display all debtors
				if($debtors == 'true' && (($cash - $total_invoiced) > 0))
				{
					$result .= 
						'
							<tr>
								<td>'.$count.'</td>
								<td>'.$orders_date.'</td>
								<td>'.$order_number.'</td>
								<td>'.$payments_value.'</td>
								'.$charges;
						
					$result .= '
								<td>'.$invoice_total.'</td>
								<td>'.($balance).'</td>
							</tr> 
					';
				}
				
				//display cash & all transactions
				else
				{
					$result .= 
						'
							<tr>
								<td>'.$count.'</td>
								<td>'.$orders_date.'</td>
								<td>'.$order_number.'</td>
								<td>'.$payments_value.'</td>
						'.$charges;
						
					$result .= '
								<td>'.$invoice_total.'</td>
								<td>'.($balance).'</td>
								<td><a href="'.site_url().'cashier/print_invoice_new/'.$order_id.'/'.$order_number.'" class="btn btn-sm btn-success" target="_blank">Invoice</a></td>
							</tr> 
					';
				}
			}
			
			$result .= 
			'
						  </tbody>
						</table>
			';
		}
		
		else
		{
			$result .= "There are no visits";
		}
		
		echo $result;
?>
          </div>
          
          <div class="widget-foot">
                                
				<?php if(isset($links)){echo $links;}?>
            
                <div class="clearfix"></div> 
            
            </div>
        
		</section>
    </div>
  </div>