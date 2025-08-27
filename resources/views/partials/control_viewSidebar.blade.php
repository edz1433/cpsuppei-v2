@php
    $curr_route = request()->route()->getName();
    $param = request()->route()?->parameter('code'); // safe access to parameter

    $ppeActive = in_array($curr_route, ['ppeRead', 'ppeEdit']) ? 'active' : '';
    $lvActive = in_array($curr_route, ['lvRead', 'lvEdit']) ? 'active' : '';
    $hvActive = in_array($curr_route, ['hvRead', 'hvEdit']) ? 'active' : '';
    $intActive = in_array($curr_route, ['intRead', 'intEdit']) ? 'active' : '';
    $unitActive = in_array($curr_route, ['unitRead', 'unitEdit']) ? 'active' : '';
    $itemtActive = in_array($curr_route, ['itemRead', 'itemEdit']) ? 'active' : '';
    $accountableActive = in_array($curr_route, ['accountableRead', 'accountableEdit']) ? 'active' : '';

    $officeCampusActive   = (in_array($curr_route, ['officeRead', 'officeEdit']) && $param == 1) ? 'active' : '';
    $officeLocationActive = (in_array($curr_route, ['officeRead', 'officeEdit']) && $param == 2) ? 'active' : '';
@endphp


<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1 {{ $ppeActive  || $lvActive || $hvActive || $intActive ? 'menu-open' : '' }}">
        @if(auth()->user()->role !== 'Campus Admin')
        <a href="#" data-toggle="collapse" aria-expanded="false" class="nav-link2 {{ ($ppeActive || $lvActive || $hvActive || $intActive) ? 'active' : '' }}" style="color: #000;" onclick="toggleSubmenu(this)">
            Property Type <i class="fas fa-angle-down right float-right"></i> 
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item mb-1 mt-1">
                <a href="{{ route('ppeRead') }}" class="nav-link2 {{ $ppeActive }}" style="color: #000;">
                    <i class="fas fa-minus nav-icon"></i> PPE
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="{{ route('lvRead') }}" class="nav-link2 {{ $lvActive }}" style="color: #000;">
                    <i class="fas fa-minus nav-icon"></i> Low Value
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('hvRead') }}" class="nav-link2 {{ $hvActive }}" style="color: #000;">
                    <i class="fas fa-minus nav-icon"></i> High Value
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('intRead') }}" class="nav-link2 {{ $intActive }}" style="color: #000;">
                    <i class="fas fa-minus nav-icon"></i> Intangible
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('unitRead') }}" class="nav-link2 {{ $unitActive }}" style="color: #000;">
            Unit
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('itemRead') }}" class="nav-link2 {{ $itemtActive }}" style="color: #000;">
            Items
        </a>
    </li>
    
    <li class="nav-item mb-1">
        <a href="{{ route('officeRead', 1) }}" class="nav-link2 {{ $officeCampusActive }}" style="color: #000;">
            Campus & Offices
        </a>
    </li>
    @endif
    <li class="nav-item mb-1">
        <a href="{{ route('officeRead', 2) }}" class="nav-link2 {{ $officeLocationActive }}" style="color: #000;">
            Location
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('accountableRead') }}" class="nav-link2 {{ $accountableActive }}" style="color: #000;">
            {{ auth()->user()->role != "Campus Admin" ? "Accountable Person" : "End User" }}
        </a>
    </li>
</ul>
