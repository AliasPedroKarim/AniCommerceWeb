<?php


namespace App\Utils\Paypal;


use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PaypalClient {
    const INTENT_AUTHORIZE = "AUTHORIZE";
    const INTENT_CAPTURE = "CAPTURE";

    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client() {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     */
    public static function environment() {
        $clientId = $_ENV["PAYPAL_SANDBOX_CLIENT_ID"] ?: "PAYPAL-SANDBOX-CLIENT-ID";
        $clientSecret = $_ENV["PAYPAL_SANDBOX_CLIENT_SECRET"] ?: "PAYPAL-SANDBOX-CLIENT-SECRET";
        return new SandboxEnvironment($clientId, $clientSecret);
    }

}