
@php 
   $curr_route = request()->route()->getName();
@endphp

<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('user_settings') }}" class="nav-link2 {{$curr_route=='user_settings'?'active':''}}" style="color: #000;">
            Account Setting
        </a>
    </li>
    
    @if(auth()->user()->role=='Administrator')
    <li class="nav-item mb-1">
        <a href="{{ route('setting_list') }}" class="nav-link2 {{$curr_route=='setting_list'?'active':''}}" style="color: #000;">
            System Setting
        </a>
    </li>
    @endif
</ul>