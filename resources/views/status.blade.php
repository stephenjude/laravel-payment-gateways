@extends('payment-gateways::layout')

@section('title', 'Paystack')

@section('content')
    <div class="h-screen">
        <div class="bg-white m-4 py-6 px-4 max-w-md md:mx-auto">
            @if($successful)
                <svg viewBox="0 0 24 24" class="text-green-600 w-16 h-16 mx-auto my-6">
                    <path fill="currentColor"
                          d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z">
                    </path>
                </svg>
            @else
                <svg class="text-red-600 w-32 h-32 mx-auto " viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" fill="currentColor"
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"></path>
                </svg>
            @endif
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">
                    Payment {{$successful ? 'Done' : 'Failed'}}!
                </h3>

                @if($successful)
                    <p class="text-gray-600 my-2">Your payment transaction was successful. Please close the tab to
                        continue.</p>
                @else
                    <p class="text-gray-600 my-2">Your payment transaction was unsuccessful. Please close the tab to
                        continue.</p>
                @endif
                @if(config('payment-gateways.support_email'))
                    <hr>
                    <p class="text-gray-600 my-2"> If you need further assistance, please contact us at <a
                            class="text-blue-500"
                            href="mailto:{{config('payment-gateways.support_email')}}">support@pay4me.app</a> or reach
                        out to us via our social media
                        platforms.
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
