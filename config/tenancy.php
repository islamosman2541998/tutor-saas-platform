<?php

return [

    /*
     * The root domain the platform is served from. Tenant public sites and
     * dashboards resolve as subdomains of this value in production
     * (ahmed-math.{central_domain}). Locally, tenant routes are also
     * reachable path-based via /teacher/{tenant} regardless of this setting.
     */
    'central_domain' => env('CENTRAL_DOMAIN', 'tutor-saas.test'),

];
