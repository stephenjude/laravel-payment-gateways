@extends('payment-gateways::layout')

@section('title', 'Paystack')

@section('content')
    @include('payment-gateways::loader')
@endsection

@push('scripts')
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        var data = {{ Illuminate\Support\Js::from($data) }};

        var handler = PaystackPop.setup({
            key: data.public_key,
            email: data.user.email,
            amount: data.amount,
            currency: data.currency, // Use GHS for Ghana Cedis or USD for US Dollars
            ref: data.reference,
            channels: data.channels,
            callback: function (response) {
                // var reference = response.reference;
                window.location.href = data.callback_url;
            },
            onClose: function () {
                window.location.href = data.callback_url;
            },
        });
        handler.openIframe();
    </script>
@endpush
