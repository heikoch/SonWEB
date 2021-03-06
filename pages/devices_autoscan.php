<?php
	
	$status       = FALSE;
	$devices      = NULL;
	$devicesFound = NULL;
	$msg          = NULL;
	$action       = "";
	$error        = FALSE;
	
	if ( isset( $_POST ) && !empty( $_POST ) ) {
		
		if ( isset( $_POST[ "search" ] ) ) {
			
			$fromip = explode( ".", $_POST[ "from_ip" ] );
			$toip   = explode( ".", $_POST[ "to_ip" ] );
			$urls   = [];
			
			$devices = $Sonoff->getDevices();
			$skipIps = [];
			
			foreach ( $devices as $device ) {
				$skipIps[] = $device->ip;
			}
			
			while ( $fromip[ 3 ] <= $toip[ 3 ] ) {
				if ( !in_array( implode( ".", $fromip ), $skipIps ) ) {
					
					
					$fakeDevice           = new stdClass();
					$fakeDevice->ip       = implode( ".", $fromip );
					$fakeDevice->username = isset( $_POST[ "device_username" ] ) ? $_POST[ "device_username" ] : "";
					$fakeDevice->password = isset( $_POST[ "device_password" ] ) ? $_POST[ "device_password" ] : "";
					$cmnd                 = "status 0";
					
					
					$urls[] = $Sonoff->buildCmndUrl( $fakeDevice, $cmnd );
					
					
					unset( $fakeDevice );
				}
				$fromip[ 3 ]++;
			}
			$devicesFound = $Sonoff->search( $urls );
			
			if ( empty( $devicesFound ) ) {
				$msg   = __( "MSG_NO_DEVICES_FOUND", "DEVICES_AUTOSCAN" );
				$error = TRUE;
			} else {
				$devicesFoundTmp = $devicesFound;
				$devicesFound    = [];
				foreach ( $devicesFoundTmp as $device ) {
					$ip                       = explode( ".", $device->StatusNET->IPAddress );
					$devicesFound[ $ip[ 3 ] ] = $device;
				}
				ksort( $devicesFound );
				$devicesFound = array_values( $devicesFound );
				unset( $devicesFoundTmp );
				$msg = __( "MSG_DEVICES_FOUND_COUNT", "DEVICES_AUTOSCAN" ).": ".count( $devicesFound );
			}
			
			
		} else if ( isset( $_POST[ "save_all" ] ) ) { //add
			
			
			$handle = fopen( $filename, "a" );
			foreach ( $_POST[ "devices" ] as $device ) {
				
				$fp                = file( $filename );
				$deviceHolder      = [];
				$deviceHolder[ 0 ] = count( $fp ) + 1;
				$deviceHolder[ 1 ] = implode( "|", isset( $device[ "device_name" ] ) ? $device[ "device_name" ] : [] );
				$deviceHolder[ 2 ] = isset( $device[ "device_ip" ] ) ? $device[ "device_ip" ] : "";
				$deviceHolder[ 3 ] = isset( $_POST[ "device_username" ] ) ? $_POST[ "device_username" ] : "";
				$deviceHolder[ 4 ] = isset( $_POST[ "device_password" ] ) ? $_POST[ "device_password" ] : "";
				$deviceHolder[ 5 ] = isset( $device[ "device_img" ] ) ? $device[ "device_img" ] : "bulb_1";
				$deviceHolder[ 6 ] = isset( $device[ "device_position" ] ) ? $device[ "device_position" ] : "";
				
				
				fputcsv( $handle, $deviceHolder );
				
			}
			fclose( $handle );
			$msg    = __( "MSG_DEVICES_ADD_DONE", "DEVICES_AUTOSCAN" );
			$action = "done";
			
		}
	}

