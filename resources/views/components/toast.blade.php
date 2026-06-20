@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showToast === 'function') {
                window.showToast("{{ session('success') }}");
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.showToast === 'function') {
                window.showToast("{{ session('error') }}");
            }
        });
    </script>
@endif
