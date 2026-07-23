@extends('layouts.app')
@section('title', 'Request Submitted')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-5">
            <div class="card-body p-5 text-center">
                <div style="font-size:2.5rem; color:#0c3c7c;">✓</div>
                <h5 class="fw-bold mt-2">Request Submitted</h5>
                <p class="text-muted small mb-4">
                    Your request has been sent to the administrator for review.
                    Please wait for approval before collecting your items.
                </p>
                <a href="{{ route('requests.create') }}" class="btn btn-outline-primary">Submit Another Request</a>
            </div>
        </div>
    </div>
</div>
@endsection