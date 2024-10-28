<?php
function callSweetAlert($status, $title, $message, $redirect) {
    $status = json_encode($status);
    $title = json_encode($title);
    $message = json_encode($message);
    $redirect = json_encode($redirect);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #loading {
          display: flex;
          position: fixed;
          top: 0;
          left: 0;
          z-index: 1000;
          width: 100vw;
          height: 100vh;
          background-color: rgba(192, 192, 192, 0.5);
          background-image: url("ForgotPassword/images/loading.gif");
          background-repeat: no-repeat;
          background-position: center;
        }

        .hide {
          display: none !important;
        }

    </style>
</head>
<body>

    <div id="loading" class="hide"></div>
    <script>
        const setVisible = (elementOrSelector, visible) => {
          const element = document.querySelector(elementOrSelector);
          if (visible) {
            element.classList.remove("hide");
          } else {
            element.classList.add("hide");
          }
        };
        setVisible('#loading', true);

        Swal.fire({
            icon: <?= $status ?>,
            title: <?= $title ?>,
            text: <?= $message ?>,
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed && <?= $redirect ?> != null) {
                window.location.href = <?= $redirect ?>;
            }
        });
    </script>
</body>
<?php
}
?>
