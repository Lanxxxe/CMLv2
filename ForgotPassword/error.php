<?php
session_start();
require_once './sweetAlert.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: <?php echo json_encode($_GET['msg']) ?>,
                    confirmButtonText: "OK"
                }).then(result => {
                    if (result.isConfirmed) {
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
                    }
            });
        });
    </script>
</body>
</html>

