@php
    $curr_route = request()->route()->getName();
    $curr_category = request()->route('category');

    $allActive = ($curr_route == 'propertiesRead' && $curr_category == 4) ? 'active' : '';
    $ppeActive = ($curr_route == 'propertiesRead' && $curr_category == 3) ? 'active' : '';
    $highActive = ($curr_route == 'propertiesRead' && $curr_category == 1) ? 'active' : '';
    $lowActive = ($curr_route == 'propertiesRead' && $curr_category == 2) ? 'active' : '';
    $intActive = ($curr_route == 'propertiesintangibleRead') ? 'active' : '';
    $blankStickerActive = ($curr_route == 'propertiesStickerTemplate') ? 'active' : '';
    $stickerActive = ($curr_route == 'stickerRead') ? 'active' : '';
    $stickerActivePost = ($curr_route == 'stickerReadPost') ? 'active' : '';
@endphp

<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead', 4) }}" class="nav-link2 {{ $allActive }}" id="allButton">
            All
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead', 3) }}" class="nav-link2 {{ $ppeActive }}" id="ppeButton">
            PPE
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead', 1) }}" class="nav-link2 {{ $highActive }}" id="highButton">
            High Value
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('propertiesRead', 2) }}" class="nav-link2 {{ $lowActive }}" id="lowButton">
            Low Value
        </a>
    </li>
    
    {{-- <li class="nav-item mb-1">
        <a href="{{ route('propertiesintangibleRead') }}" class="nav-link2 {{ $intActive }}" id="lowButton">
            Intangible Value
        </a>
    </li> --}}
    @if(auth()->user()->role !=='Campus Admin')
        <li class="nav-item mb-1">
            <a href="{{ route('stickerRead') }}" class="nav-link2 {{ $stickerActive }} {{ $stickerActivePost }}">
                Property Sticker
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="{{ route('propertiesStickerTemplate') }}" class="nav-link2 {{ $blankStickerActive }}">
                Blank Sticker
            </a>
        </li>
    @endif
</ul>

