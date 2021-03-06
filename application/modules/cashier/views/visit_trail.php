<?php 
$page_name ='';
$item_invoiced_rs = $this->cashier_model->get_patient_visit_charge_items($order_id);
$credit_note_amount = $this->cashier_model->get_sum_credit_notes($order_id);
$debit_note_amount = $this->cashier_model->get_sum_debit_notes($order_id);
$total = 0;

if(count($item_invoiced_rs) > 0)
{
	$s=0;
	
	foreach ($item_invoiced_rs as $key_items):
	$s++;
	$service_charge_name = $key_items->service_charge_name;
	$visit_charge_amount = $key_items->visit_charge_amount;
	$service_name = $key_items->service_name;
	$units = $key_items->visit_charge_units;
	$visit_total = $visit_charge_amount * $units;
	$total = $total + $visit_total;
	endforeach;
	$total_amount = $total ;

}

else
{
	$total_amount = 0;
}

?>


<div class="row" style= "margin-bottom:2em">
	<div class="col-md-12">
        <section class="panel panel-featured panel-featured-info">
            <header class="panel-heading">
                <h2 class="panel-title">Debit | Credit notes</h2>
            </header>
            
            <div class="panel-body">
                <table class="table table-hover table-bordered col-md-12">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody>
						<?php
                        $payments_rs = $this->cashier_model->payments($order_id);
                        $total_payments = 0;
                        $total_amount = ($total + $debit_note_amount) - $credit_note_amount;
                        if(count($payments_rs) > 0)
						{
							$x=0;
							
							foreach ($payments_rs as $key_items):
								$x++;
								$payment_method = $key_items->payment_method;
								
								$amount_paid = $key_items->amount_paid;
								$time = $key_items->time;
								$payment_type = $key_items->payment_type;
								$amount_paidd = number_format($amount_paid,2);
								
								if($payment_type == 2)
								{
									$type = "Debit Note";
									$amount_paidd = $amount_paidd;
                        
									?>
									<tr>
                                        <td><?php echo $time;?></td>
                                        <td><?php echo $amount_paidd;?></td>
                                        <td></td>
									</tr>
									<?php
								}
								
								else if($payment_type == 3)
								{
									$type = "Credit Note";
									$amount_paidd = "($amount_paidd)";
                        
									?>
									<tr>
                                        <td><?php echo $time;?></td>
                                        <td></td>
                                        <td><?php echo $amount_paidd;?></td>
									</tr>
									<?php
								}
                        
							endforeach;
							?>
							<tr>
                                <td>Totals</td>
                                <td><?php echo number_format($debit_note_amount,2);?></td>
                                <td><?php echo number_format($credit_note_amount,2);?></td>
							</tr>
							<tr>
                                <td colspan="2">Difference </td>
                                <td><?php echo number_format($debit_note_amount - $credit_note_amount,2);?></td>
							</tr>
							<?php
						}
							
						else{
							?>
							<tr>
								<td colspan="4"> No payments made yet</td>
							</tr>
							<?php
						}
						?>
						</tbody>
                </table>
            </div>
        </section>
  	</div>
</div>

