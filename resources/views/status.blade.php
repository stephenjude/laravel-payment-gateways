@extends('payment-gateways::layout')

@section('title', 'Paystack')

@section('content')
    <div class="h-screen">
        <div class="bg-white m-4 py-6 px-4 max-w-md md:mx-auto">
            @if($payment->isSuccessful())
                <svg viewBox="0 0 24 24" class="text-green-600 w-16 h-16 mx-auto my-6">
                    <path fill="currentColor"
                          d="M12,0A12,12,0,1,0,24,12,12.014,12.014,0,0,0,12,0Zm6.927,8.2-6.845,9.289a1.011,1.011,0,0,1-1.43.188L5.764,13.769a1,1,0,1,1,1.25-1.562l4.076,3.261,6.227-8.451A1,1,0,1,1,18.927,8.2Z">
                    </path>
                </svg>
            @elseif($payment->isProcessing())
                <svg class="text-orange-500 w-16 h-16 mx-auto my-6" xmlns="http://www.w3.org/2000/svg"
                     shape-rendering="geometricPrecision"
                     text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd"
                     clip-rule="evenodd" viewBox="0 0 502 511.82">
                    <path fill-rule="nonzero" fill="currentColor"
                          d="M279.75 471.21c14.34-1.9 25.67 12.12 20.81 25.75-2.54 6.91-8.44 11.76-15.76 12.73a260.727 260.727 0 0 1-50.81 1.54c-62.52-4.21-118.77-31.3-160.44-72.97C28.11 392.82 0 330.04 0 260.71 0 191.37 28.11 128.6 73.55 83.16S181.76 9.61 251.1 9.61c24.04 0 47.47 3.46 69.8 9.91a249.124 249.124 0 0 1 52.61 21.97l-4.95-12.96c-4.13-10.86 1.32-23.01 12.17-27.15 10.86-4.13 23.01 1.32 27.15 12.18L428.8 68.3a21.39 21.39 0 0 1 1.36 6.5c1.64 10.2-4.47 20.31-14.63 23.39l-56.03 17.14c-11.09 3.36-22.8-2.9-26.16-13.98-3.36-11.08 2.9-22.8 13.98-26.16l4.61-1.41a210.71 210.71 0 0 0-41.8-17.12c-18.57-5.36-38.37-8.24-59.03-8.24-58.62 0-111.7 23.76-150.11 62.18-38.42 38.41-62.18 91.48-62.18 150.11 0 58.62 23.76 111.69 62.18 150.11 34.81 34.81 81.66 57.59 133.77 61.55 14.9 1.13 30.23.76 44.99-1.16zm-67.09-312.63c0-10.71 8.69-19.4 19.41-19.4 10.71 0 19.4 8.69 19.4 19.4V276.7l80.85 35.54c9.8 4.31 14.24 15.75 9.93 25.55-4.31 9.79-15.75 14.24-25.55 9.93l-91.46-40.2c-7.35-2.77-12.58-9.86-12.58-18.17V158.58zm134.7 291.89c-15.62 7.99-13.54 30.9 3.29 35.93 4.87 1.38 9.72.96 14.26-1.31 12.52-6.29 24.54-13.7 35.81-22.02 5.5-4.1 8.36-10.56 7.77-17.39-1.5-15.09-18.68-22.74-30.89-13.78a208.144 208.144 0 0 1-30.24 18.57zm79.16-69.55c-8.84 13.18 1.09 30.9 16.97 30.2 6.21-.33 11.77-3.37 15.25-8.57 7.86-11.66 14.65-23.87 20.47-36.67 5.61-12.64-3.13-26.8-16.96-27.39-7.93-.26-15.11 4.17-18.41 11.4-4.93 10.85-10.66 21.15-17.32 31.03zm35.66-99.52c-.7 7.62 3 14.76 9.59 18.63 12.36 7.02 27.6-.84 29.05-14.97 1.33-14.02 1.54-27.9.58-41.95-.48-6.75-4.38-12.7-10.38-15.85-13.46-6.98-29.41 3.46-28.34 18.57.82 11.92.63 23.67-.5 35.57zM446.1 177.02c4.35 10.03 16.02 14.54 25.95 9.96 9.57-4.4 13.86-15.61 9.71-25.29-5.5-12.89-12.12-25.28-19.69-37.08-9.51-14.62-31.89-10.36-35.35 6.75-.95 5.03-.05 9.94 2.72 14.27 6.42 10.02 12 20.44 16.66 31.39z"/>
                </svg>
            @elseif($payment->failed())
                <svg class="text-red-600 w-32 h-32 mx-auto " viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" fill="currentColor"
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"></path>
                </svg>
            @endif
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">
                    Payment
                    @if($payment->isSuccessful())
                        Successful
                    @elseif($payment->failed())
                        Failed
                    @elseif($payment->isProcessing())
                        Processing
                    @endif
                </h3>

                @if($successful)
                    <p class="text-gray-600 my-2">
                        {!! config('payment-gateways.message.success') !!}
                    </p>
                @elseif($payment->failed())
                    <p class="text-gray-600 my-2">
                        {!! config('payment-gateways.message.failed') !!}
                    </p>
                @elseif($payment->isProcessing())
                    <p class="text-gray-600 my-2">
                        {!! config('payment-gateways.message.pending') !!}
                    </p>
                @endif
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
