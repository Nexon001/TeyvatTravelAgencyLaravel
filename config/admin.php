<?php

/*
|--------------------------------------------------------------------------
| Admin area settings
|--------------------------------------------------------------------------
*/

return [

    // An administrator who has not opened an admin page for this many minutes
    // is logged out the next time they try. Override with ADMIN_IDLE_MINUTES in .env.
    'idle_minutes' => (int) env('ADMIN_IDLE_MINUTES', 30),

];
