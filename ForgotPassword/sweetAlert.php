<?php

function sweetAlert($icon, $title, $text, $confirmButtonText, $link=null)
{
    $icon = json_encode($icon);
    $title = json_encode($title);
    $text = json_encode($text);
    $confirmButtonText = json_encode($confirmButtonText);
    $link = json_encode($link);
    echo "
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: $icon,
            title: $title,
            text: $text,
            confirmButtonText: $confirmButtonText
        }).then(result => {
            if (result.isConfirmed && $link) {
                window.location.href = $link;
            }
        });
    });
    </script>";

}
