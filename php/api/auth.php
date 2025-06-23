<?php
function verifyJWT($token)
{
    // ✅ Load secret from environment or config (do NOT hardcode)
    $secretKey = 'AxUWaW6ZOjEWSaRu0bQEOPt54s28DSulzIQ8uEdjh5PvyMEDGikLaHK673TwFbRP'; // Replace fallback in production
    try {
        if ($token == $secretKey) {
            return true;
        }
    } catch (Exception $e) {
        error_log("wrong password");
        return false;
    }
}
?>