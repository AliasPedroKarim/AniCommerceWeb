<?php


namespace App\Utils\Paypal;


use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalHttp\HttpException;
use PayPalHttp\HttpResponse;
use App\Utils\Paypal\PaypalClient;

class OrderPaypal {
    /**
     * This is the sample function to create an order. It uses the
     * JSON body returned by buildRequestBody() to create a new order.
     *
     * @param array $body
     * @param string $format
     * @param bool $debug
     * @return false|string
     */
    public static function createOrder(array $body, $format = 'json', $debug = false) {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');

        // $request->body = self::buildRequestBody();
        $request->body = $body;

        // 3. Call PayPal to set up a transaction
        $client = PayPalClient::client();
        $response = $client->execute($request);

        if ($debug) {
            echo "Status Code: {$response->statusCode}\n";
            echo "Status: {$response->result->status}\n";
            echo "Order ID: {$response->result->id}\n";
            echo "Intent: {$response->result->intent}\n";
            echo "Links:\n";
            foreach($response->result->links as $link)
            {
                echo "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }

            // To echo the whole response body, uncomment the following line
        }

        // 4. Return a successful response to the client.
        if ($format == 'response') {
            return $response;
        }
        return json_encode($response->result, JSON_PRETTY_PRINT);
    }

    /**
     * Use this function to perform authorization on the approved order
     * Pass a valid, approved order ID as an argument.
     *
     * @param $orderId
     * @param string $format
     * @param bool $debug
     * @return false|string
     */
    public static function authorizeOrder(string $orderId, $format = 'json', $debug = false) {
        $request = new OrdersAuthorizeRequest($orderId);
        // $request->body = self::buildRequestBody();

        $request->body = "{}";

        // 3. Call PayPal to authorize an order
        $client = PayPalClient::client();
        $response = $client->execute($request);
        // 4. Save the authorization ID to your database. Implement logic to save authorization to your database for future reference.
        if ($debug) {
            echo "Status Code: {$response->statusCode}\n";
            echo "Status: {$response->result->status}\n";
            echo "Order ID: {$response->result->id}\n";
            echo "Authorization ID: {$response->result->purchase_units[0]->payments->authorizations[0]->id}\n";
            echo "Links:\n";
            foreach($response->result->links as $link)
            {
                echo "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            echo "Authorization Links:\n";
            foreach($response->result->purchase_units[0]->payments->authorizations[0]->links as $link)
            {
                echo "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment the following line
        }

        if ($format == 'response') {
            return $response;
        }
        return json_encode($response->result, JSON_PRETTY_PRINT);
    }

    /**
     *You can use this function to retrieve an order by passing order ID as an argument.
     * @param $orderId
     * @param string $format
     * @param bool $debug
     * @return false|HttpResponse|string
     */
    public static function getOrder(string $orderId, $format = 'json', $debug = false) {
        // 3. Call PayPal to get the transaction details
        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($orderId));

        if ($debug == true) {
            /**
             *Enable the following line to echo complete response as JSON.
             */
            //echo json_encode($response->result);
            echo "Status Code: {$response->statusCode}\n";
            echo "Status: {$response->result->status}\n";
            echo "Order ID: {$response->result->id}\n";
            echo "Intent: {$response->result->intent}\n";
            echo "Links:\n";
            foreach($response->result->links as $link)
            {
                echo "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // 4. Save the transaction in your database. Implement logic to save transaction to your database for future reference.
            echo "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";
        }

        if ($format == 'response') {
            return $response;
        }
        return json_encode($response->result, JSON_PRETTY_PRINT);
    }

    // 2. Set up your server to receive a call from the client

    /**
     *This function can be used to capture an order payment by passing the approved
     *order ID as argument.
     *
     * @param string $orderId
     * @param string $format
     * @param bool $debug
     * @return HttpResponse
     */
    public static function captureOrder(string $orderId, $format = 'json', $debug=false)
    {
        $request = new OrdersCaptureRequest($orderId);

        // 3. Call PayPal to capture an authorization
        $client = PayPalClient::client();
        $response = $client->execute($request);
        // 4. Save the capture ID to your database. Implement logic to save capture to your database for future reference.
        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Links:\n";
            foreach($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            print "Capture Ids:\n";
            foreach($response->result->purchase_units as $purchase_unit)
            {
                foreach($purchase_unit->payments->captures as $capture)
                {
                    print "\t{$capture->id}";
                }
            }

            if ($format == 'response') {
                return $response;
            }
            return json_encode($response->result, JSON_PRETTY_PRINT);
        }

        return $response;
    }

    /**
     * Use the following function to capture Authorization.
     * Pass a valid authorization ID as an argument.
     * @param $authorizationId
     * @param string $format
     * @param bool $debug
     * @return false|HttpResponse|string
     */
    public static function captureAuth($authorizationId, $format = 'json', $debug = false) {
        $request = new AuthorizationsCaptureRequest($authorizationId);
        $request->body = "{}";
        $client = PayPalClient::client();

        try {
            $response = $client->execute($request);
        }catch (HttpException $httpException) {
            if ($httpException->statusCode == 422) {
                $jsonError = json_decode($httpException->getMessage());
                if ($jsonError->details[0]->issue == "AUTHORIZATION_ALREADY_CAPTURED") {
                    return json_decode('{ 
                        "error": true,
                        "cause": "AUTHORIZATION_ALREADY_CAPTURED",
                        "stack_error": '. $httpException->getMessage() .'
                     }');
                }
            }
        }

        if ($debug) {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Capture ID: {$response->result->id}\n";
            print "Links:\n";
            foreach($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment
            // the follwowing line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }
        if ($format == 'response') {
            return $response;
        }
        return json_encode($response->result, JSON_PRETTY_PRINT);
    }
}