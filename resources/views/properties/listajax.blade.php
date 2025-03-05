@extends('layouts.master')

@section('body')

<style>
.hidden {
    display: none;
}
.dropdown-item:hover {
    background-color: #06601f;
    color: #fff;
}
.un-bg {
    background-color: #f8d7da;
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
                <div class="card-header">
                    @if(auth()->user()->role !=='Campus Admin')
                    <h3 class="card-title" style="float: right;">
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-inventory">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                    </h3>
                    @endif
                </div>
                
                <!-- Modal -->
                @include('properties.modal')
                @include('properties.modal-prntSticker')
                <!-- /End Modal -->
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example" class="table table-bordered table-hover properties-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="250">ITEM NAME</th>
                                    <th width="400">DETAILS</th>
                                    <th>PRICE</th>
                                    <th>QTY</th>
                                    <th>TOTAL COST</th>
                                    <th>DATE ACQ.</th>
                                    <th>ITEM STATUS</th>
                                    <th>ACTION</th> 
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function formatNumber(input) {
    const value = input.value.replace(/[^\d.]/g, '');
    const formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    input.value = formattedValue;
}
</script>
<script>
    function updateHiddenInput(selectElement) {
        var selectedValue = selectElement.value;
        document.getElementById('selected_category_id').value = selectedValue;
    }
</script>
<script>
function calculateTotalCost() {
    const qtyInput = document.getElementsByName('qty')[0];
    const itemCostInput = document.getElementsByName('item_cost')[0];
    const qty = parseFloat(qtyInput.value) || 0;
    const itemCost = parseFloat(itemCostInput.value.replace(/[^\d.]/g, '')) || 0;
    const totalCost = qty * itemCost;
    const formattedTotalCost = totalCost.toLocaleString();
    document.getElementsByName('total_cost')[0].value = formattedTotalCost;
}
</script>

<script>
function toggleSecondForm(selectElement) {
    const secondForm = document.getElementById('secondForm');
    const price = parseFloat(document.getElementsByName('item_cost')[0].value.replace(/[^\d.]/g, '')) || 0;
    const itemIdSelect = document.getElementById('item_id');

    if (price >= 10 && price <= 15000) {
        itemIdSelect.value = 2;
    } else if (price >= 15001 && price <= 49000) {
        itemIdSelect.value = 1;
    } else if (price >= 50000) {
        itemIdSelect.value = 3;
    }
    secondForm.style.display = 'block';
}
</script>

<script>
function categor(val) {
    var categoryId = val;
    var price = $("#item_cost").val().replace(/,/g, '');
    
    var modeval = (price <= 49000) ? 2 : 3;
    var urlTemplate = "{{ route('propertiesCat', [':id', ':mode']) }}";
    var url = urlTemplate.replace(':id', categoryId).replace(':mode', modeval);
    
    if (categoryId) {
        $.ajax({
            url: url,
            type: "GET",
            success: function(response) {
                console.log(response);
                $('#account_title').empty();
                $('#account_title').append("<option value=''></option>");
                $('#account_title').append(response.options);
            }
        })
        $("#account_title").on("change", function() {
            var selectedOption = $(this).find(':selected');
            var selectedAccountId = selectedOption.val(); // Get the account ID directly from the selected option's value
            var selectedAccountCode = selectedOption.data('account-id'); // Get the account code from the data attribute
            $("#selected_account_id").val(selectedAccountCode); // Set account ID to input field
            // Optionally, do something with the selected account code
        });
    }
};
</script>

<script>
    function printSticker(purchase_id) {
        $.ajax({
            url: "{{ route('propertiesPrntSticker', ':id') }}".replace(':id', purchase_id),
            method: 'GET',
            success: function(response) {
                
                $('#modal-prntSticker .modal-body').html(response);
                
                $('#modal-prntSticker').modal('show');  

                console.log(response);

                $(".downloadStickerButton").each(function() {
                    $(this).val(purchase_id);
                });
            }
        });
    }
</script>

<script>
    $(document).ready(function() {
        $('#accountableSelect').change(function() {
            var selectedValue = $(this).val();
            if (!selectedValue) {
                var officeId = $('#officeSelect option:selected').data('office-id');
                $('#officeSelect').val(officeId).trigger('change.select2');
            }
        });
    });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

@endsection