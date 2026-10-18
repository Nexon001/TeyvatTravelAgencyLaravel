@extends('layouts.app')

@php $n = (include resource_path('data/nations.php'))['natlan']; @endphp

@section('title', $n['name'].' — '.$n['eyebrow'])
@section('description', 'Plan a trip to '.$n['name'].' with Teyvat Travel Services: itineraries, traveller\'s notes and a photo gallery for the '.$n['name'].' chapter of your journey.')
@section('body-class', 'chapter-natlan')

@section('content')
    @include('partials.nation', ['n' => $n, 'slug' => 'natlan'])
@endsection