@php
    $curr_route = request()->route()->getName();
    $allActive = in_array($curr_route, ['propertiesRead', 'propertiesEdit']) ? 'active' : '';
    $ppeActive = in_array($curr_route, ['propertiesppeRead', 'propertiesEdit']) ? 'active' : '';
    $highActive = in_array($curr_route, ['propertieshighRead', 'propertiesEdit']) ? 'active' : '';
    $lowActive = in_array($curr_route, ['propertieslowRead', 'propertiesEdit']) ? 'active' : '';
    $intActive = in_array($curr_route, ['propertiesintangibleRead']) ? 'active' : '';
    $blankStickerActive = in_array($curr_route, ['propertiesStickerTemplate']) ? 'active' : '';
@endphp

<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead') }}" class="nav-link2 {{ $allActive }}" id="allButton">
            All
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertiesppeRead') }}" class="nav-link2 {{ $ppeActive }}" id="ppeButton">
            PPE
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertieshighRead') }}" class="nav-link2 {{ $highActive }}" id="highButton">
            High Value
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertieslowRead') }}" class="nav-link2 {{ $lowActive }}" id="lowButton">
            Low Value
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertiesintangibleRead') }}" class="nav-link2 {{ $intActive }}" id="lowButton">
            Intangible Value
        </a>
    </li>
    @if(auth()->user()->role !=='Campus Admin')
    <li class="nav-item mb-1">
        <a href="{{ route('propertiesStickerTemplate') }}" class="nav-link2 {{ $blankStickerActive }}">
            Blank Sticker
        </a>
    </li>
    @endif
</ul>

