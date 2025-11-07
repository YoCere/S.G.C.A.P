<!DOCTYPE html>
<html>
<head>
    <title>Test reCAPTCHA</title>
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6LetFQUsAAAAAFSzPgg0VkvK0fouoYYp0DFDUR3h"></script>
</head>
<body>
    <form id="test-form">
        <button type="submit">Test reCAPTCHA</button>
    </form>
    
    <script>
        document.getElementById('test-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Executing reCAPTCHA...');
            
            const token = await grecaptcha.enterprise.execute(
                '6LetFQUsAAAAAFSzPgg0VkvK0fouoYYp0DFDUR3h', 
                {action: 'TEST'}
            );
            
            console.log('Token received:', token);
            alert('Token: ' + token.substring(0, 50) + '...');
        });
    </script>
</body>
</html>