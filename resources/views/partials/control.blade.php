@if(!request()->is('technician/form/propno*'))
@php
    $current_route=request()->route()->getName();
@endphp

<div class="row pt-2 bg-gray rounded">
    <div class="col-sm-10">
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-app {{$current_route=='dashboard'?'active':''}}">
                <i class="fas fa-th"></i> Dashboard
            </a>
            @if(auth()->user()->role !== 'Campus Admin')
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('ppeRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician', 'Campus Admin'])) disabled @endif {{ request()->is('view*') ? 'active' : '' }}">
                <i class="fas fa-list"></i> View
            </a>
            @else 
                <a href="{{ route('accountableRead') }}" class="btn btn-app {{ request()->is('view*') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> View
                </a>
            @endif
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('purchaseREAD') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician', 'Campus Admin'])) disabled @endif {{ request()->is('purchases*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> Purchases
            </a>

            <a href="@if(auth()->user()->role !== 'Technician'){{ route('propertiesRead', 4) }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician'])) disabled @endif {{ request()->is('properties*') ? 'active' : '' }}">
                <i class="fas fa-server"></i> Properties
            </a>
            
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('inventoryRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician'])) disabled @endif {{ request()->is('inventory*') ? 'active' : '' }}">
                <i class="fas fa-server"></i> Inventory
            </a>

            <a href="@if(auth()->user()->role !== 'Technician'){{ route('reportOption', 1) }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician', 'Campus Admin'])) disabled @endif {{ request()->is('reports*') ? 'active' : '' }}">
                <i class="fas fa-file-pdf"></i> Reports
            </a>

            <a href="@if(auth()->user()->role == 'Technician' || auth()->user()->role == 'Administrator'){{ route('repairRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Campus Admin'])) disabled @endif {{ request()->is('technician*') ? 'active' : '' }}">
                <i class="fas fa-user"></i> Technician
            </a>
            
            @if(auth()->user()->role=='Administrator')
                <a href="{{ route('userRead') }}" class="btn btn-app {{$current_route=='userRead' || $current_route=='userEdit' ?'active':''}}">
                    <i class="fas fa-users"></i> Users
                </a>
            @endif
            
            <a href="{{ route('user_settings') }}" class="btn btn-app {{ request()->is('settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>
    
    <div class="col-sm-2" style="text-align: right;" >
        <div>
            <a href="{{ route('logout') }}" class="btn btn-app pull-right">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </div>
</div>
@endif