@if(!request()->is('technician/form/propno*'))
@php
    $current_route=request()->route()->getName();
@endphp

<div class="control-toolbar mt-4">
    <nav class="control-nav" aria-label="Primary navigation">
            <a href="{{ route('dashboard') }}" class="btn btn-app {{$current_route=='dashboard'?'active':''}}" title="Dashboard">
                <i class="fas fa-th mt-1"></i> Dashboard
            </a>
            @if(auth()->user()->role !== 'Campus Admin')
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('ppeRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician', 'Campus Admin'])) disabled @endif {{ request()->is('view*') ? 'active' : '' }}" title="View">
                <i class="fas fa-list mt-1"></i> View
            </a>
            @else 
                <a href="{{ route('accountableRead') }}" class="btn btn-app {{ request()->is('view*') ? 'active' : '' }}" title="View">
                    <i class="fas fa-list mt-1"></i> View
                </a>
            @endif
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('purchaseREAD') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician', 'Campus Admin'])) disabled @endif {{ request()->is('purchases*') ? 'active' : '' }}" title="Purchases">
                <i class="fas fa-shopping-cart mt-1"></i> Purchases
            </a>

            <a href="@if(auth()->user()->role !== 'Technician'){{ route('propertiesRead', 4) }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician'])) disabled @endif {{ request()->is('properties*') ? 'active' : '' }}" title="Properties">
                <i class="fas fa-server mt-1"></i> Properties
            </a>
            
            <a href="@if(auth()->user()->role !== 'Technician'){{ route('inventoryRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician'])) disabled @endif {{ request()->is('inventory*') ? 'active' : '' }}" title="Inventory">
                <i class="fas fa-server mt-1"></i> Inventory
            </a>

            <a href="@if(auth()->user()->role !== 'Technician'){{ route('reportForm') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Technician'])) disabled @endif {{ request()->is('report*') ? 'active' : '' }}" title="Reports">
                <i class="fas fa-file-pdf mt-1"></i> Reports
            </a>

            <a href="@if(auth()->user()->role == 'Technician' || auth()->user()->role == 'Administrator'){{ route('repairRead') }}@endif" class="btn btn-app @if(in_array(auth()->user()->role, ['Campus Admin'])) disabled @endif {{ request()->is('technician*') ? 'active' : '' }}" title="Technician">
                <i class="fas fa-user mt-1"></i> Technician
            </a>

            <a href="{{ route('returnSlip.index') }}" class="btn btn-app {{ request()->is('return-slips*') ? 'active' : '' }}" title="Return Slip">
                <i class="fas fa-clipboard-check mt-1"></i> Return Slip
            </a>
            
            @if(auth()->user()->role=='Administrator')
                <a href="{{ route('userRead') }}" class="btn btn-app {{$current_route=='userRead' || $current_route=='userEdit' ?'active':''}}" title="Users">
                    <i class="fas fa-users mt-1"></i> Users
                </a>
            @endif
            
            @if(auth()->user()->role !== 'Supply Officer')
                <a href="{{ route('user_settings') }}" class="btn btn-app {{ request()->is('settings*') ? 'active' : '' }}" title="Settings">
                    <i class="fas fa-cog mt-1"></i> Settings
                </a>
            @endif
    </nav>
    
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="control-logout">
        @csrf
        <button type="submit" class="btn btn-app mr-2" title="Sign Out">
            <i class="fas fa-sign-out-alt mt-1"></i> Sign Out
        </button>
    </form>
</div>
@endif
