<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<title>PRE REPAIR INSPECTION REPORT</title>
    <!-- Logo  -->
    <link rel="shortcut icon" href="{{ asset('template/img/CPSU_L.png') }}">
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }

    header {
        position: fixed;
        top: -10px;
        left: 0px;
        right: 0px;
        height: 150px;
        /* background-color: lightblue; */
        text-align: center;
    }

    header img {
        width: 65%;
        height: auto;
    }

    .b{
        font-weight: bold;
    }

    .f{
        font-size: 16px;
    }

    .f1{
        font-size: 14px;
    }

    .mt-10{
        margin-top: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th{
        text-align: center;
    }

    td, th {
        border: 1px solid #000;
        padding: 4px;
        height: 22px;
    }
</style>
<body>
	<header>
		<img src="{{ asset('uploads/pre-repair-header.png') }}">
        <div class="b f1 mt-10">PRE-REPAIR INSPECTION REPORT</div>
	</header>
    <div style="margin-top: 95px; padding: 0 30px;">
        <p class="f1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I hereby certify that, as the TECHNICAL SUPPORT of CENTRAL PHILIPPINES STATE UNIVERSITY, I carefully examined the University's ICT equipment listed below.</p>
        <p class="f1">Finding/s</p>
        
        <div style="display:flex; flex-direction:column; gap:20px; margin-top:10px;">
            <span style="display:block; width:100%; border-bottom:1px solid #000; padding-bottom:1px;"><span style="color: white;">. {{ $repair->findings }}</span></span>
            <span style="display:block; width:100%; border-bottom:1px solid #000; padding-bottom:1px;"><span style="color: white;">.</span></span>
            <span style="display:block; width:100%; border-bottom:1px solid #000; padding-bottom:1px;"><span style="color: white;">.</span></span>
        </div>

        <p class="f1 mt-10">The ICT equipment requirements are as follows:</p>
        <table>
            <thead>
                <th>Unit/Items</th>
                <th>Specifications</th>
                <th>Quantity</th>
                <th>No. of Repairs conducted</th>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:20px;">
            <div class="f1">Action to be taken</div>
            <div style="display:flex; flex-direction:column; gap:8px; margin-top:6px; padding-left:6px;">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" style="width:14px; height:16.5px;" /> <span class="f1">For Further Evaluation</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" style="width:14px; height:16.5px;" /> <span class="f1">For Replacement</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" style="width:14px; height:16.5px;" /> <span class="f1">For Repair</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" style="width:14px; height:16.5px;" /> <span class="f1">For Job Out</span>
            </label>
            </div>
        </div>

        <div style="margin-top:10px;">
            <p class="f1">Inspected and Submitted:</p>
        </div>
    </div>
</body>
</html>