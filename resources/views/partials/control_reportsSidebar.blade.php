@php
    $curr_route = request()->route()->getName();
    $rpcppeActive = in_array($curr_route, ['rpcppeOption']) ? 'active' : '';
    $rpcsepActive = in_array($curr_route, ['rpcsepOption']) ? 'active' : '';
    $icsActive = in_array($curr_route, ['icsOption']) ? 'active' : '';
    $parActive = in_array($curr_route, ['parOption']) ? 'active' : '';
    $unservActive = in_array($curr_route, ['unserviceForm']) ? 'active' : '';
@endphp

<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('rpcppeOption') }}" class="nav-link2 {{ $rpcppeActive }}" style="color: #000;">
            RPCPPE Reports
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('rpcsepOption') }}" class="nav-link2 {{ $rpcsepActive }}" style="color: #000;">
            RPCSEP Reports
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('icsOption') }}" class="nav-link2 {{ $icsActive }}" style="color: #000;">
            ICS Reports
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('parOption') }}" class="nav-link2 {{ $parActive }}" style="color: #000;">
            PAR Reports
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('unserviceForm') }}" class="nav-link2 {{ $unservActive }}" style="color: #000;">
            Unserviceable Reports
        </a>
    </li>
    
</ul>

