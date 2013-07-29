<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Stocktake</title>
	
	<link rel="stylesheet" type="text/css" href="css/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	
	<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/functions.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript">

		var codes;
		var decoded = new Array();

		var startpos = 0;
		var endpos = 0;
		
		$(document).ready(function(){

			//$("#sel_cn").focus();

			$('#chk_custom').click(function(){ 
				var $checkbox = $(this).find(':checkbox');
				
				if ( $(this).prop('checked') ) {
					$("#sel_cn").prop("disabled", true);
					$("#txt_cn").prop("disabled", false);
					$("#btn_add_stock").prop("disabled", false);
				} else {
					$("#sel_cn").removeAttr("disabled", false);
					$("#txt_cn").attr("disabled", true);
					$("#btn_add_stock").attr("disabled", true);
				}
				
				$checkbox.attr('checked', !$checkbox.is(':checked'));
			});

			$("#sel_cn").on("change", function(){
				var total = parseInt( $('#scan_result #txt_qoh').val() ) + parseInt( $('#scan_result #txt_adj').val() ) + parseInt( $(this).val() );
				var total_adj = parseInt( $('#scan_result #def_adj').val() ) + parseInt( $(this).val() );

				$('#scan_result #def_qoh').val( $('#scan_result #txt_qoh').val() );
		    	$('#scan_result #def_tc').val( total );
		    	$('#scan_result #def_cn').val( $(this).val() );
		    	$('#scan_result #def_adj').val( total_adj );
				
				var form = document.getElementById("frmAddStock");
				//var form = document.getElementById("btn_add_stock");
		        form.submit();
			});
			
			//var sample_code = '31000524';
			//var sample = setTimeout(function () {
			//    $('#txt_scan').val(sample_code);
		    //}, 10000);

			var checkVal = setInterval(function () {
			    if ( $('#txt_scan').val() != "" ) {
				   $('#h_scan').val( $('#txt_scan').val() );
				    var form = document.getElementById("frmScan");
			        form.submit();
			    }
		    }, 1000);

		    
			
			// SET STOCK CODES
			codes = <?php echo $rs_stock; ?>;
			
			$.each(codes, function() {
			  decoded.push(this["StockCode"]);
			});

			$("#tabs").tabs();

			// CONVERT UPPERCASE
			$('#txt_scan').on('keyup', function(e) {
				$('#txt_scan').val($('#txt_scan').val().replace(/([a-z])/,function(s){return s.toUpperCase()}));
			});
			
			// SCAN QUERY MATCH
			$('#txt_scan').on('keypress', function(e) {
				clearTimeout($.data(this, 'timer'));
				var code = (e.keyCode ? e.keyCode : e.which);
				
	            if (code == 13) {
	                e.preventDefault();

	                if($('#txt_scan').val() == "")
						return true;

	                //$('#txt_scan').ajaxresult();
	                
	            }  else {
	            	
					var wait = setTimeout(function () {
				    	var input = $("#txt_scan");
				    	var searchVal = $("#txt_scan").val();
		
				    	if(input.val() != ""){
							for(var i=0; i<decoded.length; i++){
								if((decoded[i].substring(0, searchVal.length) != searchVal) && searchVal != null)
									continue;
								
								input.val(searchVal + decoded[i].substring(searchVal.length, decoded[i].length));
								input.selectRange(searchVal.length, decoded[i].length);
								input.setCursorPosition(searchVal.length);
	
								break;
							}
				    	}else{
							//$("#result").css("display", "block");
							$("#scan_result").css("display", "none");
					    }
					    
				    }, 1000);
				    $(this).data('timer', wait);
	            }
			});

			$("#frmScan #submit").on("click", function(){
				//$(this).ajaxresult();
			});
			
		    //set selection range
		    $.fn.selectRange = function(start, end) {
		        return this.each(function() {
		            if (this.setSelectionRange) {
		                this.focus();
		                this.setSelectionRange(start, end);
		            } else if (this.createTextRange) {
		                var range = this.createTextRange();
		                range.collapse(true);
		                range.moveEnd('character', end);
		                range.moveStart('character', start);
		                range.select();
		            }
		        });
		    };

		    //set cursor position
		    $.fn.setCursorPosition = function(position){
		        if(this.length == 0) return this;
		        //return $(this).setSelection(position, position);
		    };

		    //append individual count
		    $.fn.appendCount = function(obj){
		    	this.html("");
		    	
			    if(obj == null){
			    	this.append("<tr><td>None</td><td>&nbsp;</td></tr>");
			    	return true;
				}
				
			    for(var i=0; i<obj.length; i++){
					this.append("<tr><td>"+obj[i]["UsrInitials"]+"</td><td>"+obj[i]["QtyCounted"]+"</td></tr>");
			    }
			};

			// Ajax Result
			$.fn.ajaxresult = function(){
				$.ajax({
					url: "Ajax/scan.php",
					beforeSend: function(){
						$("#scan_result #load").html("<img src='images/loading.gif' width='20px' height='20px' />");
					},
					data: { stock_code: $("#txt_scan").val() },
					type: 'post',
					success: function(obj){
						
						if(obj["rs_jimstock"] == null){
							alert("No result found!");
							return true;
						}
						
						$("#scan_result #load").html("");
						$('#scan_result #ind_count tbody').appendCount(obj["rs_indcount"]);
						
						$("#scan_result").css("display", "block");

						var total = Number(obj["rs_jimstock"][0]["QtyOnHand"]) + Number(obj["rs_jimstock"][0]["AdjustQty"]);

						$('#scan_result #row_id').val( obj["rs_jimstock"][0]["RowID"] );
						$('#scan_result #scn_stockno').val( obj["rs_jimstock"][0]["StockNo"] );
						$('#scan_result #scn_stockcode').html( obj["rs_jimstock"][0]["StockCode"] );
						$('#scan_result #scn_stockdesc').html( obj["rs_jimstock"][0]["StockDesc"] );
						$('#scan_result #scn_qtyonhand').html( Number(obj["rs_jimstock"][0]["QtyOnHand"]) );
						$('#scan_result #scn_total').html( total );

						$('#scan_result #def_tc').val( total );
						$('#scan_result #def_adj').val( Number(obj["rs_jimstock"][0]["AdjustQty"]) );
						
						$('#scan_result #txt_qoh').val( Number(obj["rs_jimstock"][0]["QtyOnHand"]) );
						$('#scan_result #txt_tc').val( total );
						$('#scan_result #txt_adj').val( Number(obj["rs_jimstock"][0]["AdjustQty"]) );

					},
					error: function(){
						alert("An error has occured");
					}
				});
			};

		    // On Submit Add Count
		    $('#frmAddStock').submit(function() {
			    if($('#scan_result #txt_cn').val() == ""){
			    	alert("There's nothing to add");
			    	return false;
				}
				
		    	$('#scan_result #def_qoh').val( $('#scan_result #txt_qoh').val() );
		    	$('#scan_result #def_tc').val( $('#scan_result #txt_tc').val() );
		    	$('#scan_result #def_cn').val( $('#scan_result #txt_cn').val() );
		    	$('#scan_result #def_adj').val( $('#scan_result #txt_adj').val() );
		    	
		    	return true;
		    });

			// ONCHANGE TC
			$('#scan #scan_result form table tbody tr #scn_form #txt_cn').keyup(function(e) {
		        	var result = $('#scan #scan_result form table tbody tr #add_count');
		        	var count = $('#scan #scan_result form table tbody tr #scn_form #txt_cn');

		        	if(parseInt(count.val())){
			        	result.find("#txt_tc").val( parseInt(result.find("#def_tc").val()) + parseInt(count.val()) );
			        	result.find("#txt_adj").val( parseInt(result.find("#def_adj").val()) + parseInt(count.val()) );
		        	}else{
			        	result.find("#txt_tc").val(parseInt(result.find("#def_tc").val()));
			        	result.find("#txt_adj").val(parseInt(result.find("#def_adj").val()));
			        }
			  });

			// TOGGLE INDIVIDUAL COUNT
			//$("#res #ind_count tbody").hide();
			//$('#res #ind_count a').each(function() {
				//$(this).bind(
				//	"click",
				//	function() {
				//		var par = $("");
					    //$("#res #ind_count tbody").slideToggle('slow');
					    //e.preventDefault();
				//	}
			//});
			  
		});// document

	</script>
	