<div class="row">
  	<div class="col-md-12">
   
        <section class="panel panel-featured panel-featured-info">
            <header class="panel-heading">
                <h2 class="panel-title">Invoice charges</h2>
            </header>
            
            <div class="panel-body">
        		<table class="table table-hover table-bordered col-md-12">
                      <thead>
                      <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>Item Name</th>
                        <th>Time Charged</th>
                        <th>Units</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                        <?php
                        if($page_name == 'administration')
                        {
                          echo "
                             <th>Charged by</th>
                             <th></th>";
                        }
                        else
                        {
                        }
                        ?>
                        
                      </tr>
                      </thead>
                      <tbody>

                        <?php
                        $item_invoiced_rs = $this->cashier_model->get_patient_visit_charge_items($order_id);
                        $total = 0;
                        if(count($item_invoiced_rs) > 0){
                          $s=0;
                          
                          foreach ($item_invoiced_rs as $key_items):
                            $s++;
                            $service_charge_name = $key_items->service_charge_name;
                            $visit_charge_amount = $key_items->visit_charge_amount;
                            $service_name = $key_items->service_name;
                            $service_id = $key_items->service_id;
                            $units = $key_items->visit_charge_units;
                            $created_by = $key_items->created_by;
                            $service_charge_idd = $key_items->service_charge_id;
                            $visit_charge_timestamp = $key_items->visit_charge_timestamp;
                            $visit_charge_id = $key_items->visit_charge_id;
                            $visit_total = $visit_charge_amount * $units;
                            $personell_rs = $this->cashier_model->get_personnel_details($created_by);
                            $item_rs = $this->cashier_model->get_service_charges_per_type($service_id);
							$personnel_id = $key_items->personnel_id;
							$doctor = '';
							
							if($personnel_id > 0)
							{
                            	$doctor_rs = $this->cashier_model->get_personnel($personnel_id);
								if($doctor_rs->num_rows() > 0)
								{
									$key_personnel = $doctor_rs->row();
									$first_name = $key_personnel->personnel_fname;
									$personnel_onames = $key_personnel->personnel_onames;
									$doctor = ' : Dr. '.$personnel_onames.' '.$first_name;
								}
							}
                            if(empty($created_by))
                            {
                                $created_by_name = " - ";
                            }
                            else
                            {
                              if(count($personell_rs) > 0)
                              {
                                foreach ($personell_rs as $key_personnel):
                                  # code...
                                  $first_name = $key_personnel->personnel_fname;
                                  $personnel_onames = $key_personnel->personnel_onames;
                                endforeach;
                                $created_by_name = $first_name;
                              }
                              else
                              {
                                $created_by_name = " - ";
                              }
                            }
                           
                            ?>
                              <tr>
                                <td><?php echo $s;?></td>
                                <td><?php echo $service_name;?></td>
                                <td> 
                                <?php 
                                  if($page_name == 'administration' && $service_id == 1)
                                  {
                                    ?>
                                    <select name="consultation_id" id="consultation_id<?php echo $order_id;?>"   class="form-control">
									<?php
                                    if(count($item_rs) > 0){
                                        foreach($item_rs as $row):
                                            $service_charge_id = $row->service_charge_id;
                                            $service_charge_name= $row->service_charge_name;
                                            
                                            if($service_charge_id == $service_charge_idd)
                                            {
                                                echo "<option value='".$service_charge_id."' selected='selected'>".$service_charge_name."</option>";
                                            }
                                            
                                            else
                                            {
                                                echo "<option value='".$service_charge_id."'>".$service_charge_name."</option>";
                                            }
                                        endforeach;
                                    }
                                    ?>
                                </select>
                                    <?php
                                  }
                                  else
                                  {
                                    echo $service_charge_name.$doctor;
                                  }
                                  $units = $key_items->visit_charge_units;
                     $visit_total = $visit_charge_amount * $units;
                  
                                  ?>
                                  

                                </td>
                                <td><?php echo date('jS M Y H:i a',strtotime($visit_charge_timestamp));?></td>
                                <td><?php echo $units;?></td>
                                <td><?php echo number_format($visit_charge_amount,2);?></td>
                                <td><?php echo number_format($visit_total,2);?></td>
                                <?php
                              if(($page_name == 'administration') && $service_id != 1)
                              {
                                echo '<td>'.$created_by_name.'</td>';
                                echo '<td><a href="'.site_url().'/administration/delete_visit_charge/'.$visit_charge_id.'" class="btn btn-sm btn-danger" onclick="return confirm(\'Do you want to delete this service charge ?\');">Delete Service Charge</a></td>';
                              }
                              else if(($page_name == 'administration') && $service_id == 1)
                              {
                                echo '<td>'.$created_by_name.'</td>';
                                echo '<td><a onclick="update_service_charge('.$visit_charge_id.','.$order_id.')" class="btn btn-sm btn-success">Update Consultation</a></td>';
                              }
                              else
                              {}
                                  
                                ?>

                              </tr>
                            <?php
                              $total = $total + $visit_total;
                          endforeach;
                          
                           $payments_rs = $this->cashier_model->payments($order_id);
                           $total_amount = ($total + $debit_note_amount) - $credit_note_amount;
                           $total_payments = 0;
                            if(count($payments_rs) > 0){
                              $r=0;
                              
                              foreach ($payments_rs as $key_items):
                                $r++;
                                $payment_method = $key_items->payment_method;
                                
                                $time = $key_items->time;
                                $payment_type = $key_items->payment_type;
                                $payment_type = $key_items->payment_type;
                                $payment_status = $key_items->payment_status;
                                                                
                                if($payment_type == 1 && $payment_status == 1)
                                {
                                  $amount_paid = $key_items->amount_paid;
                                  $total_payments = $total_payments + $amount_paid;
                                }
                                else
                                {
                                }
                               
                               
                              endforeach;
                            }
                            ?>
                            <tr>
                             <td colspan="5"></td>
                              <td><span>Total Invoice :</span></td>
                              <td> <?php echo number_format($total,2);?></td>
                            </tr>
                            <tr>
                              <td colspan="5"></td>
                              <td>Total Amount Paid :</td>
                              <td> <?php echo number_format($total_payments,2);?></td>
                            </tr>
                            <tr>
                              <td colspan="5"></td>
                              <td>Balance :</td>
                              <td> <?php echo number_format(($total_amount - $total_payments),2);?></td>
                            </tr>
                            <?php
                        }else{
                           ?>
                            <tr>
                              <td colspan="6"> No Charges</td>
                            </tr>
                            <?php
                        }
                        ?>
                        
                      </tbody>
                    </table>
            </div>
        </section>
  	</div>
