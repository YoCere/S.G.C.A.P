<!DOCTYPE html>
<html>
<head>
    <title>Test reCAPTCHA v2</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <h1>Test reCAPTCHA v2</h1>
    <form action="/test-recaptcha-v2" method="POST">
        @csrf
        <div class="g-recaptcha" data-sitekey="6LetFQUsAAAAAFSzPgg0VkvK0fouoYYp0DFDUR3h"></div>
        <button type="submit">Enviar Test</button>
    </form>
    
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Enviando test...');
            this.submit();
        });
    </script>
</body>
</html>