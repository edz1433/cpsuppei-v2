@extends('layouts.master')

@section('body')

<style>
.hidden {
    display: none;
}
.spinner {
    width: 40px;
    height: 40px;
    border: 5px solid #ccc;
    border-top: 5px solid #28a745;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
         <div class="col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: 17pt"></h5>
                    @include('partials.control_propertiesSidebar')
                </div>
            </div>
        </div>
        <div class="col-lg-10">
            <div class="card">
                {{-- <form method="POST" class="m-4" action="{{ route('stickerReadPost') }}" id="campusForm">
                    @csrf
                    <div class="form-group">
                        <select name="camp_id" id="camp_id" class="form-control select2bs4" onchange="document.getElementById('campusForm').submit();">
                            <option value="">-- Select Campus/Office --</option>
                            @foreach ($campoff as $office)
                                <option value="{{ $office->id }}" {{ isset($selectcampoff) && $selectcampoff->id == $office->id ? 'selected' : '' }}>
                                    {{ $office->office_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <div class="card-body">
                    @if(isset($selectcampoff))
                        <iframe src="{{ route('stickerReadPdf', $selectcampoff->id) }}" width="100%" height="510"></iframe>
                    @endif
                </div> --}}

                <div class="form-row m-3">
                    <div class="form-group col-md-3">
                        <label for="rowRange">Select Range</label>
                        <select name="rowRange" id="rowRange" class="form-control select2bs4">
                            <option value="">-- Select Range --</option>
                            <option value="1-1000">1-1000</option>
                            <option value="1001-2000">1001-2000</option>
                            <option value="2001-3000">2001-3000</option>
                            <option value="3001-4000">3001-4000</option>
                            <option value="4001-5000">4001-5000</option>
                            <option value="5001-6000">5001-6000</option>
                            <option value="6001-7000">6001-7000</option>
                            <option value="7001-8000">7001-8000</option>
                        </select>
                    </div>
                    <div class="form-group col-md-9">
                        <label for="campid">Campus/Office</label>
                        <select name="campid" id="campid" class="form-control select2bs4">
                            <option value="">-- Select Campus/Office --</option>
                            @foreach ($campoff as $office)
                                <option value="{{ $office->id }}">
                                    {{ $office->office_name }} - {{ number_format($office->property_count) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="card-body" style="margin-top: -30px;">
                    <button id="printBtn" class="btn btn-success mb-2" disabled><i class="fas fa-print"></i> Print Stickers</button>
                    {{-- <button id="downloadPdfBtn" class="btn btn-danger mb-2" disabled><i class="fas fa-file-pdf"></i> Download PDF</button> --}}
                    <div id="preloader" style="display: none; text-align: center; padding: 2rem;">
                        <div class="spinner"></div>
                        <div style="margin-top: 10px;">Loading stickers... Please wait.</div>
                    </div>
                    <div id="stickerPreviewIframe" style="width: 100%; height: 510px; border: 1px solid #ccc; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    const stickerRouteTemplate = "{{ route('stickerReadJson', ['range' => '__RANGE__', 'campus' => '__CAMPUS__']) }}";
    const logoPath = "{{ asset('uploads/' . $setting->photo_filename) }}";

    let generatedHtml = '';

    $(document).ready(function () {
        function loadStickers() {
            const campusId = $('#campid').val();
            const rowRange = $('#rowRange').val();

            // ✅ Show preloader & disable buttons
            $('#preloader').show();
            $('#printBtn').prop('disabled', true);
            $('#downloadPdfBtn').prop('disabled', true);
            $('#stickerPreviewIframe').empty();

            if (!campusId) {
                $('#preloader').hide(); // ❌ Hide if no campus
                $('#stickerPreviewIframe').html('<div class="no-data">Please select a campus/office.</div>');
                return;
            }

            const url = stickerRouteTemplate
                .replace('__RANGE__', encodeURIComponent(rowRange))
                .replace('__CAMPUS__', encodeURIComponent(campusId));

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    const properties = response.stickers;

                    if (!properties || !properties.length) {
                        $('#preloader').hide(); // ❌ Hide if empty
                        $('#stickerPreviewIframe').html('<div class="no-data text-danger ml-2 mt-1">No sticker data available.</div>');
                        return;
                    }

                    let html = `
                    <div id="printableArea">
                       <style>
                            @media print {
                                @page {
                                    margin: 0;
                                    size: A4;
                                }

                                * {
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }

                                body {
                                    margin: 0;
                                    font-size: 8.35pt;
                                }

                                .page-break {
                                    page-break-after: always;
                                }
                            }

                            body {
                                margin: 0;
                                padding: 0;
                                font-family: 'Bookman Old Style', Georgia, serif;
                                font-size: 8.35pt;
                            }

                            .layout {
                                width: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                                page-break-inside: avoid;
                            }

                            .sticker-cell {
                                width: 50%;
                                height: 280 px;
                                padding: 2px;
                                vertical-align: top;
                                box-sizing: border-box;
                            }

                            .sticker-wrapper {
                                height: 100%;
                                max-height: 280 px;
                                box-sizing: border-box;
                                overflow: hidden;
                                display: flex;
                                flex-direction: column;
                                justify-content: space-between;
                            }

                            #sticker {
                                width: 100%;
                                height: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                                font-size: 8.37pt;
                                color: #000;
                            }

                            #sticker th, #sticker td {
                                border: 1px solid #000;
                                padding: 1px;
                                text-align: left;
                                vertical-align: top;
                                overflow: hidden;
                                white-space: nowrap;
                                text-overflow: ellipsis;
                            }

                            .logo-sticker {
                                width: 30px;
                                height: 30px;
                                text-align: center;
                            }

                            .label-inline {
                                display: flex;
                                align-items: center;
                                gap: 4px;
                                overflow: hidden;
                                white-space: nowrap;
                            }

                            .dataText-inline {
                                flex-grow: 1;
                                display: inline-block;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            }

                            .sticker-text-label {
                                text-align: center;
                            }

                            .bg-yellow {
                                background-color: yellow;
                            }

                            .bg-green {
                                background-color: #008000;
                            }

                            .bg-lightgreen {
                                background-color: #8ceb8c;
                            }

                            .no-data {
                                padding: 1rem;
                                font-family: sans-serif;
                                color: red;
                            }
                        </style>
                    `;

                    const chunks = [];
                    for (let i = 0; i < properties.length; i += 10) {
                        chunks.push(properties.slice(i, i + 10));
                    }

                    chunks.forEach((group, chunkIndex) => {
                        html += '<table class="layout">';
                        for (let r = 0; r < group.length; r += 2) {
                            const row = group.slice(r, r + 2);
                            html += '<tr>';
                            row.forEach(inventory => {
                                const serial = (inventory.serial_number || 'N/A').split(';')[0].trim();
                                const cost = parseFloat(inventory.item_cost || 0);
                                const bgClass = cost < 5000 ? 'bg-lightgreen' : (cost < 50000 ? 'bg-green' : 'bg-yellow');

                                html += `
                                <td class="sticker-cell">
                                    <div class="sticker-wrapper ${bgClass}">
                                        <table id="sticker">
                                            <thead>
                                                <tr>
                                                    <th class="sticker-text-label" style="width: 55px; text-align: center;">
                                                        <img src="${logoPath}" class="logo-sticker">
                                                    </th>
                                                    <th colspan="4" class="sticker-text-label" style="font-size: 12px; text-align: center; vertical-align: middle;">Central Philippines State University</th>
                                                </tr>
                                                <tr>
                                                    <th rowspan="10" class="sticker-text-label" style="text-align: center; vertical-align: middle;"><img src="data:image/png;base64,${inventory.qr_base64}" width="55"></th>
                                                    <th colspan="4"><div class="label-inline"><span class="dataText-inline">Property No.: <i>${inventory.property_no_generated}</i></span></div></th>
                                                </tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Item: <i>${titleCase(inventory.item_name)}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Classification: <i>${titleCase(inventory.account_title_abbr)}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Model/Brand: <i>${inventory.item_model || ''}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Serial No.: <i>${serial}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Acquisition Cost: <i>${cost.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Acquisition Date: <i>${inventory.date_acquired}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Person Accountable: <i>${titleCase(inventory.person_accnt_fname2 || inventory.person_accnt_fname1 || '')}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Assignment: <i>${titleCase(inventory.office_name)}</i></span></div></th></tr>
                                                <tr><th colspan="4"><div class="label-inline"><span class="dataText-inline">Validation Sign:</span></div></th></tr>
                                            </thead>
                                            <tfoot>
                                                <tr><td colspan="5" class="sticker-text-label">*Removing or tampering of this sticker is punishable by Law*</td></tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </td>`;
                            });

                            if (row.length < 2) {
                                html += '<td class="sticker-cell"></td>';
                            }

                            html += '</tr>';
                        }
                        html += '</table>';
                        if (chunkIndex < chunks.length - 1) {
                            html += '<div class="page-break"></div>';
                        }
                    });

                    html += '</div>';

                    $('#stickerPreviewIframe').html(html);
                    generatedHtml = html;

                    // ✅ Re-enable buttons and hide preloader
                    $('#printBtn').prop('disabled', false);
                    $('#downloadPdfBtn').prop('disabled', false);
                    $('#preloader').hide();
                },
                error: function (xhr) {
                    $('#preloader').hide();
                    $('#printBtn').prop('disabled', false);
                    $('#downloadPdfBtn').prop('disabled', false);
                    const message = xhr.responseJSON?.error || xhr.statusText || 'An error occurred while loading data.';
                    $('#stickerPreviewIframe').html(`<div class="no-data"><strong>Error ${xhr.status}:</strong> ${message}</div>`);
                }
            });
        }

        $('#campid').on('change', loadStickers);
        $('#rowRange').on('change', function () {
            if ($('#campid').val()) {
                loadStickers();
            }
        });

        $('#printBtn').on('click', function () {
            const win = window.open('', '_blank');
            win.document.write(`<html><head><title>Print</title></head><body>${generatedHtml}</body></html>`);
            win.document.close();
            win.focus();
            win.print();
        });

        $('#downloadPdfBtn').on('click', function () {
            const element = document.getElementById('stickerPreviewIframe');
            const opt = {
                margin: 0,
                filename: `CPSU-Stickers-${new Date().toISOString().slice(0, 10)}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        });

        function titleCase(str) {
            return str?.toLowerCase().replace(/\b(\w)/g, s => s.toUpperCase()) || '';
        }
    });
</script>



@endsection