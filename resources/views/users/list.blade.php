@extends('layouts.master')

@section('body')

@php $cr = request()->route()->getName(); @endphp

<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Campus Name</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                @php $no = 1; @endphp
                                @foreach($user as $data)
                                <tr id="tr-{{ $data->uid }}" class="{{ $cr === 'userEdit' ? $data->uid == $selectedUser->uid ? 'bg-selectEdit' : '' : ''}}">
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $data->campus_name }}</td>
                                    <td>{{ $data->lname }}</td>
                                    <td>{{ $data->fname }}</td>
                                    <td>{{ $data->mname }}</td>
                                    <td>{{ $data->username }}</td>
                                    <td>{{ $data->role }}</td>
                                    <td>
                                        <a href="{{ route('userEdit', $data->uid) }}" class="btn btn-info btn-xs btn-edit" data-id="{{ $data->id }}">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </a>
                                        <button value="{{ $data->uid }}" class="btn btn-danger btn-xs users-delete">
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
                    <h3 class="card-title">
                        <i class="fas fa-{{ $cr == 'userEdit' ? 'pen' : 'plus'}}"></i> {{ $cr == 'userEdit' ? 'Edit' : 'Add'}}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ $cr == 'userEdit' ? route('userUpdate', ['id' => $selectedUser->uid]) : route('userCreate') }}" class="form-horizontal add-form-user" method="POST" id="addUser">
                        @csrf
                        @if ($cr == 'userEdit')
                            <input type="hidden" name="id" value="{{ $selectedUser->uid }}">
                        @endif
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Last Name:</label>
                                    <input type="text" name="lname" value="{{ $cr === 'userEdit' ? $selectedUser->lname : '' }}" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Last Name" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">First Name:</label>
                                    <input type="text" name="fname" value="{{ $cr === 'userEdit' ? $selectedUser->fname : '' }}" oninput="this.value = this.value.toUpperCase()" placeholder="Enter First Name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Middle Name:</label>
                                    <input type="text" name="mname" value="{{ $cr === 'userEdit' ? $selectedUser->mname : '' }}" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Middle Name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Username:</label>
                                    <input type="text" name="username" value="{{ $cr === 'userEdit' ? $selectedUser->username : '' }}" placeholder="Enter Username" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Gender:</label>
                                    <select class="form-control select2bs4" name="gender">
                                        <option value=""> --- Select Here --- </option>
                                        <option {{ $cr == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option {{ $cr == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                               </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Password:</label>
                                    <input type="password" name="password" value="{{ $cr === 'userEdit' ? $selectedUser->password : '' }}" placeholder="Enter Password" class="form-control">   
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Campus:</label>
                                    <select class="form-control select2bs4" name="campus_id">
                                        <option value=""> --- Select Here --- </option>
                                        @foreach ($camp as $cp)
                                            <option value="{{ $cp->id }}" @if($cr === 'userEdit' && $cp->id === $selectedUser->campus_id) selected @endif>{{ $cp->campus_name }}</option>
                                        @endforeach
                                    </select>
                               </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Role:</label>
                                    <select class="form-control select_camp" name="role">
                                        <option value=""> --- Select Role --- </option>
                                        <option value="Administrator" @if($cr === 'userEdit' && $selectedUser->role == 'Administrator' ? 'selected' : '' ) selected @endif>Administrator</option>
                                        <option value="Supply Officer" @if($cr === 'userEdit' && $selectedUser->role == 'Supply Officer' ? 'selected' : '' ) selected @endif>Supply Officer</option>
                                        <option value="Campus Admin" @if($cr === 'userEdit' && $selectedUser->role == 'Campus Admin' ? 'selected' : '' ) selected @endif>Campus Admin</option>
                                        <option value="Supply Staff" @if($cr === 'userEdit' && $selectedUser->role == 'Supply Staff' ? 'selected' : '' ) selected @endif>Supply Staff</option>
                                        <option value="Technician" @if($cr === 'userEdit' && $selectedUser->role == 'Technician' ? 'selected' : '' ) selected @endif>Technician</option>
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
                                        <i class="fas fa-save"></i> {{ $cr == 'userEdit' ? 'Update' : 'Save'}}
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