?>
<div class='row justify-content-sm-center'>
	<div class='col-12 col-md-8 col-xl-6'>
		<h2 class='text-sm-center mb-5'>
			<?php echo $title; ?>
		</h2>
		<?php if ( isset( $error ) && $error ): ?>
			<div class='row justify-content-sm-center'>
				<div class='col-12'>
					<div class="alert alert-danger fade show mb-5" data-dismiss="alert" role="alert">
						<?php echo $msg; ?>
					</div>
				</div>
			</div>
		<?php elseif ( isset( $msg ) && $msg != "" ): ?>
			<div class='row justify-content-sm-center'>
				<div class='col-12'>
					<div class="alert alert-success fade show mb-5" role="alert">
						<?php echo $msg; ?>
						<?php if ( $action == "done" ): ?>
							<div class="text-left mt-3">
								<a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
									<?php echo __( "BTN_BACK", "DEVICE_ACTIONS" ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		
		<form class='form'
		      name='autoscan_form'
		      method='post'>
			
			<div class="form-row">
				<div class="form-group col-12 col-sm-6">
					<label for="from_ip">
						<?php echo __( "FROM_IP", "DEVICES_AUTOSCAN" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="from_ip"
					       name='from_ip'
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo isset( $_POST[ "from_ip" ] ) ? $_POST[ "from_ip" ] : "192.168.178.1"; ?>'
					       required>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __( "FROM_IP_HELP", "DEVICES_AUTOSCAN" ); ?>
					</small>
				</div>
				<div class="form-group col-12 col-sm-6">
					<label for="to_ip">
						<?php echo __( "TO_IP", "DEVICES_AUTOSCAN" ); ?>
					</label>
					<input type="text"
					       class="form-control"
					       id="to_ip"
					       name='to_ip'
					       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
					       value='<?php echo isset( $_POST[ "to_ip" ] ) ? $_POST[ "to_ip" ] : "192.168.178.254"; ?>'
					       required>
					<small id="from_ipHelp" class="form-text text-muted">
						<?php echo __( "TO_IP_HELP", "DEVICES_AUTOSCAN" ); ?>
					</small>
				</div>
			</div>
			<div class="form-group">
				<label for="device_username">
					<?php echo __( "DEVICE_USERNAME", "DEVICE_ACTIONS" ); ?>
				</label>
				<input type="text"
				       class="form-control"
				       id="device_username"
				       name='device_username'
				       value='<?php echo isset( $_POST[ "device_username" ] ) ? $_POST[ "device_username" ]
					       : "admin"; ?>'
				>
				<small id="device_usernameHelp" class="form-text text-muted">
					<?php echo __( "DEVICE_USERNAME_HELP", "DEVICE_ACTIONS" ); ?>
				</small>
			</div>
			<div class="form-group">
				<label for="device_password">
					<?php echo __( "DEVICE_PASSWORD", "DEVICE_ACTIONS" ); ?>
				</label>
				<input type="text"
				       class="form-control"
				       id="device_password"
				       name='device_password'
				       value='<?php echo isset( $_POST[ "device_password" ] ) ? $_POST[ "device_password" ] : ""; ?>'
				>
				<small id="device_passwordHelp" class="form-text text-muted">
					<?php echo __( "DEVICE_PASSWORD_HELP", "DEVICE_ACTIONS" ); ?>
				</small>
			</div>
			<div class='row justify-content-sm-center mt-5'>
				<div class="col-12 col-sm-6 text-center">
					<button type='submit'
					        name='search'
					        value='search'
					        class='btn btn-primary col-12 col-sm-auto'
					        onclick='waitingDialog.show("", { headerText:
							        "<?php echo __( "MSG_SCANNING", "DEVICES_AUTOSCAN" ); ?>"
							        }
							        );'
					>
						<?php echo __( "BTN_START_AUTOSCAN", "DEVICES_AUTOSCAN" ); ?>
					</button>
				</div>
			</div>
			
			
			<?php if ( !empty( $devicesFound ) ): ?>
				<?php foreach (
					$devicesFound
					
					as $idx => $device
				): ?>
					<hr class='my-5'/>
					<h3 class='text-sm-center mb-5'>
						<?php echo __( "DEVICE", "DEVICES_AUTOSCAN" )." ".( $idx + 1 ); ?>
					</h3>
					<div class="form-row">
						<div class="form-group col-12 col-sm-12">
							<label for="device_ip">
								<?php echo __( "DEVICE_IP", "DEVICE_ACTIONS" ); ?>
							</label>
							<input type="text"
							       class="form-control disabled"
							       id="device_ip_fake"
							       name='devices[<?php echo $idx; ?>][device_ip]'
							       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
							       value='<?php echo $device->StatusNET->IPAddress; ?>'
							       disabled required>
							<input type='hidden'
							       name='devices[<?php echo $idx; ?>][device_ip]'
							       value='<?php echo $device->StatusNET->IPAddress; ?>'>
							<small id="device_ipHelp" class="form-text text-muted">
								<?php echo __( "DEVICE_IP_HELP", "DEVICE_ACTIONS" ); ?>
							</small>
						</div>
					</div>
					
					
					<div class="form-group">
						<label for="device_position">
							<?php echo __( "DEVICE_POSITION", "DEVICE_ACTIONS" ); ?>
						</label>
						<input type="text"
						       class="form-control"
						       id="device_position"
						       name='devices[<?php echo $idx; ?>][device_position]'
						       value='<?php echo $idx + 1; ?>'
						>
						<small id="device_positionHelp" class="form-text text-muted">
							<?php echo __( "DEVICE_POSITION_HELP", "DEVICE_ACTIONS" ); ?>
						</small>
					</div>
					<?php if ( isset( $device->StatusSTS->POWER ) ): ?>
						
						<div class="form-row">
							<div class="form-group col-12 col-sm-6">
								<label for="device_name">
									<?php echo __( "LABEL_NAME", "DEVICE_ACTIONS" ); ?>
								</label>
								<input type="text"
								       class="form-control"
								       id="device_name"
								       name='devices[<?php echo $idx; ?>][device_name][]'
								       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
								       value='<?php echo $device->Status->FriendlyName; ?>'
								       required>
								<small id="device_nameHelp" class="form-text text-muted d-none d-sm-block">
									&nbsp;
								</small>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label class="d-none d-sm-block mb-3">&nbsp;</label>
								( <a href='#'
								     class='default-name'><?php echo $device->Status->FriendlyName; ?></a>
								)
								<small id="default_nameHelp" class="form-text text-muted">
									<?php echo __( "DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS" ); ?>
								</small>
							
							
							</div>
							<div class="form-group col-12 col-sm-3">
								<a id='test_device' class='btn btn-secondary col-12 test_device'
								   style='margin-top: 1.8rem;'
								   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
								   data-device_relais='1'
								>
									<?php echo __( "BTN_TEST", "DEVICES_AUTOSCAN" ); ?>
								</a>
							</div>
						</div>
					<?php endif; ?>
					
					
					<?php
					$i            = 1;
					$power        = "POWER".$i;
					$channelFound = FALSE;
					
					while ( isset( $device->StatusSTS->$power ) )  : ?>
						<?php $channelFound = TRUE; ?>
						<div class="form-row">
							<div class="form-group col-12 col-sm-6">
								<label for="device_name_<?php echo $i; ?>">
									<?php echo __( "LABEL_NAME", "DEVICE_ACTIONS" ); ?><?php echo $i; ?>
								</label>
								<input type="text"
								       class="form-control"
								       id="device_name_<?php echo $i; ?>"
								       name='devices[<?php echo $idx; ?>][device_name][<?php echo $i; ?>]'
								       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
								       value='<?php echo isset( $device->names[ $i - 1 ] )
								                         && !empty(
								       $device->names[ $i - 1 ]
								       ) ? $device->names[ $i - 1 ] : $device->Status->FriendlyName." ".$i; ?>'
								       required>
								<small id="device_name_<?php echo $i; ?>Help"
								       class="form-text text-muted d-none d-sm-block">
									&nbsp;
								</small>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label class="d-none d-sm-block mb-3">&nbsp;</label>
								(
								<a href='#' title='<?php echo __( "OVERTAKE", "DEVICE_ACTIONS" ); ?>'
								   class='default-name'>
									<?php echo $device->Status->FriendlyName." ".$i; ?>
								</a>
								)
								<small id="default_nameHelp" class="form-text text-muted">
									<?php echo __( "DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS" ); ?>
								</small>
							
							
							</div>
							<div class="form-group col-12 col-sm-3">
								<a id='' class='btn btn-secondary col-12 test_device'
								   style='margin-top: 1.8rem;'
								   data-device_ip='<?php echo $device->StatusNET->IPAddress; ?>'
								   data-device_relais='<?php echo $i; ?>'
								>
									<?php echo __( "BTN_TEST", "DEVICES_AUTOSCAN" ); ?>
								</a>
							</div>
						</div>
						
						
						<?php
						
						$i++;
						$power = "POWER".$i;
						?>
					
					<?php endwhile; ?>
					
					<?php if ( !isset( $device->StatusSTS->POWER ) && !$channelFound ) :
						//no channel found?>
						
						<div class="form-row">
							<div class="form-group col-12 col-sm-9">
								<label for="device_name">
									<?php echo __( "LABEL_NAME", "DEVICE_ACTIONS" ); ?>
								</label>
								<input type="text"
								       class="form-control"
								       id="device_name"
								       name='devices[<?php echo $idx; ?>][device_name][]'
								       placeholder="<?php echo __( "PLEASE_ENTER" ); ?>"
								       value='<?php echo $device->Status->FriendlyName; ?>'
								       required>
								<small id="device_nameHelp" class="form-text text-muted d-none d-sm-block">
									&nbsp;
								</small>
							</div>
							<div class="form-group col-12 col-sm-3">
								<label class="d-none d-sm-block mb-3">&nbsp;</label>
								( <a href='#'
								     class='default-name'><?php echo $device->Status->FriendlyName; ?></a>
								)
								<small id="default_nameHelp" class="form-text text-muted">
									<?php echo __( "DEVICE_NAME_TOOLTIP", "DEVICE_ACTIONS" ); ?>
								</small>
							</div>
						</div>
					<?php endif; ?>
				
				
				<?php endforeach; ?>
				<div class="row">
					<div class="col-12 col-sm-6 text-left">
						<a class="btn btn-secondary  col-12 col-sm-auto" href='<?php echo _BASEURL_; ?>devices'>
							<?php echo __( "BTN_BACK", "DEVICE_ACTIONS" ); ?>
						</a>
					</div>
					<div class="col-12 col-sm-6 text-right">
						<button type='submit'
						        name='save_all'
						        value='save_all'
						        class='btn btn-primary col-12 col-sm-auto'
						
						>
							<?php echo __( "BTN_SAVE_ALL", "DEVICES_AUTOSCAN" ); ?>
						</button>
					</div>
				</div>
				
				</table>
			
			
			<?php endif; ?>
		</form>
	
	
	</div>
</div>
<script>
	$( document ).on( "ready", function () {
		$( ".default-name" ).on( "click", function ( e ) {
			e.preventDefault();
			// console.log( $( this ).parent().parent().find( "input" ) );
			$( this ).parent().parent().find( "input" ).val( $( this ).html() );
		} );
		
		
		$( ".test_device" ).on( "click", function ( e ) {
			e.preventDefault();
			var device_ip     = $( this ).data( "device_ip" );
			var device_relais = $( this ).data( "device_relais" );
			var cmnd          = "Power" + device_relais + "%20toggle";
			
			var url = decodeURIComponent( Sonoff.buildCmndUrl( device_ip, cmnd ) );
			Sonoff.directAjax( url );
		} );
	} );
</script>
