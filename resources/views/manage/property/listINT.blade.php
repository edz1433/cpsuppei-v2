@extends('layouts.master')

@section('body')

@php $cr = request()->route()->getName(); @endphp

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: 17pt"></h5>
                    @include('partials.control_viewSidebar')
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="10" class="align-center">No.</th>
                                    <th>Category</th>
                                    <th>Account Title</th>
                                    <th>Account Number</th>
                                    <th>Account Title Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php $no = 1; @endphp
                            @foreach ($properties as $proint)
                            <tr id="tr-{{ $proint->id  }}" class="{{ $cr === 'intEdit' ? $proint->id == $intProperties->id ? 'bg-selectEdit' : '' : ''}}">
                                <td>{{ $no++ }}</td>
                                <td>{{ $proint->cat_name }}</td>
                                <td>{{ $proint->account_title }}</td>
                                <td>{{ $proint->account_number }}</td>
                                <td>{{ $proint->account_title_abbr }}</td>
                                <td>
                                    <a href="{{ route('intEdit', $proint->id) }}" class="btn btn-info btn-xs"><i class="fas fa-info-circle"></i></a>
                                    <button value="{{ $proint->id }}" class="btn btn-danger btn-xs property-delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-plus"></i>{{ $cr == 'intEdit' ? 'Edit' : 'Add'}}
                    </h5>
                </div>
                <div class="card-body">
                    <form id="intForm" action="{{ $cr == 'intEdit' ? route('intUpdate') : route('intCreate') }}" method="POST">
                        @csrf
                        <!-- /.card-body -->
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="">Categories </label>
                                    @if ($cr == 'intEdit')
                                        <input type="hidden" name="id" value="{{ $intProperties->id }}">
                                    @endif
                                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                                    <select id="category" name="category_id" class="form-control select2bs4" data-placeholder="Select Category" style="width: 100%;" onchange="concatenateValue(this)">
                                        <option value="">-- Select --</option>
                                        @foreach ($categories as $cat)
                                        <option value="{{ $cat->cat_code }}" @if($cr === 'intEdit' && $cat->cat_code === $intProperties->category_id) selected @endif>
                                            {{ $cat->cat_code }} {{ $cat->cat_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="">Account Number : <span class="text-muted">0-00-00-000</span></label>
                                    <input type="text" id="account_number" name="account_number" pattern="[0-9]-[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9]" @if($cr == 'intEdit') value="{{ $intProperties->account_number }}" @endif value="{{ $property->default_code }}-{{ $property->property_code }}-" class="form-control" style="text-transform: capitalize;" placeholder="Enter Category Name" onkeyup="extractLastThree()" oninput="updateInputValue(this, '{{ $property->default_code }}-{{ $property->property_code }}-')">
                                    <input type="hidden" id="code" name="code" value="{{ $cr == 'intEdit' ? $intProperties->code : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="">Account title</label>
                                    <input type="text" id="account_title" name="account_title" value="{{ $cr == 'intEdit' ? $intProperties->account_title : '' }}" class="form-control" style="text-transform: capitalize;" placeholder="Enter Account Title">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="">Account title type</label>
                                    <input type="text" id="account_title_abbr" name="account_title_abbr" value="{{ $cr == 'intEdit' ? $intProperties->account_title_abbr : '' }}" class="form-control" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Account Title Type">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> {{ $cr == 'intEdit' ? 'Update' : 'Save'}}
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
    function extractLastThree() {
        var inputString = document.getElementById("account_number").value;
        var lastThreeDigits = inputString.split("-").pop();
        document.getElementById("code").value = lastThreeDigits;
    }
</script>
@endsection