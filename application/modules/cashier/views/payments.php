
 <section class="panel">
	<header class="panel-heading">
		<h2 class="panel-title">Billing for <?php echo $order_number;?> </h2>
		<a href="<?php echo site_url();?>orders" class="btn btn-sm btn-primary pull-right" style="margin-top:-25px;" >Back to orders</a>
	</header>
	
	<!-- Widget content -->
	
	<div class="panel-body">
		<div class="row">
			<div class="col-md-12">
			<?php
				$error = $this->session->userdata('error_message');
				$success = $this->session->userdata('success_message');
				
				if(!empty($error))
				{
				  echo '<div class="alert alert-danger">'.$error.'</div>';
				  $this->session->unset_userdata('error_message');
				}
				
				if(!empty($success))
				{
				  echo '<div class="alert alert-success">'.$success.'</div>';
				  $this->session->unset_userdata('success_message');
				}
			 ?>
			</div>
		</div>
        <div class="row">
		    <div class="col-md-12">
		        <section class="panel panel-featured panel-featured-info">
		            <header class="panel-heading">
		                <h2 class="panel-title">Supply Charges</h2>
		            </header>
		            <div class="panel-body">
		                <div class="col-lg-8 col-md-8 col-sm-8">
		                  <div class="form-group">
		                    <select id='service_charge_id' name='service_charge_id' class='form-control custom-select '>
		                    <!-- <select class="form-control custom-select" id='service_charge_id' name='service_charge_id'> -->
		                      <option value=''>None - Please Select a service_charge</option>
		                      <?php echo $service_charges;?>
		                    </select>
		                  </div>
		                
		                </div>
		                <div class="col-lg-4 col-md-4 col-sm-4">
		                  <div class="form-group">
		                      <button class="btn btn-sm btn-success"  onclick="parse_service_charge(<?php echo $order_id;?>,1);"> Add service charge</button>
		                  </div>
		                </div>
		               
		            </div>
		             <!-- visit Procedures from java script -->
		                <div id="service_charges_to_order"></div>
		                <!-- end of visit procedures -->
		         </section>
		    </div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="col-md-7">
					
					<div class="row">
						<div class="col-md-12">
							<section class="panel panel-featured panel-featured-info">
								<header class="panel-heading">
									
									<h2 class="panel-title">Invoices Charges</h2>
								</header>
								<div class="panel-body">
                                	<div class="row">
                                    	<div class="col-md-12">
                                        	<a href="<?php echo site_url();?>cashier/print_invoice_new/<?php echo $order_id;?>/<?php echo $order_number;?>" target="_blank" class="btn btn-sm btn-primary pull-right" style="margin-bottom:10px;" >Print Invoice</a>
                                        </div>
                                    </div>
									<div id="invoiced_charges"></div>
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
                                	<div class="row">
                                    	<div class="col-md-12">
                                        	<a href="<?php echo site_url();?>cashier/print_receipt_new/<?php echo $order_id;?>/<?php echo $order_number;?>" target="_blank" class="btn btn-sm btn-primary pull-right" style="margin-bottom:10px;" >Print all Receipts</a>
                                        </div>
                                    </div>
									<table class="table table-hover table-bordered col-md-12">
										<thead>
											<tr>
												<th>#</th>
												<th>Time</th>
												<th>Service</th>
												<th>Method</th>
												<th>Amount</th>
												<th colspan="2"></th>
											</tr>
										</thead>
										<tbody>
											<?php
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
													$total = $total + $visit_total;
												endforeach;
											}
											$total_amount = $total ;
											$payments_rs = $this->cashier_model->payments($order_id);
											$total_payments = 0;
											$total_amount = ($total + $debit_note_amount) - $credit_note_amount;
											
											if(count($payments_rs) > 0)
											{
												$x=0;

												foreach ($payments_rs as $key_items):
													$x++;
													$payment_method = $key_items->payment_method;

													$time = $key_items->time;
													$payment_type = $key_items->payment_type;
													$payment_id = $key_items->payment_id;
													$payment_status = $key_items->payment_status;
													$payment_service_id = $key_items->payment_service_id;
													$service_name = '';
													
													if($payment_type == 1 && $payment_status == 1)
													{
														$amount_paid = $key_items->amount_paid;
														$amount_paidd = number_format($amount_paid,2);
														
														if(count($item_invoiced_rs) > 0)
														{
															foreach ($item_invoiced_rs as $key_items):
															
																$service_id = $key_items->service_id;
																
																if($service_id == $payment_service_id)
																{
																	$service_name = $key_items->service_name;
																	break;
																}
															endforeach;
														}
													
														//display DN & CN services
														if((count($payments_rs) > 0) && ($service_name == ''))
														{
															foreach ($payments_rs as $key_items):
																$payment_type = $key_items->payment_type;
																
																if(($payment_type == 2) || ($payment_type == 3))
																{
																	$payment_service_id2 = $key_items->payment_service_id;
																	
																	if($payment_service_id2 == $payment_service_id)
																	{
																		$service_name = $this->cashier_model->get_service_detail($payment_service_id);
																		break;
																	}
																}
																
															endforeach;
														}
														?>
														<tr>
															<td><?php echo $x;?></td>
															<td><?php echo $time;?></td>
															<td><?php echo $service_name;?></td>
															<td><?php echo $payment_method;?></td>
															<td><?php echo $amount_paidd;?></td>
															<td><a href="<?php echo site_url().'cashier/print_single_receipt/'.$payment_id;?>/<?php echo $order_id;?>/<?php echo $order_number;?>" class="btn btn-small btn-warning" target="_blank"><i class="fa fa-print"></i></a></td>
															<td>
                                                            	<button type="button" class="btn btn-small btn-default" data-toggle="modal" data-target="#refund_payment<?php echo $payment_id;?>"><i class="fa fa-times"></i></button>
<!-- Modal -->
<div class="modal fade" id="refund_payment<?php echo $payment_id;?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            	<h4 class="modal-title" id="myModalLabel">Cancel payment</h4>
            </div>
            <div class="modal-body">
            	<?php echo form_open("cashier/cancel_payment/".$payment_id.'/'.$order_id.'/'.$order_number, array("class" => "form-horizontal"));?>
                <div class="form-group">
                    <label class="col-md-4 control-label">Action: </label>
                    
                    <div class="col-md-8">
                        <select class="form-control" name="cancel_action_id">
                        	<option value="">-- Select action --</option>
                            <?php
                                if($cancel_actions->num_rows() > 0)
                                {
                                    foreach($cancel_actions->result() as $res)
                                    {
                                        $cancel_action_id = $res->cancel_action_id;
                                        $cancel_action_name = $res->cancel_action_name;
                                        
                                        echo '<option value="'.$cancel_action_id.'">'.$cancel_action_name.'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-4 control-label">Description: </label>
                    
                    <div class="col-md-8">
                        <textarea class="form-control" name="cancel_description"></textarea>
                    </div>
                </div>
                
                <div class="row">
                	<div class="col-md-8 col-md-offset-4">
                    	<div class="center-align">
                        	<button type="submit" class="btn btn-primary">Save action</button>
                        </div>
                    </div>
                </div>
                <?php echo form_close();?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
                                                            </td>
														</tr>
														<?php
														$total_payments =  $total_payments + $amount_paid;
													}
												endforeach;

												?>
												<tr>
													<td colspan="4"><strong>Total : </strong></td>
													<td><strong> <?php echo number_format($total_payments,2);?></strong></td>
												</tr>
												<?php
											}
											
											else
											{
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
					<!-- END OF THIRD ROW -->
					
					<div class="row" style= "margin-top:2em">
						<div class="col-md-12">
							<table class="table table-hover table-bordered">
								<tbody>
									<tr>
										<td colspan="3"><strong>Balance :</strong></td>
										<td><strong> <?php echo number_format(($total_amount - $total_payments),2) ;?> </strong></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- END OF FOURTH ROW -->
				</div>
				<!-- END OF THE SPAN 7 -->
				  
				<!-- START OF THE SPAN 5 -->
				<div class="col-md-5">
					<div class="row">
						<div class="col-md-12">
							<section class="panel panel-featured panel-featured-info">
								<header class="panel-heading">
									
									<h2 class="panel-title">Add payment</h2>
								</header>
                                
								<div class="panel-body">
									<?php echo form_open("cashier/make_payments/".$order_id.'/'.$order_number, array("class" => "form-horizontal"));?>
										<div class="form-group">
											<div class="col-lg-4">
                                            	<div class="radio">
                                                    <label>
                                                        <input id="optionsRadios2" type="radio" name="type_payment" value="1" checked="checked" onclick="getservices(1)"> 
                                                        Normal
                                                    </label>
                                                </div>
											</div>
											<div class="col-lg-4">
                                            	<div class="radio">
                                                    <label>
                                                        <input id="optionsRadios2" type="radio" name="type_payment" value="2" onclick="getservices(2)"> 
                                                        Debit Note
                                                    </label>
                                                </div>
											</div>
											<div class="col-lg-4">
                                            	<div class="radio">
                                                    <label>
                                                        <input id="optionsRadios2" type="radio" name="type_payment" value="3" onclick="getservices(3)"> 
                                                        Credit Note
                                                    </label>
                                                </div>
											</div>
										</div>
                                        
										<div id="service_div2" class="form-group">
											<label class="col-lg-4 control-label">Service: </label>
										  
											<div class="col-lg-8" id="services_selected">
                                            	
											</div>
										</div>
                                        
                                    	<div id="service_div" class="form-group" style="display:none;">
                                            <label class="col-lg-4 control-label"> Services: </label>
                                            
                                            <div class="col-lg-8">
                                                <select class="form-control" name="payment_service_id" >
                                                	<option value="">--Select a service--</option>
													<?php
                                                    $service_rs = $this->cashier_model->get_all_service();
                                                    $service_num_rows = count($service_rs);
                                                    if($service_num_rows > 0)
                                                    {
														foreach($service_rs as $service_res)
														{
															$service_id = $service_res->service_id;
															$service_name = $service_res->service_name;
															
															echo '<option value="'.$service_id.'">'.$service_name.'</option>';
														}
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

										<div class="form-group">
											<label class="col-lg-4 control-label">Amount: </label>
										  
											<div class="col-lg-8">
												<input type="text" class="form-control" name="amount_paid" placeholder="" autocomplete="off">
											</div>
										</div>
										
										<div class="form-group" id="payment_method">
											<label class="col-lg-4 control-label">Payment Method: </label>
											  
											<div class="col-lg-8">
												<select class="form-control" name="payment_method" onchange="check_payment_type(this.value)">
                                                	<?php
													  $method_rs = $this->cashier_model->get_payment_methods();
													  $num_rows = count($method_rs);
													 if($num_rows > 0)
													  {
														
														foreach($method_rs as $res)
														{
														  $payment_method_id = $res->payment_method_id;
														  $payment_method = $res->payment_method;
														  
															echo '<option value="'.$payment_method_id.'">'.$payment_method.'</option>';
														  
														}
													  }
												  ?>
												</select>
											  </div>
										</div>
										<div id="mpesa_div" class="form-group" style="display:none;" >
											<label class="col-lg-4 control-label"> Mpesa TX Code: </label>

											<div class="col-lg-8">
												<input type="text" class="form-control" name="mpesa_code" placeholder="">
											</div>
										</div>
									  
										<div id="insuarance_div" class="form-group" style="display:none;" >
											<label class="col-lg-4 control-label"> Insurance Number: </label>
											<div class="col-lg-8">
												<input type="text" class="form-control" name="insuarance_number" placeholder="">
											</div>
										</div>
									  
										<div id="cheque_div" class="form-group" style="display:none;" >
											<label class="col-lg-4 control-label"> Cheque Number: </label>
										  
											<div class="col-lg-8">
												<input type="text" class="form-control" name="cheque_number" placeholder="">
											</div>
										</div>
									  
										<div id="username_div" class="form-group" style="display:none;" >
											<label class="col-lg-4 control-label"> Username: </label>
										  
											<div class="col-lg-8">
												<input type="text" class="form-control" name="username" placeholder="">
											</div>
										</div>
									  
										<div id="password_div" class="form-group" style="display:none;" >
											<label class="col-lg-4 control-label"> Password: </label>
										  
											<div class="col-lg-8">
												<input type="password" class="form-control" name="password" placeholder="">
											</div>
										</div>
										
										<div class="center-align">
											<button class="btn btn-info btn-sm" type="submit">Add Payment Information</button>
										</div>
										<?php echo form_close();?>
								</div>
							</section>
						</div>
					</div>
				
					<!-- Bill Methods -->
					<div class="row">
						<div class="col-md-12">
							<section class="panel panel-featured panel-featured-info">
								<header class="panel-heading">
									
									<h2 class="panel-title">Calculator</h2>
								</header>
								
								<div class="panel-body">
									
                                   <link rel="stylesheet" href="<?php echo base_url().'assets/calculator/';?>prism.css">
                                   <script src="<?php echo base_url().'assets/calculator/';?>prism.js"></script>
                                
                                   <link rel="stylesheet" href="<?php echo base_url().'assets/calculator/';?>SimpleCalculadorajQuery.css">
                                   <script src="<?php echo base_url().'assets/calculator/';?>SimpleCalculadorajQuery.js"></script>
                                   
                                   <div id="micalc"></div>
                                   <script>
									 $("#micalc").Calculadora({'EtiquetaBorrar':'Clear',TituloHTML:''});
									
										$("#CalcOptoins").Calculadora({
											EtiquetaBorrar:'Clear',
											ClaseBtns1: 'warning', /* Color Numbers*/
											ClaseBtns2: 'success', /* Color Operators*/
											ClaseBtns3: 'primary', /* Color Clear*/
											TituloHTML:'<h2>Develoteca.com</h2>',
											Botones:["+","-","*","/","0","1","2","3","4","5","6","7","8","9",".","="]
										});
									 
									
									</script>
								</div>
							</section>
						</div>
					</div>
					<!-- End Bill Methods -->

				</div>
				<!-- END OF THE SPAN 5 -->
			</div>
		</div>
			
		<div class="row " style= "margin-top:2em">
			<div class="center-align">
				<!-- redirect to unclosed accounts queue -->
				<?php
			  
				if($going_to->num_rows() > 0)
				{
					$row = $going_to->row();
				  
					$department_id = $row->department_id;
					$accounts = $row->accounts;
					$department_name = $row->department_name;
					
					if($department_name == 'Accounts')
					{
						$query = $this->cashier_model->get_last_department($order_id);
						
						if($query->num_rows() > 0)
						{
							$row2 = $query->row();
						  
							$department_id = $row2->department_id;
							$accounts = 0;
							$department_name = $row2->department_name;
						}
					}
				  
				  	//without end visit
					//if(($accounts == 0) && ($department_id != 6))
					if(($accounts == 0))
					{
						?>
						<a href= "<?php echo site_url();?>accounts/send_to_department/<?php echo $order_id;?>/<?php echo $department_id;?>" class="btn btn-sm btn-warning" onclick="return confirm(\'Do you want to send to <?php echo $department_name;?>?\');">Send to <?php echo $department_name;?></a>
                        
						<a href= "<?php echo site_url();?>reception/end_visit/<?php echo $order_id;?>/1" class="btn btn-sm btn-danger" onclick="return confirm(\'Do you want to end visit?\');">End Visit</a>
						<?php
					}
				  	
					//with end visit
					else
					{
						//if(($accounts == 0) && ($department_id == 6))
						if(($accounts == 0))
						{
							?>
							<a href= "<?php echo site_url();?>accounts/send_to_department/<?php echo $order_id;?>/<?php echo $department_id;?>" class="btn btn-sm btn-warning" onclick="return confirm(\'Do you want to send to <?php echo $department_name;?>?\');">Send to <?php echo $department_name;?></a>
							<?php
						}
						if(isset($order_number))
						{
							?>
							<a href= "<?php echo site_url();?>reception/end_visit/<?php echo $order_id;?>/<?php echo $order_number;?>" class="btn btn-sm btn-danger" onclick="return confirm(\'Do you want to end visit?\');">End Visit</a>
							<?php
						}
					  
						else
						{
							?>
							<a href= "<?php echo site_url();?>reception/end_visit/<?php echo $order_id;?>/1" class="btn btn-sm btn-danger" onclick="return confirm(\'Do you want to end visit?\');">End Visit</a>
							<?php
						}
				  }
			  }
			 ?>
			</div>
		</div>
		<!-- END OF PADD -->
	</div>
</section>
<script text="javascript">
$(function() {
    $("#service_charge_id").customselect();
});
$(document).ready(function(){
  	service_charge_interface(<?php echo $order_id;?>);
  	show_invoiced_charges(<?php echo $order_id;?>);
  	show_services_charged(<?php echo $order_id?>);

	$(function() {
		$("#service_charge_id").customselect();
	});
});

function service_charge_interface(order_id){

    var XMLHttpRequestObject = false;
        
    if (window.XMLHttpRequest) {
    
        XMLHttpRequestObject = new XMLHttpRequest();
    } 
        
    else if (window.ActiveXObject) {
        XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
    }

    var config_url = document.getElementById("config_url").value;
    var url = "<?php echo site_url();?>cashier/charged_items/"+order_id;

  if(XMLHttpRequestObject) {
        
        var obj = document.getElementById("service_charges_to_order");
                
        XMLHttpRequestObject.open("GET", url);
                
        XMLHttpRequestObject.onreadystatechange = function(){
            
            if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {

                obj.innerHTML = XMLHttpRequestObject.responseText;
            }
        }
                
        XMLHttpRequestObject.send(null);
    }
}
function show_invoiced_charges(order_id){

    var XMLHttpRequestObject = false;
        
    if (window.XMLHttpRequest) {
    
        XMLHttpRequestObject = new XMLHttpRequest();
    } 
        
    else if (window.ActiveXObject) {
        XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
    }

    var config_url = document.getElementById("config_url").value;
    var url = "<?php echo site_url();?>cashier/show_invoiced_items/"+order_id;

  if(XMLHttpRequestObject) {
        
        var obj = document.getElementById("invoiced_charges");
                
        XMLHttpRequestObject.open("GET", url);
                
        XMLHttpRequestObject.onreadystatechange = function(){
            
            if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {

                obj.innerHTML = XMLHttpRequestObject.responseText;
            }
        }
                
        XMLHttpRequestObject.send(null);
    }
}
function show_services_charged(order_id)
{
	 var XMLHttpRequestObject = false;
        
    if (window.XMLHttpRequest) {
    
        XMLHttpRequestObject = new XMLHttpRequest();
    } 
        
    else if (window.ActiveXObject) {
        XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
    }

    var config_url = document.getElementById("config_url").value;
    var url = "<?php echo site_url();?>cashier/show_services_charged/"+order_id;

  if(XMLHttpRequestObject) {
        
        var obj = document.getElementById("services_selected");
                
        XMLHttpRequestObject.open("GET", url);
                
        XMLHttpRequestObject.onreadystatechange = function(){
            
            if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {

                obj.innerHTML = XMLHttpRequestObject.responseText;
            }
        }
                
        XMLHttpRequestObject.send(null);
    }
}
function parse_service_charge(order_id,suck)
{
  var service_charge_id = document.getElementById("service_charge_id").value;

  service_charge(service_charge_id, order_id,suck);

}
 function service_charge(id, order_id,suck){
        
        var XMLHttpRequestObject = false;
            
        if (window.XMLHttpRequest) {
        
            XMLHttpRequestObject = new XMLHttpRequest();
        } 
            
        else if (window.ActiveXObject) {
            XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
        }
        var url = "<?php echo site_url();?>cashier/order_service_charges/"+id+"/"+order_id+"/"+suck;
        
        if(XMLHttpRequestObject) {
                    
            XMLHttpRequestObject.open("GET", url);
                    
            XMLHttpRequestObject.onreadystatechange = function(){
                
                if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
                    
                   document.getElementById("service_charges_to_order").innerHTML = XMLHttpRequestObject.responseText;
                   //get_surgery_table(visit_id);
                   service_charge_interface(order_id);
                   show_invoiced_charges(order_id);
                   show_services_charged(order_id);
                }
            }
            
            XMLHttpRequestObject.send(null);
        }
    }
function calculatevaccinetotal(amount, id, procedure_id, order_id)
{
    var units = document.getElementById('units'+id).value;  

    grand_service_charge_total(id, units, amount, order_id);
}
function calculateconsumabletotal(amount, id, procedure_id, order_id)
{

    var units = document.getElementById('units'+id).value;  

    grand_service_charge_total(id, units, amount, order_id);
}

function grand_service_charge_total(charge_id, units, amount, order_id){
    var XMLHttpRequestObject = false;
        
    if (window.XMLHttpRequest) {
    
        XMLHttpRequestObject = new XMLHttpRequest();
    } 
        
    else if (window.ActiveXObject) {
        XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    var url = "<?php echo site_url();?>cashier/service_charge_total/"+charge_id+"/"+units+"/"+amount;

    if(XMLHttpRequestObject) {
                
        XMLHttpRequestObject.open("GET", url);
                
        XMLHttpRequestObject.onreadystatechange = function(){
            
            if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
                service_charge_interface(order_id);
                show_invoiced_charges(order_id);
                show_services_charged(order_id);
            }
        }
                
        XMLHttpRequestObject.send(null);
    }
}
function delete_order_service_charge(id, order_id){
	
	var confirmation = confirm('Delete service charged?');
	
	if(confirmation)
	{
		var XMLHttpRequestObject = false;
			
		if (window.XMLHttpRequest) {
		
			XMLHttpRequestObject = new XMLHttpRequest();
		} 
			
		else if (window.ActiveXObject) {
			XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
		}
		var config_url = document.getElementById("config_url").value;
		var url = config_url+"cashier/delete_order_service_charge/"+id;
		
		if(XMLHttpRequestObject) {
					
			XMLHttpRequestObject.open("GET", url);
					
			XMLHttpRequestObject.onreadystatechange = function(){
				
				if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
	
					service_charge_interface(order_id);
                	show_invoiced_charges(order_id);
                	show_services_charged(order_id);
				}
			}
					
			XMLHttpRequestObject.send(null);
		}
	}
}

</script>
  <!-- END OF ROW -->
<script type="text/javascript">
  function getservices(id){

        var myTarget1 = document.getElementById("service_div");
        var myTarget2 = document.getElementById("username_div");
        var myTarget3 = document.getElementById("password_div");
        var myTarget4 = document.getElementById("service_div2");
        var myTarget5 = document.getElementById("payment_method");
		
        if(id == 1)
        {
          myTarget1.style.display = 'none';
          myTarget2.style.display = 'none';
          myTarget3.style.display = 'none';
          myTarget4.style.display = 'block';
          myTarget5.style.display = 'block';
        }
        else
        {
          myTarget1.style.display = 'block';
          myTarget2.style.display = 'block';
          myTarget3.style.display = 'block';
          myTarget4.style.display = 'none';
          myTarget5.style.display = 'none';
        }
        
  }
  function check_payment_type(payment_type_id){
   
    var myTarget1 = document.getElementById("cheque_div");

    var myTarget2 = document.getElementById("mpesa_div");

    var myTarget3 = document.getElementById("insuarance_div");

    if(payment_type_id == 1)
    {
      // this is a check
     
      myTarget1.style.display = 'block';
      myTarget2.style.display = 'none';
      myTarget3.style.display = 'none';
    }
    else if(payment_type_id == 2)
    {
      myTarget1.style.display = 'none';
      myTarget2.style.display = 'none';
      myTarget3.style.display = 'none';
    }
    else if(payment_type_id == 3)
    {
      myTarget1.style.display = 'none';
      myTarget2.style.display = 'none';
      myTarget3.style.display = 'block';
    }
    else if(payment_type_id == 4)
    {
      myTarget1.style.display = 'none';
      myTarget2.style.display = 'none';
      myTarget3.style.display = 'none';
    }
    else if(payment_type_id == 5)
    {
      myTarget1.style.display = 'none';
      myTarget2.style.display = 'block';
      myTarget3.style.display = 'none';
    }
    else
    {
      myTarget2.style.display = 'none';
      myTarget3.style.display = 'block';  
    }

  }
</script>