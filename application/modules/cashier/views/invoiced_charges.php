<?php

$result = '
<table class="table table-hover table-bordered col-md-12">
	<thead>
		<tr>
			<th>#</th>
			<th>Service</th>
			<th>Item Name</th>
			<th>Units</th>
			<th>Unit Cost</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>';
		$item_invoiced_rs = $this->cashier_model->get_patient_visit_charge_items($order_id);
		$credit_note_amount = $this->cashier_model->get_sum_credit_notes($order_id);
		$debit_note_amount = $this->cashier_model->get_sum_debit_notes($order_id);
		$total = 0;
		$s=0;
		if(count($item_invoiced_rs) > 0)
		{
			foreach ($item_invoiced_rs as $key_items):
				$s++;
				$service_charge_name = $key_items->service_charge_name;
				$visit_charge_amount = $key_items->visit_charge_amount;
				$service_name = $key_items->service_name;
				$units = $key_items->visit_charge_units;
				$visit_total = $visit_charge_amount * $units;
				$personnel_id = $key_items->personnel_id;
				$doctor = '';
				
				if($personnel_id > 0)
				{
					$doctor_rs = $this->reception_model->get_personnel($personnel_id);
					if($doctor_rs->num_rows() > 0)
					{
						$key_personnel = $doctor_rs->row();
						$first_name = $key_personnel->personnel_fname;
						$personnel_onames = $key_personnel->personnel_onames;
						$doctor = ' : Dr. '.$personnel_onames.' '.$first_name;
					}
				}
				$result .='
				<tr>
					<td>'.$s.'</td>
					<td>'.$service_name.'</td>
					<td>'.$service_charge_name.$doctor.'</td>
					<td>'.$units.'</td>
					<td>'.number_format($visit_charge_amount,2).'</td>
					<td>'.number_format($visit_total,2).'</td>
				</tr>';
				$total = $total + $visit_total;
			endforeach;
		}
		$total_amount = $total ;
		// enterring the payment stuff
		$payments_rs = $this->cashier_model->payments($order_id);

		$total_payments = 0;
		$total_amount = ($total + $debit_note_amount) - $credit_note_amount;
		
		if(count($payments_rs) > 0)
		{
			$x = $s;
			foreach ($payments_rs as $key_items):
				$x++;
				$payment_method = $key_items->payment_method;
				
				$amount_paid = $key_items->amount_paid;
				$time = $key_items->time;
				$payment_type = $key_items->payment_type;
				$amount_paidd = number_format($amount_paid,2);
				$payment_service_id = $key_items->payment_service_id;
				
				if($payment_service_id > 0)
				{
				$service_associate = $this->cashier_model->get_service_detail($payment_service_id);
				}
				else
				{
				$service_associate = " ";
				}

				if($payment_type == 2)
				{
					$type = "Debit Note";
					$amount_paidd = $amount_paidd;
					
					$result .='
					<tr>
						<td>'.$x.'</td>
						<td colspan="2">'.$service_associate.'</td>
						<td>1</td>
						<td>'.$amount_paidd.'</td>
					</tr>';
					
				}
				
				else if($payment_type == 3)
				{
					$type = "Credit Note";
					$amount_paidd = "($amount_paidd)";
					
					$result .='
					<tr>
						<td>'.$x.'</td>
						<td colspan="2">'.$service_associate.'</td>
						<td>'.$amount_paidd.'</td>
					</tr>';
				}
				
			endforeach;
		}
		// end of the payments
		$total_amount = ($total + $debit_note_amount) - $credit_note_amount;
		// $total_payments = 0;
	
		if(count($payments_rs) > 0)
		{
			$x=0;
			
			foreach ($payments_rs as $key_items):
				$x++;
			// var_dump($key_items->payment_type); die();
				$payment_type = $key_items->payment_type;
				$payment_status = $key_items->payment_status;
				
				if($payment_type == 1 && $payment_status == 1)
				{

					$payment_method = $key_items->payment_method;
					$amount_paid = $key_items->amount_paid;
					
					$total_payments = $total_payments + $amount_paid;
				}
			endforeach;
	  
		}
		else
		{
			$total_payments = 0;
		}


		$result .='
		<tr>
			<td colspan="5" align="right"><strong>Total Payments:</strong></td>
			<td><strong>'.number_format($total_payments,2).'</strong></td>
		</tr>
		<tr>
			<td colspan="5" align="right"><strong>Total Invoice:</strong></td>
			<td><strong>'.number_format($total_amount - $total_payments,2).'</strong></td>
		</tr>';
	
		$result .= '
	</tbody>
</table>';

echo $result;