</div>

<div class="row" style= "margin-top:2em">
  	<div class="col-md-12">
   
        <section class="panel panel-featured panel-featured-info">
            <header class="panel-heading">
                <h2 class="panel-title">Receipts</h2>
            </header>
            
            <div class="panel-body">
  <table class="table table-hover table-bordered col-md-12">
    <thead>
    <tr>
      <th>#</th>
      <th>Time</th>
      <th>Method</th>
      <th>Amount</th>
      <th></th>
    </tr>
    </thead>
    <tbody>
     <?php
      $payments_rs = $this->cashier_model->payments($order_id);
      $total_payments = 0;
      $total_amount = ($total + $debit_note_amount) - $credit_note_amount;
      if(count($payments_rs) > 0){
        $x=0;
        
        foreach ($payments_rs as $key_items):
          $x++;
          $payment_method = $key_items->payment_method;
          
          $time = $key_items->time;
          $payment_type = $key_items->payment_type;
          $payment_id = $key_items->payment_id;
          $payment_status = $key_items->payment_status;
          if($payment_type == 1)
          {
            if($page_name == "administration")
            {
              $amount_paid = $key_items->amount_paid;
              $amount_paidd = number_format($amount_paid,2);
              ?>
              <tr>
                <td><?php echo $x;?></td>
                <td><?php echo $time;?></td>
                <td><?php echo $payment_method;?></td>
                <td><?php echo $amount_paidd;?></td>
                <?php
                if($payment_status == 0)
                {
                ?>
                <td><a href='<?php echo site_url();?>/administration/increase_receipt/<?php echo $payment_id;?>' class="btn btn-sm btn-success" onclick="return confirm(\'Do you want to increase this receipt entry ?\');">Restore Receipt Entry</a></td>
                <?php
                }
                else
                {
                ?>
                <td><a href='<?php echo site_url();?>/administration/reduce_receipt/<?php echo $payment_id;?>' class="btn btn-sm btn-danger" onclick="return confirm(\'Do you want to delete this receipt entry ?\');">Delete Receipt Entry</a></td>
                <?php
                }
                ?>
              </tr>
              <?php
              $total_payments =  $total_payments + $amount_paid;
            }
            else
            {
              if($payment_status == 1){
                $amount_paid = $key_items->amount_paid;
                $amount_paidd = number_format($amount_paid,2);
                ?>
                <tr>
                  <td><?php echo $x;?></td>
                  <td><?php echo $time;?></td>
                  <td><?php echo $payment_method;?></td>
                  <td><?php echo $amount_paidd;?></td>
                </tr>
                <?php
                $total_payments =  $total_payments + $amount_paid;
              }
            }
            
            
          }
         
        endforeach;
          $payment = $this->cashier_model->total_payments($order_id);
          if($page_name == "administration")
          {
           ?>
           <tr>
            <td colspan="3"><strong>Total payments (before deductions): </strong></td>
            <td><strong> <?php echo number_format($total_payments,2);?></strong></td>
          </tr>
          <tr>
            
            <td colspan="3"><strong>Total payments (after deductions): </strong></td>
            <td><strong> <?php echo number_format($payment,2);?></strong></td>
          </tr>
          <?php
          }
          else
          {
             ?>
              <tr>
                <td colspan="3"><strong>Total payments: </strong></td>
                <td><strong> <?php echo number_format($payment,2);?></strong></td>
              </tr>
            <?php 
          }
        }else{
          ?>
          <tr>
            <td colspan="4"> No payments made yet</td>
          </tr>
          <?php
        }
        ?>
    </tbody>
  </table>
            </div>
        </section>
  	</div>
</div>

<script type="text/javascript">
  
  function update_service_charge(visit_charge_id,order_id){
         
        var config_url = $('#config_url').val();
        var data_url = config_url+"administration/update_visit_charge/"+visit_charge_id;
        
        var consultation_id = $('#consultation_id'+order_id).val();
        
        $.ajax({
        type:'POST',
        url: data_url,
        data:{consultation: consultation_id},
        dataType: 'text',
        success:function(data){
        window.alert("You have successfully updated the charge");
        //obj.innerHTML = XMLHttpRequestObject.responseText;
        },
        error: function(xhr, status, error) {
        //alert("XMLHttpRequest=" + xhr.responseText + "\ntextStatus=" + status + "\nerrorThrown=" + error);
        alert(error);
        }
        });
        
    }
</script>