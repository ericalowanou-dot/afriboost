@extends('layouts.admin')

@section('title', 'Modifier mission')
@section('heading', 'Modifier la mission')

@section('content')
    @include('admin.missions.form', ['mission' => $mission])
@endsection
