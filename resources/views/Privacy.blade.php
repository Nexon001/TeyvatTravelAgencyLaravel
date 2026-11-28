@extends('layouts.app')

@section('title', 'Privacy Notice')
@section('description', 'How Teyvat Travel Services collects, uses and protects your personal data, and your rights under the Philippine Data Privacy Act.')
@section('body-class', 'chapter-profile')

@section('content')
{{--
    DRAFT for NFR-03. Written for the team to review with the Director before launch.
    TODO: confirm the retention period and who may see the data, then update the wording below.
--}}
<section class="section">
  <div class="container privacy">
    <span class="eyebrow">Privacy</span>
    <h1>Privacy Notice</h1>
    <p>Last updated: November 2026</p>

    <p>Teyvat Travel Services is a sole proprietorship. We respect your privacy and handle personal data in line with the Data Privacy Act of 2012 (Republic Act No. 10173).</p>

    <h2>What we collect</h2>
    <ul>
      <li><strong>Contact form:</strong> your name, email address, the subject you choose and your message, plus the date and time you agreed to this notice.</li>
      <li><strong>Accounts and bookings:</strong> if you create an account or make a booking, we also keep your phone number, travel dates and booking details. Passwords are stored only as a secure hash.</li>
    </ul>

    <h2>Why we collect it</h2>
    <p>We use your data only to answer your inquiry, to manage your booking and to contact you about it. We do not use it for anything else without asking you first.</p>

    <h2>Who can see it</h2>
    <p>Messages and bookings are stored in our database and are visible to the Director, who administers this website.</p>

    <h2>How long we keep it</h2>
    <p>We keep your data only for as long as we need it to answer you, to deliver your trip and to keep the records we are required to keep. After that it is deleted or anonymised.</p>

    <h2>Your rights</h2>
    <p>You may ask us to show you the data we hold about you, correct it, or delete or block it. You may also withdraw your consent or object to how we use your data. If you believe your data was mishandled, you may file a complaint with the National Privacy Commission.</p>

    <h2>Contact us</h2>
    <p>To use any of these rights, email <a href="mailto:{{ config('app.desk_email') }}">{{ config('app.desk_email') }}</a>.</p>
  </div>
</section>
@endsection
