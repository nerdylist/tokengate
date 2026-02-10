<?php
/**
 * Reusable Alert Component
 *
 * Usage:
 * include 'partials/alert.php';
 * showAlert('success', 'Your message here');
 * showAlert('error', 'Error message here');
 * showAlert('warning', 'Warning message here');
 *
 * Parameters:
 * $type - 'success', 'error', or 'warning'
 * $message - The message to display
 */

function showAlert($type, $message) {
    $bgColor = '#14532d';
    $borderColor = '#166534';
    $textColor = '#4ade80';

    if ($type === 'error') {
        $bgColor = '#7f1d1d';
        $borderColor = '#991b1b';
        $textColor = '#fca5a5';
    } elseif ($type === 'warning') {
        $bgColor = '#78350f';
        $borderColor = '#92400e';
        $textColor = '#fbbf24';
    }

    echo '<div class="message-alert message-' . htmlspecialchars($type) . '" style="
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        font-family: \'Share Tech Mono\', monospace;
        font-size: 0.9375rem;
        font-weight: 600;
        z-index: 1000;
        max-width: 400px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        background-color: ' . $bgColor . ';
        border: 1px solid ' . $borderColor . ';
        color: ' . $textColor . ';
    ">' . htmlspecialchars($message) . '</div>';

    echo '<script>
    setTimeout(() => {
        const alert = document.querySelector(".message-alert");
        if (alert) {
            alert.style.opacity = "0";
            alert.style.transition = "opacity 0.3s ease";
            setTimeout(() => alert.remove(), 300);
        }
    }, 4000);
    </script>';
}

// If $alertType and $alertMessage are set (for immediate display)
if (isset($alertType) && isset($alertMessage)) {
    showAlert($alertType, $alertMessage);
}
?>
