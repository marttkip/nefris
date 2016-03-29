<?php
$result ='
<select name="service_id" class="form-control">
    	<option value="">All services</option>';
	if(count($item_invoiced_rs) > 0)
	{
		$s=0;
		foreach ($item_invoiced_rs as $key_items):
			$s++;
			$service_id = $key_items->service_id;
			$service_name = $key_items->service_name;
			$result .='
            <option value="'.$service_id.'">'.$service_name.'</option>';
			
		endforeach;
	}
		
	//display DN & CN services
	if(count($payments_rs) > 0)
	{
		foreach ($payments_rs as $key_items):
			$payment_type = $key_items->payment_type;
			
			if(($payment_type == 2) || ($payment_type == 3))
			{
				$payment_service_id = $key_items->payment_service_id;
				
				if($payment_service_id > 0)
				{
					$service_associate = $this->cashier_model->get_service_detail($payment_service_id);
					$result .='
					<option value="'.$payment_service_id.'">'.$service_associate.'</option>';
					
				}
			}
			
		endforeach;
	}
	$result .='
    </select>';
echo $result;
?>