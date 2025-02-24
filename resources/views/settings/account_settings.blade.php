@extends('layouts.master')

@section('body')
<div class="container-fluid">
    <div class="row" style="padding-top: 100px;">
        <div class="col-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: 17pt"></h5>
                    @include('partials.control_settingsSidebar')
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Account Information</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('profileUpdate') }}" method="post" id="addEmp">
                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <label for="exampleInputName">First Name:</label>
                                    <input type="text" name="fname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter First Name" value="{{ Auth::guard('web')->user()->fname}}" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Middle Name:</label>
                                    <input type="text" name="mname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Middle Name" value="{{ Auth::guard('web')->user()->mname}}" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Last Name:</label>
                                    <input type="text" name="lname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Last Name" value="{{ Auth::guard('web')->user()->lname}}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <label for="exampleInputName">Username:</label>
                                    <input type="text" name="username" placeholder="Enter Username" value="{{ Auth::guard('web')->user()->username}}" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Gender:</label>
                                    <select name="gender" class="form-control">
                                        <option disabled selected value=""> --- Select --- </option>
                                        <option value="Male" {{Auth::guard('web')->user()->gender == 'Male' ? 'selected="selected"' : '' }}>Male</option>
                                        <option value="Female" {{Auth::guard('web')->user()->gender == 'Female' ? 'selected="selected"' : '' }}>Female</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Role:</label>
                                    <select name="role" class="form-control" style="pointer-events: none;">
                                        <option disabled selected value=""> --- Select --- </option>
                                        @if(Auth::user()->role=='Administrator')
                                        <option value="Administrator" {{Auth::guard('web')->user()->role == 'Administrator' ? 'selected="selected"' : '' }}>Administrator</option>
                                        @endif
                                        <option value="Supply Officer" {{Auth::guard('web')->user()->role == 'Supply Officer' ? 'selected="selected"' : '' }}>Supply Officer</option>
                                        <option value="Campus Admin" {{Auth::guard('web')->user()->role == 'Campus Admin' ? 'selected="selected"' : '' }}>Campus Admin</option>
                                        <option value="Staff" {{Auth::guard('web')->user()->role == 'Staff' ? 'selected="selected"' : '' }}>Staff</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>   
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Update Password</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('profilePassUpdate') }}" method="post" id="updatePass">
                        @csrf
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="exampleInputName">Password:</label>
                                    <input type="text" name="password" placeholder="Enter Password" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </div>   
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                        src="{{ asset('template/img/user.png') }}"
                        alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{auth()->user()->fname}}</h3>
                    <a href="#" class="btn btn-default btn-block btn-muted" ><b>{{auth()->user()->role}}</b></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection