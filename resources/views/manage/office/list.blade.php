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
                                    <th>Office Code</th>
                                    <th>Office Name</th>
                                    <th>Abbreviation</th>
                                    <th>Office Head</th>
                                    <th class="text-center" width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($office as $data)
                                <tr id="tr-{{ $data->id }}" class="{{ $cr === 'officeEdit' ? $data->id == $selectedOffice->id ? 'bg-selectEdit' : '' : ''}}">
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $data->office_code }}</td>
                                    <td>{{ $data->office_name }}</td>
                                    <td>{{ $data->office_abbr }}</td>
                                    <td>{{ $data->office_officer }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('officeEdit', $data->id) }}" class="btn btn-info btn-xs btn-edit" data-id="{{ $data->id }}">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </a>
                                        {{-- <button value="{{ $data->id}}" class="btn btn-danger btn-xs office-delete">
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
                        <i class="fas fa-plus"></i> {{ $cr == 'officeEdit' ? 'Edit' : 'Add'}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $cr == 'officeEdit' ? route('officeUpdate', ['id' => $selectedOffice->id]) : route('officeCreate') }}" class="form-horizontal" method="post" id="addoffice">
                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Office Code (3 digits):</label>
                                    @if ($cr == 'officeEdit')
                                        <input type="hidden" name="id" value="{{ $selectedOffice->id }}">
                                    @endif
                                    <input type="number" name="office_code" value="{{ $cr === 'officeEdit' ? $selectedOffice->office_code : '' }}" class="form-control" min="1" max="100" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="3" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Office Name:</label>
                                    <input type="text" name="office_name" value="{{ $cr === 'officeEdit' ? $selectedOffice->office_name : '' }}" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Office Abbreviation:</label>
                                    <input type="text" name="office_abbr" value="{{ $cr === 'officeEdit' ? $selectedOffice->office_abbr : '' }}" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label>Office Director:</label>
                                    <input type="text" name="office_officer" value="{{ $cr === 'officeEdit' ? $selectedOffice->office_officer : '' }}" class="form-control" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <button type="reset" class="btn btn-danger">
                                        <i class="fas fa-rotate-right"></i> Clear
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