@extends('layouts.network')

@section('title', 'Post')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow border">
            @include('network.partials.post')
        </div>
    </div>
@endsection
