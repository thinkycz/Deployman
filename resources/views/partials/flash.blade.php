@if (session()->has('flash_notification.message'))
    <script>
        swal({
            title: "{!! session('flash_notification.message') !!}",
            type: "{{ session('flash_notification.level') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif