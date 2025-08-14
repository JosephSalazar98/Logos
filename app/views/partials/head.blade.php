<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cascadia+Code:ital,wght@0,200..700;1,200..700&display=swap"
    rel="stylesheet">
<title>Logos AI</title>
<link rel="shortcut icon" href="/assets/img/logo.png" type="image/png">
@vite('css/new.css')
@alpine
<script>
    window.userWallet = "{{ session()->get('wallet') }}";
</script>
