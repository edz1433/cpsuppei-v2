<script>
    $(document).ready(function() {
        $('.purchase-table').DataTable({
            "ajax": "{{ route('getPurchase') }}",
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'abbreviation'},
                {data: 'po_number'},
                {data: 'item_model'},
                {data: 'item_name'},
                {data: 'item_descrip',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 8ch;">' + data + '</div>';
                        } else {
                            return data;
                        }
                    }
                },
                {data: 'serial_number',
                    render: function(data, type, row) {
                        if (type === 'display' && data !== null) {
                            return '<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 8ch;">' + data + '</div>';
                        } else {
                            return data;
                        }
                    }
                },
                {data: 'item_cost',
                    render: function(data, type, row) {
                        if (row.price_stat === 'Uncertain') {
                            return '<span style="color: red;">' + data + '</span>';
                        } else {
                            return '<span>' + data + '</span>';
                        }
                    }
                },
                {data: 'qty'},
                {data: 'qty_release'},
                {data: 'total_cost'},
                {data: 'date_acquired'},
                {data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var editUrl = "{{ route('propertiesEdit', ['id' => ':id']) }}".replace(':id', data);
                            return `
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown"></button>
                                    <div class="dropdown-menu">
                                        <button id="${data}" class="dropdown-item" onclick="purchaseReleaseGet(${data})" data-toggle="modal" data-target="#modal-release"><i class="fas fa-paper-plane"></i> Release</button>
                                        <button value="${data}" class="dropdown-item purchaserel-delete" href="#"><i class="fas fa-trash"></i> Delete</button>
                                    </div>
                                </div>
                            `;
                        } else {
                            return data;
                        }
                    }
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
            }
        });
    });
</script>

<script>
    function purchaseReleaseGet(dataid){
        $.ajax({
            type: "GET",
            url: "{{ route('purchaseReleaseGet', ':id') }}".replace(':id', dataid),
            success: function (data) {
                // alert(data);
                $('#rel_item_name').val(data.purchase.item_name);
                $('#rel_po_number').val(data.purchase.po_number);
                $('#purchase_id').val(dataid);
                $('#rel_qty').attr('max', data.qty_left);
                $('#qty-left').html('LEFT :'+ data.qty_left);
                $('#png').val(data.pcode);
                $('#property_no_generated').val(data.pcode);
                $('#itemnum').val(data.itemnum);
                $('#unrel_serial').html(data.unrel_serial);
            }
        });
    }
</script>

<script>
    function releasOffice(selectElement){
        var png = $('#png').val();
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var officeCode = selectedOption.getAttribute('data-officecode');
        
        $('#property_no_generated').val(png+'-'+officeCode)
    }
</script>

<script>
    $(document).on('click', '.purchaserel-delete', function(e){
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
                    url: "{{ route('purchaseRelDel', ":id") }}".replace(':id', id),
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