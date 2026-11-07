<?php

/*
 * Content for the FAQ section on the home page (resources/views/CompanyProfile.blade.php).
 * Shape: category slug => title + list of [question, answer] pairs.
 * To add a question, add another ['Question?', 'Answer.'] line; to add a category, add a new block.
 * The jump bar, the accordion rows and the anchor ids are all generated from this file.
 * Answers are plain text (they are HTML-escaped by Blade).
 */

return [
    'booking' => [
        'title' => 'Booking',
        'items' => [
            ['How do I book an itinerary?', 'Browse the seven nations, pick an itinerary and press Book Now. Online booking is still being built, so for now email your chosen itinerary to '.config('app.desk_email').' and the Director\'s desk will confirm the details with you.'],
            ['How long does it take to get a reply?', 'Most inquiries are answered, and itineraries confirmed, within 3–5 business days.'],
            ['How long are the itineraries?', 'Our curated itineraries run from 2 to 6 days. Each card on the nation pages shows the exact number of days and nights.'],
            ['Are prices per person?', 'Yes. Every price on the site is per person, in Philippine pesos (₱). The desk confirms the final quote for your group when you book.'],
        ],
    ],

    'included' => [
        'title' => 'What\'s included',
        'items' => [
            ['What does an itinerary include?', 'Each itinerary covers accommodation for the nights listed, daily breakfast, a guided tour, land and local transport, and travel insurance. Open any itinerary card and look under "Inclusions" for the full list.'],
            ['Are meals other than breakfast included?', 'Only when the itinerary description says so. For example, some routes include a picnic lunch, cooked meals or a patisserie stop. Everything else is at your own pace and budget.'],
            ['Can I see where the route goes before I book?', 'Yes. Each itinerary card lists its stops and a short description, and every nation page has a photo gallery and practical travel notes.'],
        ],
    ],

    'custom' => [
        'title' => 'Custom journeys',
        'items' => [
            ['Can I combine more than one nation in a single trip?', 'Yes. Any itinerary can be combined into a multi-nation journey. Tell the desk which nations you have in mind and we will draft a route.'],
            ['Can you tailor a trip to my group or schedule?', 'We can adjust a route to your preferences, group size and travel dates. Send the desk a short note about what you are after.'],
            ['Who plans my route?', 'The Director personally reviews every traveller\'s route before it is confirmed, and every route we sell has been walked, sailed or sledded by the Director first.'],
        ],
    ],

    'safety' => [
        'title' => 'Travel & safety',
        'items' => [
            ['Are the tours guided?', 'Yes, every journey is guided. Safety and comfort come first, with trusted local partners in each nation.'],
            ['When is the best time to visit?', 'It depends on the nation. Each nation page lists its best season at the top, so check the "Best season" fact before you choose your dates.'],
            ['Is there support while I am travelling?', 'Our desk offers 24/7 support during your journey, so you are never left to sort out a problem alone.'],
        ],
    ],

    'payments' => [
        'title' => 'Payments & changes',
        'items' => [
            ['How do I pay?', 'Online payment is coming soon. Until then, the desk confirms your booking by email and will send payment arrangements together with your confirmation.'],
            ['Can I change or cancel a booking?', 'Contact the desk as early as you can. Changes and cancellations are handled case by case, and the terms for your trip are confirmed in writing with your booking.'],
            ['Who runs Teyvat Travel Services?', 'We are an independent, sole-proprietor agency founded in Mondstadt in 2018: one desk, one director, and a short list of trusted local partners in each nation.'],
        ],
    ],
];
