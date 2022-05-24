@extends('gateways.layout')

@section('title', 'Flutterwave')

@section('content')
    @include('payment-gateways::loader')
@endsection

@push('scripts')
    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <script>
        const data = {{ Illuminate\Support\Js::from($data) }};

        const publicKey = {{ Illuminate\Support\Js::from(config('payment-gateways.providers.flutterwave.public')) }};

        FlutterwaveCheckout({
            public_key: publicKey,
            tx_ref: data.reference,
            amount: data.amount,
            currency: data.currency,
            payment_options: data.channels.join(', '),
            redirect_url: data.callbackUrl,
            meta: data.meta,
            customer: {
                email: data.email,
            },
        });

    </script>
@endpush
