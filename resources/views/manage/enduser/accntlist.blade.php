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
                                    <th>{{ auth()->user()->role == "Supply Officer" ? "Accountable Person" : "End User" }}</th>
                                    @if(auth()->user()->role !== "Campus Admin")
                                    <th>
                                        Campus / Office / College
                                    </th>
                                        <th>Other Designated Offices</th>
                                    @endif
                                    <th class="text-center" width="50">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($accnt as $data)
                                    <tr id="tr-{{ $data->id }}" class="{{ $cr === 'accountableEdit' ? $data->id == $selectedAccnt->id ? 'bg-selectEdit' : '' : ''}}">
                                        <td>{{ $no++ }}</td>

                                        {{-- Person with role --}}
                                        <td>
                                            {{ $data->person_accnt }}
                                            @if($data->accnt_role == 1)
                                                - HEAD
                                            @elseif($data->accnt_role == 2)
                                                - CUSTODIAN
                                            @endif
                                        </td>
                                        
                                        @if(auth()->user()->role !== "Campus Admin")
                                        {{-- Main Office --}}
                                        <td>{{ $data->office_name ?? 'N/A' }}</td>

                                        {{-- Other Designated Offices --}}
                                       
                                        <td>
                                            @php
                                                $otherOffices = json_decode($data->desig_offid, true);
                                                $offices = \App\Models\Office::whereIn('id', $otherOffices ?? [])->pluck('office_abbr')->toArray();
                                            @endphp
                                            @if (!empty($offices))
                                                {{ implode(', ', $offices) }}
                                            @else
                                                
                                            @endif
                                        </td>
                                        @endif

                                        {{-- Actions --}}
                                        <td class="text-center">
                                            <a href="{{ route('accountableEdit', $data->id) }}" class="btn btn-info btn-xs btn-edit">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->user()->role == "Supply Officer" || auth()->user()->role == "Administrator")
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
                                    <label>{{ auth()->user()->role !== "Campus Admin" ? "Accountable Person" : "End User" }}:</label>
                                    @if ($cr == 'accountableEdit')
                                        <input type="hidden" name="id" value="{{ $selectedAccnt->id }}">
                                    @endif
                                    <input type="text" name="person_accnt" value="{{ $cr === 'accountableEdit' ? $selectedAccnt->person_accnt : '' }}" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" class="form-control">
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->role == "Supply Officer" || auth()->user()->role == "Administrator")
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <label>Role:</label>
                                        <select class="form-control select2bs4" id="accnt_role" name="accnt_role" style="width: 100%;">
                                            <option value="0" @if($cr === 'accountableEdit' && $selectedAccnt->accnt_role == 0) selected @endif> Staff </option>
                                            <option value="1" @if($cr === 'accountableEdit' && $selectedAccnt->accnt_role == 1) selected @endif> Office Head </option>
                                            <option value="2" @if($cr === 'accountableEdit' && $selectedAccnt->accnt_role == 2) selected @endif> Campus Custodian </option>
                                        </select>
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
                                        <label>Other Designated Office:</label>
                                        <select class="form-control select2bs4" id="desig_offid" name="desig_offid[]" style="width: 100%;" multiple>
                                            <option disabled> --- Select here --- </option>
                                            @foreach ($office as $data)
                                                @if($data->office_code != 0000)
                                                    <option value="{{ $data->id }}"
                                                        @if($cr === 'accountableEdit' && in_array($data->id, (array) json_decode($selectedAccnt->desig_offid))) selected @endif>
                                                        {{ $data->office_abbr }} - {{ $data->office_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
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
        @else
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
                                        <label>{{ auth()->user()->role == "Supply Officer" ? "Accountable Person" : "End User" }}:</label>
                                        @if ($cr == 'accountableEdit')
                                            <input type="hidden" name="id" value="{{ $selectedAccnt->id }}">
                                        @endif
                                        <input type="text" name="person_accnt" value="{{ $cr === 'accountableEdit' ? $selectedAccnt->person_accnt : '' }}" oninput="var words = this.value.split(' '); for(var i = 0; i < words.length; i++){ words[i] = words[i].substr(0,1).toUpperCase() + words[i].substr(1); } this.value = words.join(' ');" class="form-control">
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->role == "Supply Officer" || auth()->user()->role == "Administrator")
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
                            @endif

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
        @endif
    </div>
</div>
@endsection