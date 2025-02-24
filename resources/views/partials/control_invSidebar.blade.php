@php 
   $curr_route = request()->route()->getName();
@endphp
<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead') }}" class="nav-link2 {{$curr_route=='propertiesRead'?'active':''}}" style="color: #000;">
            Add Inventory
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('inventory_RPCPPEreports') }}" class="nav-link2 {{$curr_route=='inventory_RPCPPEreports'?'active':''}}" style="color: #000;">
            Reports
        </a>
    </li>
</ul>