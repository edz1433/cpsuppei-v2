<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ strtoupper('PAR REPORT ' . $datereport) }}</title>
	<style>
		/*.table-responsive {
		  	overflow-x: auto;
		  	max-width: 100%; 
		}*/
		.text-type {
			text-align: center;
			margin-top: -5px;
		}
		.text1 {
			text-align: center;
		}
		.text2 {
			text-align: center;
		}
		.text3 {
			font-size: 11pt;
			margin-top: 10px;
		}
		.text4 {
			font-size: 11pt;
		}

		#rpcppe {
		  	font-family: Arial;
		  	border-collapse: collapse;
		  	width: 100%;
		  	font-size: 11pt;
		}

		#rpcppe td {
			border: 1px solid #000;
		  	padding: 3px;
		} 
		#rpcppe th {
		  	border: 1px solid #000;
		  	/*padding: 8px;*/
		}
		.icsno{
			text-align: right !important;
			font-size: 8pt;
		}
		.text-total {
			font-size: 12pt !important;
			height: 9px;
		}
		#rpcppe tfoot {
		  	border: 1px solid #000;
		  	padding: 8px;
		}

		#rpcppe tr:nth-child(even){background-color: #f2f2f2;}

		#rpcppe tr:hover {background-color: #ddd;}

		#rpcppe th {
		  	padding-top: 12px;
		  	padding-bottom: 12px;
		  	text-align: center;
		  	background-color: #fff;
		  	font-size: 10pt;
		}
		.footer-cell {
			width: 32%;
			padding: 5px; 
		}

		.footer-cell-title {
  			font-weight: bold;
		}

		.footer-cell-sign {
  			margin-top: 20px;
		}
		.footer-cell-text {
			font-size: 8pt;
		  	margin-top: 5px;
		}
		.sign {
			height: 80px;
		}
		.text-receivedby {
			font-size: 8pt;
		}
		.text-center{
			text-align: center;
		}
	</style>
