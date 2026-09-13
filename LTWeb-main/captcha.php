<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCaptcha() {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $_SESSION['captcha_num1'] = $num1;
    $_SESSION['captcha_num2'] = $num2;
    $_SESSION['captcha_answer'] = $num1 + $num2;
}

function verifyCaptcha($userInput) {
    $sessionAnswer = $_SESSION['captcha_answer'] ?? null;
    if ($userInput === '' || $sessionAnswer === null) {
        return false;
    }
    return (int)$userInput === (int)$sessionAnswer;
}

function clearCaptcha() {
    unset($_SESSION['captcha_num1'], $_SESSION['captcha_num2'], $_SESSION['captcha_answer']);
}
?>