<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>

	<style>
		#sticker {
		  	font-family: Bookman Old Style, Georgia, serif;
		  	border-collapse: collapse;
		  	width: 100%;
		  	margin-top: 20px;
		  	color: #000;
		}

		#sticker td {
			border: 1px solid #000;
		  	padding: 4px;
		} 
		#sticker th {
		  	border: 1px solid #000;
		  	padding: 4px;
		}

		#sticker tfoot {
		  	border: 2px solid #000;
		  	padding: 4px;
		}

		#sticker th {
		  	padding-top: 1px;
		  	padding-bottom: 1px;
		  	font-size: 10pt;
		}
		.sticker-text-label {
			text-align: center;
		}
		.sticker-label {
			text-align: left;
		}
		.logo-sticker {
            width: 40px;
            float: center;
        }
        .colortableyellow {
        	background-color: yellow;
        }
        .colortablegreen {
        	background-color: #008000;
        }
        .dataText {
        	font-style: italic;
        	font-size: 9pt;
        	text-decoration: underline;
        }
        .dataText1 {
        	font-style: italic;
        	font-size: 9pt;
        }
	</style>
</head>
<body>
	@php
		$serialNumbers = explode(';', $inventory->serial_number);
	@endphp

	@foreach ($serialNumbers as $serial)
	<div class="colortable{{ $inventory->pid }}" style="background-color: {{ $propertiesId == 3 ? 'yellow' : ($propertiesId == 2 ? 'lightgreen' : ($propertiesId == 1 ? 'green' : '')) }}">
		<table id="sticker">
			<thead>
				<tr>
					<th rowspan="1" class="sticker-text-label">
						@if($setting->photo_filename)
		                    <img src="{{ asset('uploads/'. $setting->photo_filename) }}" class="logo-sticker brand-image img-circle elevation-3" alt="Uploaded Photo">
		                @else
		                    <img class="logo-sticker img-circle" src="{{ asset('template/img/default-logo.png') }}" alt="User profile picture">
		                @endif
					</th>
					<th colspan="3" class="sticker-text-label" style="font-size: 11pt">Central Philippines State University</th>
				</tr>
				<tr>
					<th rowspan="10" class="sticker-text-label"><canvas id="qrcode{{ $loop->iteration }}" class="elevation-3"></canvas></th>
					<th><b class="sticker-label">Property No.:</b> <span class="dataText">{{ $inventory->property_no_generated  }}</span class=""></th>
				</tr>
				<tr>
					<th>Item: <span class="dataText">{{ $inventory->item_name  }}</span></th>
				</tr>
				<tr>
					<th>Classification: <span class="dataText">{{ $inventory->account_title_abbr  }}</span></th>
				</tr>
				<tr>
					<th>Model/Brand: <span class="dataText">{{ $inventory->item_model  }}</span></th>
				</tr>
				<tr>
					<th>Serial No.: <span class="dataText">{{ trim($serial) }}</span></th>
				</tr>
				<tr>
					<th>Acquisition Cost: <span class="dataText">{{ $inventory->item_cost  }}</span></th>
				</tr>
				<tr>
					<th>Acquisition Date: <span class="dataText">{{ $inventory->date_acquired  }}</span></th>
				</tr>
				<tr>
					<th>Person Accountable: <span class="dataText">
						@php
					        $accountable = $inventory->person_accnt ?? $inventory->office_officer ?? null;
					        echo is_null($accountable) ? 'N/A' : $accountable;
					    @endphp
					</span></th>
				</tr>
				<tr>
					<th>Assignment: <span class="dataText">{{ $inventory->office_name  }}</span></th>
				</tr>
				<tr>
					<th>Validation Sign: <span class="dataText"></span></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="5" class="sticker-text-label">*Removing or tampering of this sticker is punishable by Law*</td>
				</tr>
			</tbody>
		</table>
	</div>
	@php
    	$qrCodeValue = $inventory->property_no_generated . '-' . trim($serial);
	@endphp

	<script>
	    var inputText = "{{ $inventory->property_no_generated }}";
	    var qr = new QRious({
	        element: document.getElementById("qrcode{{ $loop->iteration }}"),
	        value: inputText,
	        size: 90,
	    });
	</script>
	@endforeach



<script>
    var inputText= "{{ $inventory->property_no_generated  }}";
    var qr = new QRious({
        element: document.getElementById("qrcode1"),
        value: inputText,
        size: 90,
    });
</script>


<script>

    function triggerDownload(link) {
        const event = new MouseEvent('click', {
            view: window,
            bubbles: true,
            cancelable: true
        });
        link.dispatchEvent(event);
    }

    function getBackgroundColor() {
        return "{{ $propertiesId == 3 ? 'yellow' : ($propertiesId == 2 ? 'lightgreen' : ($propertiesId == 1 ? 'green' : '')) }}";
    }

    
	let downloadInProgress = false;

$('#downloadStickerButton').click(function() {
    if (downloadInProgress) {
        // If a download is already in progress, prevent starting a new one
        return;
    }

    var id = $(this).val();
    const colortables = document.querySelectorAll('.colortable' + id);

    colortables.forEach((colortable, index) => {
        const contentToCapture = colortable;
        const originalBackgroundColor = window.getComputedStyle(contentToCapture).backgroundColor;
        const backgroundColor = originalBackgroundColor;

        contentToCapture.style.backgroundColor = backgroundColor;

        const downloadLink = document.createElement('a');

        let downloadCanceled = false;

        html2canvas(contentToCapture).then(function(canvas) {
            if (downloadCanceled) {
                contentToCapture.style.backgroundColor = originalBackgroundColor;
                return;
            }

            const imageData = canvas.toDataURL('image/png');
            contentToCapture.style.backgroundColor = originalBackgroundColor;

            downloadLink.href = imageData;
            downloadLink.download = 'sticker_' + (index + 1) + '.png';
            downloadLink.setAttribute('data-cache-control', 'no-cache');

            triggerDownload(downloadLink);

            downloadInProgress = false;
            // You can trigger the next download here if needed
        });
    });
    downloadInProgress = true;
});


</script>



</body>
</html>