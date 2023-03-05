@extends('payment-gateways::layout')

@section('title', 'Paystack')

@section('content')
    <div class="h-screen">
        <div class="bg-white m-4 py-6 px-4 max-w-md md:mx-auto">
            <svg class="text-red-600 w-32 h-32 mx-auto " viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" fill="currentColor"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"></path>
            </svg>
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">
                    {{$title}}
                </h3>

                <p class="text-gray-600 my-2">
                    {{$message}}
                </p>

                @if(config('payment-gateways.support_email'))
                    <hr>
                    <p class="text-gray-600 my-2"> If you need further assistance, please contact us at <a
                            class="text-blue-500"
                            href="mailto:{{$support_mail = config('payment-gateways.support_email')}}">{{ $support_mail }}</a> or reach
                        out to us via our social media
                        platforms.
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
