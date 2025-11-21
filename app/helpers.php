<?php

function isMobileDevice() {
    return preg_match('/Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i',
        request()->header('User-Agent')
    );
}
