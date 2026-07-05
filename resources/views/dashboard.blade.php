@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="fw-bold">Welcome, {{ Auth::user()->name }}!</h4>
        <p class="text-muted mb-0">This is your SPDRRMO Inventory dashboard. We'll build the supply modules here next.</p>
    </div>
</div>
@endsection