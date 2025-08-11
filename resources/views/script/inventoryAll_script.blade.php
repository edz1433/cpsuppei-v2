<script>
$(document).ready(function() {
    $('.properties-table').DataTable({
        ajax: "{{ route('getProperties') }}",
        responsive: true,
        lengthChange: true,
        searching: true,
        paging: true,
        columns: [
            {
                data: 'id',
                name: 'id',
                className: 'align-middle',
                orderable: false,
                searchable: false
            },
            { data: 'item_name', className: 'align-middle' },
            {
                data: null,
                className: 'align-middle',
                render: function(data, type, row) {
                    var campus = row.office_name ? '<b>CAMPUS:</b> ' + row.office_name : '';
                    var typeAbbr = row.abbreviation ? '<br><b>TYPE:</b> ' + row.abbreviation : '';
                    var poNumber = row.po_number ? '<br><b>PO NUMBER:</b> ' + row.po_number : '';
                    var propertyCode = row.property_no_generated ? '<br><b>PROPERTY CODE:</b> ' + row.property_no_generated : '';
                    var oldPropertyCode = row.property_no_generated_old ? '<br><b>OLD PROPERTY CODE:</b> ' + row.property_no_generated_old : '';
                    var itemModel = row.item_model ? '<br><b>MODEL:</b> ' + row.item_model : '';
                    var serialNumber = row.serial_number ? '<br><b>SERIAL NUMBER:</b> ' + row.serial_number : '';
                    var description = row.item_descrip ? '<br><br><b>DESCRIPTION:</b><br> ' + row.item_descrip : '';
                    var accountableName = row.accountableName ? '<br><br><b>PERSON ACCOUNTABLE:</b><br> ' + row.accountableName : '';
                    var endUser = row.accountableNames ? '<br><br><b>END USER:</b><br> ' + row.accountableNames : '';

                    return campus + typeAbbr + poNumber + propertyCode + oldPropertyCode + itemModel + serialNumber + description + accountableName + endUser;
                }
            },
            {
                data: 'item_cost',
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    const formatted = Number(data).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    if (row.price_stat === 'Uncertain') {
                        return '<span style="color: red;">' + formatted + '</span>';
                    }
                    return '<span>' + formatted + '</span>';
                }
            },
            { data: 'qty', className: 'text-center align-middle' },
            {
                data: 'total_cost',
                className: 'text-center align-middle',
                render: function(data) {
                    return Number(data).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { data: 'date_acquired', className: 'text-center align-middle' },
            { data: 'remarks', className: 'text-center align-middle' },
            {
                data: 'id',
                className: 'text-center align-middle',
                render: function(data, type, row) {
                    if (type === 'display') {
                        var editUrl = "{{ route('propertiesEdit', ':id') }}".replace(':id', data);
                        var returnSlipUrl = "{{ route('returnSlip', ':id') }}".replace(':id', data);
                        var urole = "{{ auth()->user()->role }}";
                        if (urole !== "Campus Admin") {
                            return `
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="${editUrl}" class="dropdown-item btn-edit"><i class="fas fa-exclamation-circle"></i> Edit</a></li>
                                        <li><button id="${data}" onclick="printSticker(${data})" class="dropdown-item btn-print"><i class="fas fa-print"></i> Sticker</button></li>
                                        <li><button value="${data}" class="dropdown-item inventory-delete"><i class="fas fa-trash"></i> Delete</button></li>
                                        ${row.remarks === "Unserviceable" ? `<li><a href="${returnSlipUrl}" class="dropdown-item"><i class="fas fa-file-alt"></i> Return Slip</a></li>` : ""}
                                    </ul>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                </div>
                            `;
                        }
                    }
                    return data;
                }
            }
        ],
        initComplete: function() {
            var api = this.api();
            api.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        },
        createdRow: function(row, data) {
            $(row).attr('id', 'tr-' + data.id);
            if (data.remarks === "Unserviceable") {
                $('td', row).addClass('un-bg');
            }
        }
    });
});
</script>

<script>
    $(document).ready(function() {
        $('.inventory-table').DataTable({
            "ajax": "{{ route('getInventory') }}",
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'id', name: 'id', orderable: false, searchable: false, className: 'text-center align-middle'},
                {data: 'item_name', className: 'align-middle'},
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<b>CAMPUS:</b> ' + row.office_name + ' <br> <b>PROPERTY CODE</b>: ' + row.property_no_generated + ' <br><br> <b>DESCRIPTION</b><br>' + row.item_descrip;
                    },
                    className: 'align-middle'
                },
                {data: 'item_cost',
                    render: function(data, type, row) {
                        if (row.price_stat === 'Uncertain') {
                            return '<span style="color: red;">' + data + '</span>';
                        } else {
                            return '<span>' + data + '</span>';
                        }
                    },
                    className: 'text-center align-middle'
                },
                {data: 'qty', className: 'text-center align-middle'},
                {data: 'total_cost', className: 'text-center align-middle'},
                {data: 'date_acquired', 
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var date = new Date(data);
                            var options = { year: 'numeric', month: 'long', day: 'numeric' };
                            return date.toLocaleDateString('en-US', options);
                        } else {
                            return data;
                        }
                    },
                    className: 'text-center align-middle'
                },
                {data: 'remarks', className: 'text-center align-middle'},
                {data: 'his_remarks', className: 'text-center align-middle'},
                {data: 'inv_status',
                    render: function(data, type, row) {
                            var badgeClass = data == 1 ? 'badge-warning' : 'badge-success';
                            var statusText = data == 1 ? 'Ongoing' : 'Done';
                            return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
                    },
                    className: 'text-center align-middle'
                }
            ],
            initComplete: function(settings, json) {
                var api = this.api();
                api.column(0, {search: 'applied', order: 'applied'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            },
            "createdRow": function (row, data, dataIndex) {
                $(row).attr('id', 'tr-' + data.id);
                if (data.remarks === "Unserviceable") {
                    $('td', row).addClass('un-bg');
                }
            }
        });
    });
</script>

<script>
    $(document).on('click', '.inventory-delete', function(e){
        var id = $(this).val();
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('propertiesDelete', ":id") }}".replace(':id', id),
                        success: function (response) {  
                        $("#tr-"+id).delay(1000).fadeOut();
                        Swal.fire({
                            title:'Deleted!',
                            text:'Successfully Deleted!',
                            type:'success',
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 1000
                        })
                    }
                });
            }
        })
    });
</script>

