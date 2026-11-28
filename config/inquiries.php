<?php

/*
|--------------------------------------------------------------------------
| Inquiry inbox settings
|--------------------------------------------------------------------------
*/

return [

    // An unanswered inquiry is flagged "overdue" once this many business days (Monday to Friday,
    // Manila time) have passed. The site promises a reply within 3 to 5 business days.
    // Override with INQUIRY_OVERDUE_BUSINESS_DAYS in .env.
    'overdue_business_days' => (int) env('INQUIRY_OVERDUE_BUSINESS_DAYS', 3),

];
