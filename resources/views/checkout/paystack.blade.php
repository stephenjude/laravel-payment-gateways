@extends('payment-gateways::layout')

@section('title', 'Paystack Checkout')

@section('content')
    @include('payment-gateways::loader')
@endsection

@push('scripts')
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const data = {{ Illuminate\Support\Js::from($data) }};

        const publicKey = {{ Illuminate\Support\Js::from(config('payment-gateways.providers.paystack.public')) }};

        const handler = PaystackPop.setup({
            key: publicKey,
            email: data.email,
            amount: data.amount,
            currency: data.currency, // Use GHS for Ghana Cedis or USD for US Dollars
            ref: data.reference,
            channels: data.channels,
            metadata: data.meta,
            callback: function (response) {
                // var reference = response.reference;
                window.location.href = data.callbackUrl;
            },
            onClose: function () {
                window.location.href = data.callbackUrl;
            },
        });
        handler.openIframe();
    </script>
@endpush
