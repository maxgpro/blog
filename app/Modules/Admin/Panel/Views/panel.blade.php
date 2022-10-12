@extends('Admin::Layouts.layout')

@section('header')
    @include('Admin::Layouts.parts.header')
@endsection

@section('navigation')
    {!! $sidebar !!}
@endsection

@section('content')
    {!! $content !!}
@endsection

@section('footer')
    @include('Admin::Layouts.parts.footer')
@endsection
