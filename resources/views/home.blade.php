@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                    @if (Auth::user()->role['role'] == 'ho')
                        {{ 'Welcome HO' }}
                    @elseif (Auth::user()->role['role'] == 'dc')
                        {{ 'Welcome DC' }}
                    @elseif (Auth::user()->role['role'] == 'admin' )
                        {{ 'Welcome Admin' }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
