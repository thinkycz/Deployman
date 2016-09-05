<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Compiled CSS -->
<link rel="stylesheet" href="{{ asset('css/all.css') }}">

<!-- Custom scripts -->
<script>
    window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
    ]); ?>
</script>