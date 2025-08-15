@extends('layouts.master')

@section('body')

@php $cr = request()->route()->getName(); @endphp

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: 17pt"></h5>
                    @include('partials.control_reportsSidebar')
                </div>
            </div>
        </div>
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-pdf"></i> Reports Option
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('reportOptionView') }}" class="form-horizontal add-form" id="rpcppeReport" method="POST" target="_blank">
                        @csrf
                        @if(auth()->user()->role !== 'Campus Admin')
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label>Campus or Office:</label>
                                    <select class="form-control select2bs4" id="office_id" name="office_id" style="width: 100%;">
                                        <option disabled selected value=""> ---Select Campus or Office Type--- </option>
                                        <option value="All">All</option>
                                        @foreach ($office as $data)
                                            <option value="{{ $data->id }}">{{ $data->office_abbr }} - {{ $data->office_name }}</option>
                                        @endforeach
                                    </select>   
                                </div>
                                <div class="col-md-6">
                                    <label>Location:</label>
                                    <select class="form-control select2bs4" id="location" name="location" style="width: 100%;">
                                        <option disabled selected value=""> --- Select Location --- </option>
                                        <option value="All" selected>All</option>
                                        <option value="null">Office/Campus</option>
                                        @foreach ($office as $data)
                                            <option value="{{ $data->id }}">{{ $data->office_abbr }} - {{ $data->office_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @else
                            <input type="text" name="office_id" id="office_id" value="{{ $uoffice->id }}" hidden>
                        @endif
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label>Property Type:</label>
                                    <input type="number" name="repcat" value="{{ $repcat }}" hidden>
                                    <select class="form-control select2bs4" id="properties_id" name="properties_id[]" {{ isset($repcat) && $repcat != 1 ? 'multiple' : '' }} style="width: 100%;">
                                        @foreach ($property as $data)
                                            @if($repcat == 1 && $data->id == 3)
                                                <option value="{{ $data->id }}">{{ $data->abbreviation }} - {{ $data->property_name }}</option>
                                            @endif
                                            @if($repcat == 2 && in_array($data->id, [1, 2]))
                                                <option value="{{ $data->id }}" selected>{{ $data->abbreviation }} - {{ $data->property_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label>Category:</label>
                                    <select id="category_id" name="categories_id" onchange="categor(this.value)" data-placeholder="---Select Category---" class="form-control select2bs4" style="width: 100%;">
                                        <option></option>
                                        
                                        <option value="All">All</option>
                                        @foreach ($category as $data)
                                            <option value="{{ $data->cat_code }}">
                                                {{ $data->cat_code }} - {{ $data->cat_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6" id="account-div">
                                    <label>Account Title:</label>
                                    <select id="account_title" name="property_id" data-placeholder="---Select Account Title---" class="form-control select2bs4" style="width: 100%;">
                                    </select>
                                </div>
                                <input type="hidden" id="selected_account_id" name="selected_account_id">
                                <div class="col-md-6">
                                    <label>Date Range:</label>
                                    <div class="input-group">
                                        <div class="sdate col-md-6">
                                            <input type="date" name="start_date_acquired" class="form-control" placeholder="Start Date">
                                        </div>
                                        <div class="edate col-md-6">
                                            <input type="date" name="end_date_acquired" class="form-control" placeholder="End Date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-row">
                                 @if(auth()->user()->role == 'Campus Admin')
                                    <div class="col-md-6">
                                        <label>Location:</label>
                                        <select class="form-control select2bs4" id="location" name="location" style="width: 100%;">
                                            <option disabled selected value=""> ---Select Campus or Office Type--- </option>
                                            <option value="" selected>N/A</option>
                                            <option value="All">All</option>
                                            @foreach ($office as $data)
                                                @if ($data->office_code == '0000')
                                                    <option value="{{ $data->id }}">{{ $data->office_abbr }} - {{ $data->office_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="file_type" value="PDF">
                                    </div>
                                @endif  
                                
                                @if(auth()->user()->role !== 'Campus Admin')
                                    <div class="col-md-6">
                                        <label>File Type:</label>
                                        <select class="form-control" id="file_type" name="file_type" style="width: 100%;">
                                            <option value="PDF">PDF</option>
                                            <option value="EXCEL">EXCEL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label>Serial Column:</label>
                                        <input type="checkbox" name="serial" class="form-control" value="1">
                                    </div>
                                    <div class="col-md-1">
                                        <label>Date Acquired:</label>
                                        <input type="checkbox" name="acquired" class="form-control" value="1">
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="reset" class="btn btn-danger" data-dismiss="modal">
                                        Reset
                                    </button>
                                    <button type="submit" name="btn-submit" class="btn btn-primary">
                                        <i class="fas fa-file-pdf"></i> Generate
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
function categor(val) {
    var categoryId = val;
    var propertyId = $("#property_id").val();
    
    var modeval;
    if (propertyId === '2') {
        modeval = 2;
    } else if (propertyId === '3') {
        modeval = 3;
    } else if (propertyId === '1') {
        modeval = 1;
    } else {
        modeval = 3; 
    }

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
        });
        $("#account_title").on("change", function() {
            var selectedOption = $(this).find(':selected');
            var selectedAccountId = selectedOption.val();
            var selectedAccountCode = selectedOption.data('account-id');
            $("#selected_account_id").val(selectedAccountCode);
        });
    }
}
</script>



@endsection