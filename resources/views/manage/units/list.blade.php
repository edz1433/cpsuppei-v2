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
                                    <th class="col-md-2">No</th>
                                    <th>Unit</th>
                                    <th class="col-md-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($unit as $data)
                                <tr id="tr-{{ $data->id }}" class="{{ $cr === 'unitEdit' ? $data->id == $selectedUnit->id ? 'bg-selectEdit' : '' : ''}}">
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $data->unit_name }}</td>
                                    <td>
                                        <a href="{{ route('unitEdit', $data->id) }}" class="btn btn-info btn-xs btn-edit">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </a>
                                        <button value="{{ $data->id}}" class="btn btn-danger btn-xs unit-delete">
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
                        <i class="fas fa-plus"></i> {{ $cr == 'unitEdit' ? 'Edit' : 'Add'}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $cr == 'unitEdit' ? route('unitUpdate', ['id' => $selectedUnit->id]) : route('unitCreate') }}" class="form-horizontal" method="post" id="addUnit">

                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Unit:</label>
                                    @if ($cr == 'unitEdit')
                                        <input type="hidden" name="id" value="{{ $selectedUnit->id }}">
                                    @endif
                                    <input type="text" name="unit_name" value="{{ $cr === 'unitEdit' ? $selectedUnit->unit_name : '' }}" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" class="form-control">

                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> {{ $cr == 'unitEdit' ? 'Update' : 'Save'}}
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