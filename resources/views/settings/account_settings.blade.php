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
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please check the account settings form.</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <label for="exampleInputName">First Name:</label>
                                    <input type="text" name="fname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter First Name" value="{{ old('fname', Auth::guard('web')->user()->fname) }}" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Middle Name:</label>
                                    <input type="text" name="mname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Middle Name" value="{{ old('mname', Auth::guard('web')->user()->mname) }}" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Last Name:</label>
                                    <input type="text" name="lname" oninput="this.value = this.value.toUpperCase()" placeholder="Enter Last Name" value="{{ old('lname', Auth::guard('web')->user()->lname) }}" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <label for="exampleInputName">Username:</label>
                                    <input type="text" name="username" placeholder="Enter Username" value="{{ old('username', Auth::guard('web')->user()->username) }}" class="form-control" required minlength="5" autocomplete="username">
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Gender:</label>
                                    <select name="gender" class="form-control" required>
                                        <option disabled selected value=""> --- Select --- </option>
                                        <option value="Male" {{ old('gender', Auth::guard('web')->user()->gender) == 'Male' ? 'selected="selected"' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', Auth::guard('web')->user()->gender) == 'Female' ? 'selected="selected"' : '' }}>Female</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInputName">Role:</label>
                                    <input type="text" value="{{ Auth::guard('web')->user()->role }}" class="form-control" disabled>
                                    <small class="text-muted">Role changes are handled by an administrator.</small>
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
                                    <input type="password" name="password" placeholder="Enter New Password" class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation">Confirm Password:</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm New Password" class="form-control" required minlength="8" autocomplete="new-password">
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
                    <a href="#" class="btn btn-default btn-block btn-muted" >
                        <b>{{ (auth()->user()->role != 'Campus Admin') ? auth()->user()->role : 'Campus Custodian' }}</b>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
