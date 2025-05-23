@extends('layouts.master')

@section('body')

<style>
.hidden {
    display: none;
}
.pba {
    color: #007bff;
}

</style>

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-8">
                            <button type="button" class="btn btn-default btn-sm float-md-right"> Edit</button>                            
                            <a href="{{ route('propertiesRead') }}" class="btn btn-default btn-sm float-md-right pba"> Purchases</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('propertiesUpdate') }}" class="form-horizontal add-form" id="addpurchase" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $inventory->id }}">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="exampleInputName">Office:</label>
                                    <select class="form-control select2bs4" name="office_id" data-placeholder=" ---Select Office--- " style="width: 100%;">
                                        <option value=""> --- Select Office Here --- </option>
                                        @foreach ($office as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedOfficeId ? 'selected' : '' }}>{{ $data->office_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="exampleInputName">Item:</label>
                                    <select class="form-control select2bs4" name="item_id" data-placeholder=" ---Select Item--- " style="width: 100%;">
                                        <option value=""> </option>
                                        @foreach ($item as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedItemId ? 'selected' : '' }}>{{ $data->item_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Item Description:</label>
                                    <textarea class="form-control" rows="2" name="item_descrip">{{ $inventory->item_descrip }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Model:</label>
                                    <textarea class="form-control" rows="2" name="item_model">{{ $inventory->item_model }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="exampleInputName">Serial Number:</label>
                                    <input type="text" name="serial_number" value="{{ $inventory->serial_number }}" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Serial Number" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="exampleInputName">Date Purchase:</label>
                                    <input type="date" name="date_acquired" value="{{ $inventory->date_acquired }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="exampleInputName">Unit:</label>
                                    <select class="form-control select2bs4" name="unit_id" data-placeholder=" ---Select Unit--- " style="width: 100%;">
                                        <option value=""> </option>
                                        @foreach ($unit as $data)
                                            <option value="{{ $data->id }}"  {{ $data->id == $selectedUnitId ? 'selected' : '' }}>{{ $data->unit_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="exampleInputName">Quantity:</label>
                                    <input type="number" name="qty" value="{{ $inventory->qty }}"  class="form-control" onkeyup="calculateTotalCost()">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="exampleInputName">Price:</label>
                                    <input type="text" id="item_cost" name="item_cost" value="{{ $inventory->item_cost }}" onkeyup="formatNumber(this); calculateTotalCost(); toggleSecondForm(this) "  class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="exampleInputName">Total Cost:</label>
                                    <input type="text" name="total_cost" value="{{ $inventory->total_cost }}" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="secondForm">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Property Type:</label>
                                    <select class="form-control" name="properties_id" id="item_id" style="pointer-events: none;" onchange="toggleSecondForm(this)">
                                        @foreach ($property as $data)
                                            <option value="{{ $data->id }}" @if($inventory->properties_id == $data->id) selected @endif>
                                                {{ $data->property_name }} ({{ $data->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="category-div" class="col-md-6 category-column mt-3">
                                    <label>Select Category</label>
                                    <select id="category_id" name="categories_id" onchange="categorEdit(this.value)" data-placeholder="Select Category" class="form-control select2bs4" style="width: 100%;">
                                        <option></option>
                                        @foreach ($category as $data)
                                            <option value="{{ $data->cat_code }}" {{ $data->id == $selectedCatId ? 'selected' : '' }}>
                                                {{ $data->cat_code }} - {{ $data->cat_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="account-div" class="col-md-6 desc-column mt-3">
                                    <label for="exampleInputName">Select Account Title</label>
                                    <select id="account_title" name="property_id" data-placeholder="Select Account Title" class="form-control select2bs4" style="width: 100%;">
                                        @foreach ($property1 as $data)
                                            <option value="{{ $data->code }}" {{ $data->code == $selectedAccId ? 'selected' : '' }}>{{ $data->code }} - {{ $data->account_title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" id="selected_account_id" name="selected_account_id" value="{{ $inventory->selected_account_id }}">

                                <div class="col-md-12 mt-3">
                                    <label>Remarks:</label>
                                    <select class="form-control" name="remarks" id="remarks">
                                        <option value="Good Condition" @if($inventory->remarks == 'Good Condition') selected @endif>Good Condition</option>
                                        <option value="Needing Repair" @if($inventory->remarks == 'Needing Repair') selected @endif>Needing Repair</option>
                                        <option value="Unserviceable" @if($inventory->remarks == 'Unserviceable') selected @endif>Unserviceable</option>
                                        <option value="Obsolete" @if($inventory->remarks == 'Obsolete') selected @endif>Obsolete</option>
                                        <option value="No Longer Needed" @if($inventory->remarks == 'No Longer Needed') selected @endif>No Longer Needed</option>
                                        <option value="Not used since purchase" @if($inventory->remarks == 'Not used since purchase') selected @endif>Not used since purchase</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label>Price Status:</label>
                                    <select class="form-control" name="price_stat" id="price_stat">
                                        <option value="Certain" @if ($inventory->price_stat == 'Certain') selected @endif>Certain</option>
                                        <option value="Uncertain" @if($inventory->price_stat == 'Uncertain') selected @endif>Uncertain</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label>Accountable Person 1:</label>
                                    <select class="form-control select2bs4" name="person_accnt" style="width: 100%;">
                                        <option value="">N/A</option>
                                        @foreach ($accnt as $data)
                                            <option value="{{ $data->id }}" {{ $data->id == $selectedPerson ? 'selected' : '' }}>{{ $data->person_accnt }}</option>
                                        @endforeach
                                    </select> 
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label>Accountable Person 2:</label>
                                    <select class="form-control select2bs4" name="person_accnt1[]" data-placeholder="--- Select Accountable Person 2 ---" style="width: 100%;" multiple>
                                        <option value="0" {{ in_array('0', $selectedPerson1 ?? []) ? 'selected' : '' }}>N/A</option>
                                        @foreach ($accnt as $data)
                                            <option value="{{ $data->id }}" {{ in_array($data->id, $selectedPerson1 ?? []) ? 'selected' : '' }}>
                                                {{ $data->person_accnt }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mt-3">
                                    <label>Item Serial Number Owned:</label>
                                    <select class="form-control select2bs4" name="serial_owned[]" data-placeholder="--- Select Serial Number ---" style="width: 100%;" multiple>
                                        <option value="0" {{ in_array('0', $serialOwned ?? []) ? 'selected' : '' }}>N/A</option>
                                        @php
                                            $serialNumbers = explode(';', $inventory->serial_number); // Assuming serial_number is separated by ;
                                        @endphp
                                        @foreach ($serialNumbers as $serial)
                                            <option value="{{ trim($serial) }}" {{ in_array(trim($serial), $serialOwned ?? []) ? 'selected' : '' }}>
                                                {{ trim($serial) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                                     
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="submit" name="btn-submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                            </div>
                        </div>   
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
function formatNumber(input) {
    const value = input.value.replace(/[^\d.]/g, '');

    const [integerPart, ...decimalParts] = value.split('.');

    const formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    let formattedValue = formattedIntegerPart;
    if (decimalParts.length > 0) {
        const editedDecimalPart = decimalParts.join('.');
        formattedValue += `.${editedDecimalPart}`;
    }

    input.value = formattedValue;
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
function categorEdit(val) {
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

@endsection