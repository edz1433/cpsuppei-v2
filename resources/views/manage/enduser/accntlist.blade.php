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
                                    <th>No</th>
                                    <th>Accountable Person</th>
                                    <th>Campus/Office</th>
                                    <th class="text-center" width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($accnt as $data)
                                <tr id="tr-{{ $data->id }}" class="{{ $cr === 'accountableEdit' ? $data->id == $selectedAccnt->id ? 'bg-selectEdit' : '' : ''}}">
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $data->person_accnt }}</td>
                                    <td>{{ $data->office_name }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('accountableEdit', $data->id) }}" class="btn btn-info btn-xs btn-edit">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </a>
                                        {{-- <button value="{{ $data->id}}" class="btn btn-danger btn-xs accnt-delete">
                                            <i class="fas fa-trash"></i>
                                        </button> --}}
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
                        <i class="fas fa-{{ $cr == 'accountableEdit' ? 'pen' : 'plus'}}"></i> {{ $cr == 'accountableEdit' ? 'Edit' : 'Add'}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $cr == 'accountableEdit' ? route('accountableUpdate', ['id' => $selectedAccnt->id]) : route('accountableCreate') }}" class="form-horizontal" method="post" id="addAccnt">
                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Accountable Person:</label>
                                    @if ($cr == 'accountableEdit')
                                        <input type="hidden" name="id" value="{{ $selectedAccnt->id }}">
                                    @endif
                                    <input type="text" name="person_accnt" value="{{ $cr === 'accountableEdit' ? $selectedAccnt->person_accnt : '' }}" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Campus / Office:</label>
                                    <select class="form-control select2bs4" id="off_id" name="off_id" style="width: 100%;">
                                        <option disabled selected> --- Select here --- </option>
                                        @foreach ($office as $data)
                                            <option value="{{ $data->id }}" @if($cr === 'accountableEdit' && $data->id === $selectedAccnt->off_id) selected @endif>{{ $data->office_abbr }} - {{ $data->office_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> {{ $cr == 'accountableEdit' ? 'Update' : 'Save'}}
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