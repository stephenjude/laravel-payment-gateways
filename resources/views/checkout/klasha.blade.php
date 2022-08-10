@extends('payment-gateways::layout')

@section('title', 'Klasha Checkout')

@section('content')
    @include('payment-gateways::loader')
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript"
            src="https://js.klasha.com/pay.js"></script>
    <script>
        const data = {{ Illuminate\Support\Js::from($sessionData) }};

        const publicKey = {{ Illuminate\Support\Js::from(config('payment-gateways.providers.klasha.public')) }};

        function redirect() {
            window.location.href = data.extra.callback_url;
        }

        const kit = {
            currency: data.extra.currency,
            email: data.extra.email,
            tx_ref: data.paymentReference,
            callBack: function () {
                window.location.href = data.extra.callback_url;
            }
        }

        new KlashaClient(
            publicKey,
            1,
            data.extra.amount,
            "ktest",
            data.extra.callback_url,
            data.extra.currency,
            data.extra.currency,
            kit,
            data.extra.is_test_mode,
        )
    </script>
@endpush
