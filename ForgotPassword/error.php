<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Show alert message from PHP
            alert('<?php echo $_GET['msg'] ?? ''; ?>');
            
            // Create the form
            const form = document.createElement('form');
            form.id = 'verifyCode';
            form.action = './new_password.php';
            form.method = 'post';
            
            // Create verification code input
            const verificationCodeInput = document.createElement('input');
            verificationCodeInput.type = 'hidden';
            verificationCodeInput.name = 'verification_code';
            verificationCodeInput.value = '<?php echo $_SESSION['input_verification_code'] ?? ''; ?>';
            form.appendChild(verificationCodeInput);
            
            // Append the form to the body
            document.body.appendChild(form);
            
            // Submit the form
            form.submit();
        });
    </script>
</body>
</html>