</head>
<body>

<div id="container">

	<div id="message"><h3><i><?= $message ?></i></h3></div>
	<div id="undo" style="display:<?= $undo_block ?>;">
		<form id="frmUndo" name="frmUndo" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>?id=<?= $id->ret_val() ?>" >
			<input type="hidden" id="row_id" name="row_id" value="<?= $undo["RowID"] ?>" />
			<input type="hidden" id="line_id" name="line_id" value="<?= $undo["LineID"] ?>" />
			<input type="hidden" id="new" name="new" value="<?= $undo["NewStock"] ?>" />
			<h3><i><?= $undo_message ?></i></h3>
			<input type="submit" id="undo" name="undo" value="Undo Count" />
		</form>
	</div>
	
	<br /><br />
	
	<div id="tabs">
		
		<ul>
			<li><a href="#scan">Scan Product</a></li>
			<li><a href="#result">Stock List</a></li>
		</ul>
		
		<div id="result">
			<?php if(count($rs_stocktake)){?>
			<table border="1" align="center">
				<tbody>
					<?php for($i=0; $i<count($rs_stocktake); $i++){ ?>
					<tr>
						<td colspan="4">
							<div><?= $rs_stocktake[$i]["StockCode"] ?></div>
							<div><?= $rs_stocktake[$i]["StockDesc"] ?></div>
						</td>
					</tr>
					<tr>
						<td>QOH</td>
						<td><?= intval($rs_stocktake[$i]["QtyOnHand"]) ?></td>
						<td>Counted Qty</td>
						<td><?= ($rs_stocktake[$i]["AdjustQty"]+$rs_stocktake[$i]["QtyOnHand"]) ?></td>
					</tr>
					<tr>
						<td colspan="4" id="res">
							<table border="1" id="ind_count">
								<thead>
									<tr>
										<td>User</td>
										<td>Counted Qty</td>
										<td><a href="#">Show</a></td>
									</tr>
								</thead>
								<tbody>
									<?php 
									for( $j=0; $j<count($rs_stockdet); $j++ ){ 
										if( $rs_stocktake[$i]["RowID"] == $rs_stockdet[$j]["RowID"] ){	
											//echo ( $rs_stockdet[$j]["QtyCounted"])."  ";	
									?>
									<tr>
										<td><?= $rs_stockdet[$j]["UsrInitials"] ?></td>
										<td colspan="2"><?= $rs_stockdet[$j]["QtyCounted"] ?></td>
									</tr>
									<?php 
										}
									} 
									?>
								</tbody>
							</table>
						</td>
					</tr>
					<?php 
					} 
					?>
				</tbody>
			</table>
			<?php } else { echo "<h2>No Results Found</h2>"; } ?>
		</div><!-- result -->
		
		<div id="scan" style="display:none;">
			
			<form id="frmScan" name="frmScan" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>?id=<?= $id->ret_val() ?>">
				<h2>Scan Product</h2>
				<p>
					<input type="hidden" id="h_scan" name="h_scan" value="" />
					<input type="text" id="txt_scan" name="txt_scan" value="<?php //= $code->ret_val() ?>" disabled />
					<!-- <input type="button" id="submit" name="submit" value="Scan" />
					<input type="submit" id="submitForm" name="submitForm" value="Scan" /> -->
				</p>
			</form>
			
			<!-- <div id="scan_result" style="display:none;"> -->
			<div id="scan_result" style="display:<?= $scn ?>;">
				<div id="load"></div>
				<form id="frmAddStock" name="frmAddStock" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>?id=<?= $id->ret_val() ?>" >
					
					<table align="center">
						<tbody>
							<tr>
								<td colspan="4">
									<div id="scn_stockcode">&nbsp;<?= $rs_stockcode["rs_jimstock"][0]["StockCode"] ?></div>
									<div id="scn_stockdesc">&nbsp;<?= $rs_stockcode["rs_jimstock"][0]["StockDesc"] ?></div>
									<div id="scn_form">
										<br /><br />
										<fieldset id="fld_count">
											<label><b>Add Count</b></label>
											<select name="sel_cn" id="sel_cn" >
												<option value="------">-----</option>
												<?php for($i=30; $i!=0; $i--){ ?>
												<option value="<?= $i ?>"><?= $i ?></option>
												<?php } ?>
											</select><br />
											<legend>Add Count</legend>
											<label><b>Custom Count</b></label>
											<input type="checkbox" id="chk_custom" name="chk_custom" /><br />
											<input type="text" id="txt_cn" name="txt_cn" disabled /><br />
											<input type="submit" id="btn_add_stock" name="btn_add_stock" value="Add Count" disabled />
										</fieldset>
									</div>
								</td>
							</tr>
							<tr>
								<td><h3>QOH</h3></td>
								<td><h3><div id="scn_qtyonhand"><?= $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] ?></div></h3></td>
								<td><h3>Counted</h3></td>
								<td><h3><div id="scn_total"><?= ( (int) $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] + (int) $rs_stockcode["rs_jimstock"][0]["AdjustQty"] )  ?></div></h3></td>
							</tr>
							<tr>
								<td colspan="4">
									<div id="add_count" name="add_count" align="left">
										<input type="hidden" id="row_id" name="row_id" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["RowID"] ?>" />
										<input type="hidden" id="def_tc" name="def_tc" value="<?= ( (int) $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] + (int) $rs_stockcode["rs_jimstock"][0]["AdjustQty"] )  ?>" />
										<input type="hidden" id="def_adj" name="def_adj" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["AdjustQty"] ?>" />
										<input type="hidden" id="def_qoh" name="def_qoh" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] ?>" />
										<input type="hidden" id="def_cn" name="def_cn" value="" />
										<input type="hidden" id="scn_stockno" name="scn_stockno" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["StockNo"] ?>" />
		
										<input type="text" id="txt_qoh" name="txt_qoh" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] ?>" disabled/><label>Qty On Hand</label><br />
										<input type="text" id="txt_tc" name="txt_tc" value="<?= ( (int) $rs_stockcode["rs_jimstock"][0]["QtyOnHand"] + (int) $rs_stockcode["rs_jimstock"][0]["AdjustQty"] )  ?>" disabled/><label>Total Counted</label><br />
										<input type="text" id="txt_adj" name="txt_adj" value="<?= (int) $rs_stockcode["rs_jimstock"][0]["AdjustQty"] ?>" disabled/><label>Adjust Qty</label><br />
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					
					<table id="ind_count">
						<thead>
							<tr>
								<td colspan="2"><h3>Previous Counts</h3></td>
							</tr>
							<tr>
								<td><h4>User</h4></td>
								<td><h4>Counted Qty</h4></td>
							</tr>
							<?php 
							if( $rs_stockcode["rs_indcount"] ) {
								for($i = 0; $i < count($rs_stockcode["rs_indcount"]); $i++) {
							?>
							<tr>
								<td><?= $rs_stockcode["rs_indcount"][$i]["UsrInitials"] ?></td>
								<td><?= $rs_stockcode["rs_indcount"][$i]["QtyCounted"] ?></td>
							</tr>
							<?php
								} 
							} else {
							?>
							<tr>
								<td colspan="2">None</td>
							</tr>
							<?php 
							}
							?>
						</thead>
						<tbody>
						
						</tbody>
					</table>
					
				</form>
			</div><!-- scan_result -->
			
		</div><!-- scan -->
	
	</div><!-- tabs -->
	
	<div id="top"><h4>Hi <?= $initials ?>, <a href="logout.php">Logout</a></h4></div>
	
	<h2>Stocktake <?= $id->ret_val() ?> - <?php echo $rs_stockinfo[0]["LocCode"]; ?> </h2>
	<h2><?php echo $rs_stockinfo[0]["SessionDate"]->format('Y-m-d H:i:s'); ?></h2>
	<div align="center">
		<a href="stocktake.php">Back</a>
	</div>
		
	<br /><br />
	
	<!-- THIS IS FOR FUTURE VERSIONS -->
	<!-- 
	<div>
		<h2>Stats</h2>
		<table border="1" align="center">
			<tbody>
				<tr>
					<td>Products with SOH &gt 0</td>
					<td>1056</td>
				</tr>
				<tr>
					<td>Products counted</td>
					<td>1056</td>
				</tr>
				<tr>
					<td colspan="2">
						<a href="">Counted Products</a>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<a href="">Not counted Products</a>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<a href="">Count &lt SOH Products</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	 -->
</div><!-- container -->

</body>
</html>


