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
                <div class="card-body">
                    <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">System Name:</label>
                                    <input type="text" name="system_name" oninput="this.value = this.value.toUpperCase()" value="{{ $setting->system_name }}" placeholder="Enter System Name" value="" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="exampleInputName">Upload:</label>
                                    <input type="file" name="photo" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success btn-muted" ><b>Save</b></button>
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
                        @if($setting->photo_filename)
                            <img src="{{ asset('uploads/'. $setting->photo_filename) }}" class="profile-user-img img-fluid img-circle" alt="Uploaded Photo">
                        @else
                            <img class="profile-user-img img-fluid img-circle" src="{{ asset('template/img/default-logo.png') }}" alt="User profile picture">
                        @endif
                    </div>
                    <h3 class="profile-username text-center">System Logo</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection