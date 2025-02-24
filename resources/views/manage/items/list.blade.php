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
                    <div class="container mb-3" style="margin-left: 58%;">
                        <form action="" method="GET" >
                            <div class="row">
                        
                                <div class="col-4">
                                    <select class="form-control form-control-sm" name="off">
                                        <option value="">All</option>
                                        @foreach($office as $off)
                                            <option value="{{ $off->id }}" @if(request('off') == $off->id) selected @endif>{{ $off->office_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                        
                                <div class="col-4">
                                    <button type="submit" class="btn btn-secondary btn-sm">Filter <i class="fas fa-filter fa-sm"></i> </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Item Name</th>
                                    <th>Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($item as $data)
                                    <tr id="tr-{{ $data->id }}" class="{{ $cr === 'itemEdit' ? $data->id == $selectedItem->id ? 'bg-selectEdit' : '' : ''}}">
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $data->item_name }}</td>
                                        <td>
                                            @if(isset($inventoryCount[$data->id]))
                                                 {{ $inventoryCount[$data->id] }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('itemEdit', $data->id) }}?off={{ request('off') }}" class="btn btn-info btn-xs btn-edit">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </a>
                                            <button value="{{ $data->id }}" class="btn btn-danger btn-xs item-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-plus"></i> {{ $cr == 'itemEdit' ? 'Edit' : 'Add'}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $cr == 'itemEdit' ? route('itemUpdate', ['id' => $selectedItem->id]) : route('itemCreate') }}" class="form-horizontal" method="post" id="addItem">
                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Item:</label>
                                    @if ($cr == 'itemEdit')
                                        <input type="hidden" name="id" value="{{ $selectedItem->id }}">
                                    @endif
                                    <input type="text" name="item_name" value="{{ $cr === 'itemEdit' ? $selectedItem->item_name : '' }}" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> {{ $cr == 'itemEdit' ? 'Update' : 'Save'}}
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
@endsection