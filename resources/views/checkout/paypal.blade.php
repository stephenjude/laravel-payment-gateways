@extends('gateways.layout')

@section('title', 'PayPal Checkout')

@push('styles')
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ $data['public_key'] }}&components=buttons,marks"
        data-client-token="{{$data['reference']}}"></script>
@endpush

@section('content')
    @include('payment-gateways::loader')
    <div id="paymentPanel" class="hidden h-screen flex items-center justify-center bg-gray-100">
        <div id="paypal-button-container" class="paypal-button-container"></div>
    </div>
@endsection

@push('scripts')
    <script>
        initializePaypal();

        function initializePaypal() {
            let paymentData = {{ Illuminate\Support\Js::from($data) }}

            paypal
                .Buttons({
                    onInit: function (data, actions) {
                        setLoading(false);
                    },
                    createOrder: function (data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: paymentData.amount
                                }
                            }]
                        });
                    },
                    onApprove: function (data, actions) {
                        return actions.order.capture().then(function (details) {
                            window.location.href = paymentData.callback_url + "&id=" + details.id;
                        });
                    }
                })
                .render("#paypal-button-container");
        }

        function setLoading(status) {
            if (status) {
                showLoader();
                document.getElementById('paymentPanel').classList.add('hidden');
            } else {
                hideLoader();
                document.getElementById('paymentPanel').classList.remove('hidden');
            }


        }
    </script>
@endpush
