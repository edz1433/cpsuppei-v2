<script>
    $(document).ready(function() {
        $('.properties-table').DataTable({
            "ajax": "{{ route('getProperties') }}",
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'item_name', className: 'align-middle'},
                {
                    data: null,
                    render: function(data, type, row) {
                        var campus = row.office_name ? '<b>CAMPUS:</b> ' + row.office_name : '';
                        var type = row.abbreviation ? '<br><b>TYPE:</b> ' + row.abbreviation : '';
                        var poNumber = row.po_number ? '<br><b>PO NUMBER</b>: ' + row.po_number : '';
                        var propertyCode = row.property_no_generated ? '<br><b>PROPERTY CODE</b>: ' + row.property_no_generated : '';
                        var itemmodel = row.item_model ? '<br><b>MODEL</b> ' + row.item_model : '';
                        var description = row.item_descrip ? '<b>DESCRIPTION:</b><br> ' + row.item_descrip : '';
                        var accountname = row.accountableName ? '<b>PERSON ACCOUNTABLE:</b><br> ' + row.accountableName : '';
                        var accountname1 = row.accountableNames ? '<b>END USER:</b><br> ' + row.accountableNames : '';
                        
                        return campus + ' ' + type + ' ' + poNumber + ' ' + propertyCode + ' ' + itemmodel + '<br><br> ' + description + '<br><br> ' + accountname + '<br><br> ' + accountname1;
                    },
                    className: 'align-middle'
                },
                {data: 'item_cost',className: 'text-center align-middle',
                    render: function(data, type, row) {
                        if (row.price_stat === 'Uncertain') {
                            return '<span style="color: red;">' + data + '</span>';
                        } else {
                            return '<span>' + data + '</span>';
                        }
                    }
                },
                {data: 'qty', className: 'text-center align-middle'},
                {data: 'total_cost', className: 'text-center align-middle'},
                {data: 'date_acquired', className: 'text-center align-middle'},
                {data: 'remarks', className: 'text-center align-middle'},
                {data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var editUrl = "{{ route('propertiesEdit', ['id' => ':id']) }}".replace(':id', data);
                            var returnSlipUrl = "{{ route('returnSlip', ['id' => ':id']) }}".replace(':id', data);
                            var urole = "{{ auth()->user()->role }}";
                            if(urole !== "Campus Admin"){
                                return `
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown"></button>
                                        <div class="dropdown-menu">
                                            <a href="${editUrl}" class="dropdown-item btn-edit" href="#"><i class="fas fa-exclamation-circle"></i> Edit</a>
                                            <button id="${data}" onclick="printSticker(${data})" class="dropdown-item btn-print" href="#"><i class="fas fa-print"></i> Sticker</button>
                                            <button value="${data}" class="dropdown-item inventory-delete" href="#"><i class="fas fa-trash"></i> Delete</button>
                                            ${row.remarks === "Unserviceable" ? `<a href="${returnSlipUrl}" class="dropdown-item"><i class="fas fa-file-alt"></i> Return Slip</a>` : ""}
                                        </div>
                                    </div>
                                `;
                            }else{
                                return `
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown"></button> 
                                    </div>`;
                            }
                        } else {
                            return data;
                        }
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

