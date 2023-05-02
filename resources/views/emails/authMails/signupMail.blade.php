@extends('emails.baseEmail')

@section('mailContent')
    <p> Sign up Successful </p>
    <h3>{{ $tokenDetails->token_code }}</h3>
    <h6>{{ $tokenDetails->token_exipry }}</h6>
@endsection
