<div id="loader" class="w-full grid grid-cols-1 h-screen">
    <div class="flex items-end justify-center">
        <div class="spinner-border animate-spin inline-block w-12 h-12 border-4 rounded-full text-gray-600"
             role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <p class=" text-center text-gray-500 mt-4">This may take a few seconds, please don't close this page.</p>
</div>

@push('scripts')
    <script>
        function hideLoader() {
            document.getElementById('loader').classList.add('hidden');
        }

        function showLoader() {
            document.getElementById('loader').classList.remove('hidden');
        }
    </script>
@endpush