</head>
<body>
	<p style="margin-top: -30px; margin-left: 640px;">Appendix 71</p>
	<header style="margin-top: -10px; margin-left: 135px;">
		<img src="{{ asset('template/img/par-header.png') }}">
	</header>
	<p style="font-weight: bolder; font-family: sans-serif; text-align: center;">PROPERTY ACKNOWLEDGEMENT RECEIPT</p>

	<div>
		<p style="font-weight: bolder; font-family: sans-serif; font-size: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			@if (!$paritems->isEmpty())
			    <p style="font-weight: bolder; font-family: sans-serif; font-size: 10pt;">
			        Entity Name: {{ $paritems[0]->office_name }}
			    </p>
			@else
			    <p style="font-weight: bolder; font-family: sans-serif; font-size: 10pt;">
			    	No related items found
			    </p>
			@endif

		</p>
		<p style="font-weight: bolder; font-family: sans-serif; font-size: 10pt; margin-top: -28px;">Entity Name: ___________________________________________________</p>
		<p style="font-weight: bolder; font-family: sans-serif; font-size: 10pt;">Fund Cluster: __________________________________________________  PAR No.: _______________________</p>
	</div>
	<div class="table-responsive">
		<table id="rpcppe" class="table table-bordered">
			<thead>
				<tr>
					<th width="30">No</th>
					<th width="30">Qty</th>
					<th width="8%">Unit</th>
					<th width="30%">Description</th>
					<th>Property Number</th>
					@if($locationcolumn == 1)
					<th class="">Location</th>
					@endif
					<th width="13%">Date Acquired</th>
					<th width="13%">Amount</th>
				</tr>
			</thead>
			<tbody>
				@php
			        $maxRows = 3;
			        $rowCount = 0;
					$no = 1;
			        $overallTotal = 0;
			        $grandTotal = 0;
			    @endphp

			    @foreach ($paritems as $paritem)
			        <tr>
						<td style="text-align: center;">{{ $no++ }}</td>
			            <td style="text-align: center;">{{ $paritem->qty }}</td>
			            <td>{{ $paritem->unit_name }}</td>
						<td>
							<b>{{ $paritem->item_name }}</b>
							<br><i> {{ $paritem->item_descrip }}</i><br>
							<b>MODEL:</b>{{ $paritem->item_model ? str_replace('Model:', '', $paritem->item_model) : '' }}<br>
							<b>SN : </b> <span style="font-size: 12px;">{!!  str_replace(';', '<br>', $paritem->serial_number) !!}</span>
						</td>
			            <td>{{ $paritem->property_no_generated }}</td>
						@if($locationcolumn == 1)
							<td>{{ $paritem->itemlocated }}</td>
						@endif
			            <td>{{ $paritem->date_acquired }}</td>
			            <td align="right"><b>{{ $paritem->item_cost }}</b></td>

			            @if (is_numeric(str_replace(',', '', $paritem->item_cost)))
			                {{-- @php $overallTotal += str_replace(',', '', $paritem->item_cost); @endphp --}}
			                @php 
				                $itemTotal = $paritem->qty * str_replace(',', '', $paritem->item_cost);
				                $overallTotal += $itemTotal;
				                $grandTotal += $itemTotal; // Add to grand total
				            @endphp
			            @endif
			        </tr>

			        @if (is_numeric(str_replace(',', '', $paritem->item_cost)))
			            @php $rowCount++; @endphp
			        @endif
			    @endforeach
					<tr>
						<td height="13"></td>
						<td></td>
						<td></td>
						<td></td>
						@if($locationcolumn == 1)
							<td></td>
						@endif
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td height="13"></td>
						<td></td>
						<td></td>
						<td></td>
						@if($locationcolumn == 1)
							<td></td>
						@endif
						<td></td>
						<td></td>
						<td></td>
					</tr>
			    	<tr>
			            <td height="13"></td>
			            <td></td>
			            <td></td>
			            <td></td>
						@if($locationcolumn == 1)
							<td></td>
						@endif
						<td></td>
			            <td style="text-align: right">Grand Total:</td>
			            <td style="text-align: right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
			        </tr>
				<tr>
			    	<td colspan="{{ $locationcolumn == 1 ? 4 : 3 }}" style="text-align: right"><b class="text-total">Supplier:</b></td>
			    	<td colspan="4" style="text-align: left"><b class="text-total"></b></td>
			    </tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4" class="sign" style="text-align: center;">
						<span class="text-receivedby" style="float: left">Received by:</span><br>
						 <span class="footer-cell">
							<span class="footer-cell-sign" style="text-decoration: underline;">
								<b>{{ (isset($paritems[0]->person_accnt_id)) ? strtoupper($paritems[0]->person_accnt) : strtoupper($paritems[0]->person_accnt_name); }}
							</span><br>
							<span class="footer-cell-text">Signature Over Printed Name</span><br><br>

							@if($pAccountable2 != 0)
								<span class="footer-cell-sign" style="text-decoration: underline;">
									<b>{{ strtoupper($pAccountable2) }}</b>
								</span><br>                                                                                                                                                                                                                              
								<span class="footer-cell-text">End User</span><br><br>
							@endif

							<span class="footer-cell-sign" style="text-decoration: underline;">
								<b>{{ isset($paritems[0]->person_accnt_id)  ? strtoupper($paritems[0]->office_name) : strtoupper($paritems[0]->office_name); }}
							</span><br>
							<span class="footer-cell-text">Positon / Office</span><br><br>

							<span class="footer-cell-sign">____________________</span><br>
							<span class="footer-cell-text">Date</span>
						</span>
					</td>
					<td colspan="{{ $locationcolumn == 1 ? 4 : 3 }}" class="sign" style="text-align: center;">
						<span class="text-receivedby" style="float: left">Issued by:</span><br>
						 <span class="footer-cell">

							<span class="footer-cell-sign"><u><b>MA. SOCORRO T. LLAMAS</u></span><br>
							<span class="footer-cell-text">Signature Over Printed Name</span><br><br>

							<span class="footer-cell-sign" style="text-decoration: underline;">
								<b>Supply Officer / SUPPLY OFFICE
							</span><br>
							<span class="footer-cell-text">Positon / Office</span><br><br>

							<span class="footer-cell-sign"><u><b>{{ \Carbon\Carbon::now()->format('M. j, Y') }}</u></span><br>
							<span class="footer-cell-text">Date</span>
						</span>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</body>
</html>