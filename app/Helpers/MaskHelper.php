<?php

if (!function_exists('mask_sensitive_data')) {
    /**
     * Mask part of sensitive data such as email, phone, etc.
     *
     * @param string $data The data to be masked (email, phone, etc.).
     * @param int $start Percentage to start masking.
     * @param int $end Percentage to end masking.
     * @return string The masked data with asterisks.
     */
    function mask_sensitive_data(string $data, int $start = 30, int $end = 70): string
    {
        $len = strlen($data);
        $startPos = floor(($start / 100) * $len); // Start masking position
        $endPos = floor(($end / 100) * $len); // End masking position
        $masked = substr($data, 0, $startPos) . str_repeat('*', $endPos - $startPos) . substr($data, $endPos);

        return $masked;
    }

    /**
     * Mask email specifically to keep the first and last part visible.
     *
     * @param string $email The email address to mask.
     * @return string The masked email address.
     */
    function mask_email(string $email): string
    {
        $email_parts = explode("@", $email);
        $email_name = $email_parts[0];
        $domain = $email_parts[1] ?? '';

        // Show only the first 2 characters and last character of the email name
        $masked_email_name = substr($email_name, 0, 2) . str_repeat('*', strlen($email_name) - 3) . substr($email_name, -1);

        // Mask the domain partially as well
        $masked_domain = substr($domain, 0, 2) . str_repeat('*', strlen($domain) - 5) . substr($domain, -3);

        return $masked_email_name . "@" . $masked_domain;
    }
}
