<?php
/* 
    Strip Payments Library v1.0 for codeigniter 
    by Jaydeep Goswami
*/

/* 
    1. get_credentials()
    2. create_customer($customer_data)
    3. construct_event($request_body, $sigHeader, $secret,$tolerance = DEFAULT_TOLERANCE)
    4. create_payment_intent($c_data)
    5. curl($url, $method = 'GET', $data = [])
*/

const DEFAULT_TOLERANCE = 300;
#[\AllowDynamicProperties]
class Stripe
{
    private $secret_key = "";
    private $publishable_key = "";
    private $webhook_secret_key = "";
    private $currency_code = "";
    private $url = "";

    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->helper('url');
        $this->CI->load->helper('form');
        $settings = get_settings('payment_method', true);
        $system_settings = get_settings('system_settings', true);

        $this->secret_key = (isset($settings['stripe_secret_key'])) ? $settings['stripe_secret_key'] : "";
        $this->publishable_key = (isset($settings['stripe_publishable_key'])) ? $settings['stripe_publishable_key'] : "";
        $this->webhook_secret_key = (isset($settings['stripe_webhook_secret_key'])) ? $settings['stripe_webhook_secret_key'] : "";
        $this->currency_code = (isset($settings['stripe_currency_code'])) ? strtolower($settings['stripe_currency_code']) : "usd";
        $this->url = "https://api.stripe.com/";
    }
    public function get_credentials()
    {
        $data['secret_key'] = $this->secret_key;
        $data['publishable_key'] = $this->publishable_key;
        $data['webhook_key'] = $this->webhook_secret_key;
        $data['currency_code'] = $this->currency_code;
        $data['url'] = $this->url;
        return $data;
    }
    public function create_customer($customer_data)
    {
        if (!empty($customer_data)) {
            $create_customer['name'] = $customer_data['name'];
            $create_customer['email'] = $customer_data['email'];
            $create_customer['address']['line1'] = $customer_data['line1'];
            $create_customer['address']['postal_code'] = $customer_data['postal_code'];
            $create_customer['address']['city'] = $customer_data['city'];
            $create_customer['address']['state'] = $customer_data['state'];
            $create_customer['address']['country'] = $customer_data['country'];
        } else {
            $create_customer['name'] = 'Jenny Rosen';
            $create_customer['email'] = 'jennyrosen@example.com';
            $create_customer['address']['line1'] = "510 Townsend St";
            $create_customer['address']['postal_code'] = '98140';
            $create_customer['address']['city'] = "San Francisco";
            $create_customer['address']['state'] = 'CA';
            $create_customer['address']['country'] = 'US';
        }

        $url = $this->url . 'v1/customers';
        $method = 'POST';
        $response = $this->curl($url, $method, $create_customer);
        $res = json_decode($response['body'], true);
        return $res;
    }
    public function construct_event($request_body, $sigHeader, $secret, $tolerance = DEFAULT_TOLERANCE)
    {
        $explode_header = explode(",", $sigHeader);
        for ($i = 0; $i < count($explode_header); $i++) {
            $data[] = explode("=", $explode_header[$i]);
        }
        if (empty($data[0][1]) || $data[0][1] == "" || empty($data[1][1]) || $data[1][1] == "") {
            $response['error'] = true;
            $response['message'] = "Unable to extract timestamp and signatures from header";
            return $response;
        }

        $timestamp = trim($data[0][1]);
        $signs = trim($data[1][1]);

        $signed_payload = "{$timestamp}.{$request_body}";
        log_message('error', 'Signed Payload: ' . $signed_payload);
        log_message('error', 'Secret: ' . $secret);

        $expectedSignature = hash_hmac('sha256', $signed_payload, $secret);

        if ($expectedSignature == $signs) {
            if (($tolerance > 0) && (abs(time() - $timestamp) > $tolerance)) {
                $response['error'] = true;
                $response['message'] = "Timestamp outside the tolerance zone";
                return $response;
            } else {
                return "Matched";
            }
        } else {
            $response['error'] = true;
            $response['expectedSignature'] = $expectedSignature;
            $response['signs'] = $signs;
            $response['signed_payload'] = $signed_payload;
            $response['message'] = "No signatures found matching the expected signature for payload";
            return $response;
        }
    }



    public function create_payment_intent($c_data, $cus_id)
    {
        $c_data['currency'] = $this->currency_code;
        $c_data['description'] = 'Software development services';
        $c_data['customer'] = $cus_id;
        $url = $this->url . 'v1/payment_intents';
        $method = 'POST';
        $response = $this->curl($url, $method, $c_data);
        $res = json_decode($response['body'], true);
        return $res;
    }

    public function curl($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($this->secret_key . ':')
            )
        );
        if (strtolower($method) == 'post') {
            $curl_options[CURLOPT_POST] = 1;
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
        }
        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        return $result;
    }
}
