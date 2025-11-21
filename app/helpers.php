<?php

use Jenssegers\Agent\Agent;

/**
 * Detect if the current request is from a mobile device (phone or tablet).
 *
 * @return bool
 */
if (!function_exists('isMobileDevice')) {
    function isMobileDevice(): bool
    {
        return (new Agent())->isMobile();
    }
}