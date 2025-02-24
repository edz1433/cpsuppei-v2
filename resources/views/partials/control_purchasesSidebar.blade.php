@php
    $curr_route = request()->route()->getName();
    $allActive = in_array($curr_route, ['purchaseREAD', 'purchaseEdit']) ? 'active' : '';
    $ppeActive = in_array($curr_route, ['purchaseppeREAD', 'purchaseEdit']) ? 'active' : '';
    $highActive = in_array($curr_route, ['purchasehighREAD', 'purchaseEdit']) ? 'active' : '';
    $lowActive = in_array($curr_route, ['purchaselowREAD', 'purchaseEdit']) ? 'active' : '';
    $intActive = in_array($curr_route, ['purchaseintangibleREAD']) ? 'active' : '';
    $blankStickerActive = in_array($curr_route, ['purchaseStickerTemplate']) ? 'active' : '';
@endphp

<ul class="nav nav-pills nav-sidebar nav-compact flex-column">
    <li class="nav-item mb-1">
        <a href="{{ route('purchaseREAD') }}" class="nav-link2 {{ $allActive }}" id="allButton">
            All
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('inventoryppeREAD') }}" class="nav-link2 {{ $ppeActive }}" id="ppeButton">
            PPE
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('inventoryhighREAD') }}" class="nav-link2 {{ $highActive }}" id="highButton">
            High Value
        </a>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('inventorylowREAD') }}" class="nav-link2 {{ $lowActive }}" id="lowButton">
            Low Value
        </a>
    </li>
</ul>

