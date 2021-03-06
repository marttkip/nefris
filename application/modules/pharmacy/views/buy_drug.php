 <section class="panel">
    <header class="panel-heading">
          <h4 class="pull-left"><i class="icon-reorder"></i><?php echo $title;?></h4>
          <div class="widget-icons pull-right">
            <a href="#" class="wminimize"><i class="icon-chevron-up"></i></a> 
          </div>
          <div class="clearfix"></div>
        </header>             

        <!-- Widget content -->
         <div class="panel-body">
          <div class="padd">
          <div class="center-align">
          	<?php
            	$error = $this->session->userdata('error_message');
				$success = $this->session->userdata('success_message');
				
				if(!empty($error))
				{
					echo '<div class="alert alert-danger">'.$error.'</div>';
					$this->session->unset_userdata('error_message');
				}
				
				if(!empty($validation_errors))
				{
					echo '<div class="alert alert-danger">'.$validation_errors.'</div>';
				}
				
				if(!empty($success))
				{
					echo '<div class="alert alert-success">'.$success.'</div>';
					$this->session->unset_userdata('success_message');
				}
			?>
          </div>
			<?php echo form_open($this->uri->uri_string(), array("class" => "form-horizontal"));?>
        	<div class="row">
                
                <div class="col-md-offset-3 col-md-6">
                    
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Container Type: </label>
                        
                        <div class="col-lg-8">
                            <select class="form-control" name="container_type_id">
                                <?php
                                    if(count($container_types) > 0)
                                    {
                                        foreach($container_types as $res)
                                        {
                                            $container_type_id = $res->container_type_id;
                                            $container_type_name = $res->container_type_name;
                                            
                                            if($container_type_id == set_value("container_type_id"))
                                            {
                                                echo '<option value="'.$container_type_id.'" selected>'.$container_type_name.'</option>';
                                            }
                                            
                                            else
                                            {
                                                echo '<option value="'.$container_type_id.'">'.$container_type_name.'</option>';
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Purchase Quantity: </label>
                        
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="purchase_quantity" placeholder="Purchase Quantity" value="<?php echo set_value('purchase_quantity');?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Pack Size: </label>
                        
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="purchase_pack_size" placeholder="Pack Size" value="<?php echo set_value('purchase_pack_size');?>">
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label class="col-lg-4 control-label">Expiry Date: </label>
                        
                        <div class="col-lg-8">
                            <div id="datetimepicker_other_patient" class="input-append">
                                <input data-format="yyyy-MM-dd" class="form-control" type="text" name="expiry_date" placeholder="Expiry Date" value="<?php echo set_value('expiry_date');?>">
                                <span class="add-on">
                                    &nbsp;<i data-time-icon="icon-time" data-date-icon="icon-calendar" style="cursor:pointer;">
                                    </i>
                                </span>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
            
            <div class="center-align">
            	<a href="<?php echo site_url().'/pharmacy/drug_purchases/'.$drugs_id;?>" class="btn btn-lg btn-default">Back</a>
                <button class="btn btn-info btn-lg" type="submit">Add Purchase</button>
            </div>
            <?php echo form_close();?>
            
          </div>
        </div>
        <!-- Widget ends -->

      </div>
 </section>