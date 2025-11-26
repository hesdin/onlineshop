@extends('layouts.app')

@section('title', 'PaDi UMKM - Marketplace B2B')

@section('content')
    @include('sections.hero')
    @include('sections.categories')
    @include('sections.collections')
    @include('sections.benefits')
    @include('sections.faq')
@endsection
