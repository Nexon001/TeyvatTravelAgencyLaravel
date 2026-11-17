@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('description', 'Administration area.')
@section('body-class', 'chapter-profile')

@php
    $versioned = fn (string $path) => asset($path).(file_exists(public_path($path)) ? '?v='.filemtime(public_path($path)) : '');
@endphp

@push('head')
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="{{ $versioned('css/auth.css') }}">
@endpush

@section('content')
<section class="auth-page">
  <div class="container">
    <h1 class="admin-title">Admin dashboard</h1>
    <p class="auth-intro">Signed in as {{ auth()->user()->name }}. You are logged out automatically after {{ config('admin.idle_minutes') }} minutes without opening an admin page.</p>

    <ul class="admin-grid">
      <li class="admin-tile"><strong>Bookings</strong><span>View, filter, confirm or cancel (coming soon)</span></li>
      <li class="admin-tile"><strong>Itineraries</strong><span>Add and edit travel packages (coming soon)</span></li>
      <li class="admin-tile"><strong>Inquiries</strong><span>Read and reply to contact messages (coming soon)</span></li>
      <li class="admin-tile"><strong>Customers</strong><span>View registered accounts (coming soon)</span></li>
    </ul>
  </div>
</section>
@endsection
