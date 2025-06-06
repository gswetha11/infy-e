<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Api extends CI_Controller
{

    /*
---------------------------------------------------------------------------
Defined Methods:-
---------------------------------------------------------------------------
1. login
2. get_delivery_boy_details

<---- Newly changes for consignment ---->
3. get_orders
<---- Newly changes for consignment ---->

4. get_fund_transfers
5. update_user
6. update_fcm
7. reset_password
8. get_notifications
9. verify_user
10. get_settings
11. send_withdrawal_request
12. get_withdrawal_request
13. update_order_consignment_status
14. get_delivery_boy_cash_collection
15. delete_delivery_boy
16. verify_otp
17. resend_otp
---------------------------------------------------------------------------
*/


    private  $user_details = [];

    protected $excluded_routes =
    [
        "delivery_boy/app/v1/api",
        "delivery_boy/app/v1/api/login",
        "delivery_boy/app/v1/api/reset_password",
        "delivery_boy/app/v1/api/get_notifications",
        "delivery_boy/app/v1/api/verify_user",
        "delivery_boy/app/v1/api/get_settings",
        "delivery_boy/app/v1/api/register",
        "delivery_boy/app/v1/api/get_zipcodes",
        "delivery_boy/app/v1/api/get_cities",
        "delivery_boy/app/v1/api/verify_otp",
        "delivery_boy/app/v1/api/resend_otp",
    ];

    public function __construct()
    {
        parent::__construct();
        header("Content-Type: application/json");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $this->load->library(['upload', 'jwt', 'Key', 'ion_auth', 'form_validation', 'paypal_lib']);
        $this->load->model(['category_model', 'Area_model', 'order_model', 'rating_model', 'cart_model', 'address_model', 'transaction_model', 'notification_model', 'Delivery_boy_model', 'Order_model']);
        $this->load->helper(['language', 'string', 'function_helper', 'sms_helper']);
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load('auth');
        $response = $temp = $bulkdata = array();
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        // initialize db tables data
        $this->tables = $this->config->item('tables', 'ion_auth');

        $current_uri =  uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            if ($token['error']) {
                header('Content-Type: application/json');
                http_response_code($token['status']);
                print_r(json_encode($token));
                die();
            }
            $this->user_details = $token['data'];
        }
    }


    public function index()
    {
        $this->load->helper('file');
        $this->output->set_content_type(get_mime_by_extension(base_url('delivery-boy-api-doc.txt')));
        $this->output->set_output(file_get_contents(base_url('delivery-boy-api-doc.txt')));
    }

    public function generate_token()
    {
        $payload = [
            'iat' => time(), /* issued at time */
            'iss' => 'eshop',
            'exp' => time() + (60 * 60 * 24 * 365), /* expires after 1 minute */
            'sub' => 'eshop Authentication'
        ];
        $token = $this->jwt->encode($payload, JWT_SECRET_KEY);
        print_r(json_encode($token));
    }

    public function verify_token()
    {
        try {
            $token = $this->jwt->getBearerToken();
        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
            print_r(json_encode($response));
            return false;
        }

        if (!empty($token)) {

            $api_keys = fetch_details('client_api_keys', ['status' => 1]);
            if (empty($api_keys)) {
                $response['error'] = true;
                $response['message'] = 'No Client(s) Data Found !';
                print_r(json_encode($response));
                return false;
            }
            JWT::$leeway = 2000;
            $flag = true; //For payload indication that it return some data or throws an expection.
            $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
            $message = '';
            try {
                $payload = $this->jwt->decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
                if (isset($payload->iss) && $payload->iss == 'eshop') {
                    $error = false;
                    $flag = false;
                } else {
                    $error = true;
                    $flag = false;
                    $message = 'Invalid Hash';
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

            if ($flag) {
                $response['error'] = true;
                $response['message'] = $message;
                print_r(json_encode($response));
                return false;
            } else {
                if ($error == true) {
                    $response['error'] = true;
                    $response['message'] = $message;
                    print_r(json_encode($response));
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Unauthorized access not allowed";
            print_r(json_encode($response));
            return false;
        }
    }

    public function get_zipcodes()
    {


        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'id';
            $search = (isset($_POST['search']) &&  !empty(trim($_POST['search']))) ? $this->input->post('search', true) : '';
            $res = $this->Area_model->get_zipcodes($search, $limit, $offset);
            $this->response['error'] = false;
            $this->response['message'] = 'Zipcodes Retrieved Successfully';
            $this->response['total'] = $res['total'];
            $this->response['data'] = $res['data'];
        }

        print_r(json_encode($this->response));
    }

    public function get_cities()
    {
        /*
           sort:               // { c.name / c.id } optional
           order:DESC/ASC      // { default - ASC } optional
           search:value        // {optional} 
       */

        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 10;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'c.name';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";

            $result = $this->Area_model->get_cities($sort, $order, $search, $limit, $offset);
            print_r(json_encode($result));
        }
    }

    public function register()
    {

        /*
            name:hiten
            mobile:7852347890
            email:amangoswami@gmail.com
            password:12345678
            confirm_password:12345678
            address : test
            serviceable_zipcodes[] : 1,2,3
            serviceable_cities[] : 1,5(city_id)
            driving_license[] : FILE 
        */

        if (!isset($_POST['user_id'])) {
            $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]');
            $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required|matches[password]|xss_clean');
            $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');

            $shipping_method = get_settings('shipping_method', true);
            if (isset($shipping_method['pincode_wise_deliverability']) && !empty($shipping_method['pincode_wise_deliverability']) && ($shipping_method['pincode_wise_deliverability'] == 1)) {
                $this->form_validation->set_rules('serviceable_zipcodes[]', 'Serviceable Zipcodes', 'trim|required|xss_clean');
            }
            if (isset($shipping_method['city_wise_deliverability']) && !empty($shipping_method['city_wise_deliverability']) && ($shipping_method['city_wise_deliverability'] == 1)) {
                $this->form_validation->set_rules('serviceable_cities[]', 'Serviceable Cities', 'trim|required|xss_clean');
            }

            // If files are selected to upload 
            if (isset($_FILES) && !empty($_FILES) && count((array)$_FILES['driving_license']['name']) < 2) {
                $this->form_validation->set_rules('driving_license', 'driving license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
            }
            if (isset($_FILES) && !empty($_FILES) && count((array)$_FILES['driving_license']['name']) > 2) {
                $this->form_validation->set_rules('driving_license', 'driving license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
            }
        }

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
        } else {
            // upload driving license
            if (!file_exists(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH)) {
                mkdir(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH, 0777);
            }

            $temp_array = array();
            $files = $_FILES;
            $images_new_name_arr = array();
            $images_info_error = "";
            $allowed_media_types = implode('|', allowed_media_types());
            $config = [
                'upload_path' =>  FCPATH . DELIVERY_BOY_DOCUMENTS_PATH,
                'allowed_types' => $allowed_media_types,
                'max_size' => 8000,
            ];

            if (!empty($_FILES['driving_license']['name']) && isset($_FILES['driving_license']['name']) && !empty($files['driving_license']['name'][0])) {
                $other_image_cnt = count((array)$_FILES['driving_license']['name']);

                $other_img = $this->upload;
                $other_img->initialize($config);

                for ($i = 0; $i < $other_image_cnt; $i++) {

                    if (!empty($_FILES['driving_license']['name'][$i])) {

                        $_FILES['temp_image']['name'] = $files['driving_license']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['driving_license']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['driving_license']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['driving_license']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['driving_license']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $images_info_error = 'driving license :' . $images_info_error . ' ' . strip_tags($other_img->display_errors());
                        } else {
                            $temp_array = $other_img->data();
                            resize_review_images($temp_array, FCPATH . DELIVERY_BOY_DOCUMENTS_PATH);
                            $images_new_name_arr[$i] = DELIVERY_BOY_DOCUMENTS_PATH . $temp_array['file_name'];
                        }
                    } else {
                        $_FILES['temp_image']['name'] = $files['driving_license']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['driving_license']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['driving_license']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['driving_license']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['driving_license']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $images_info_error = strip_tags($other_img->display_errors());
                        }
                    }
                }
                //Deleting Uploaded attachments if any overall error occured
                if ($images_info_error != NULL || !$this->form_validation->run()) {
                    if (isset($images_new_name_arr) && !empty($images_new_name_arr || !$this->form_validation->run())) {
                        foreach ($images_new_name_arr as $key => $val) {
                            if (file_exists(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH . $images_new_name_arr[$key])) {
                                unlink(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH . $images_new_name_arr[$key]);
                            }
                        }
                    }
                }
            }


            if ($images_info_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] =  $images_info_error;
                print_r(json_encode($this->response));
                return false;
            }

            if (!$this->form_validation->is_unique($_POST['mobile'], 'users.mobile') || !$this->form_validation->is_unique($_POST['email'], 'users.email')) {
                $response["error"]   = true;
                $response["message"] = "Email or mobile already exists !";
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }

            $identity_column = $this->config->item('identity', 'ion_auth');
            $email = strtolower($this->input->post('email'));
            $mobile = $this->input->post('mobile');
            $identity = ($identity_column == 'mobile') ? $mobile : $email;
            $password = $this->input->post('password');
            if (isset($_POST['serviceable_zipcodes']) && !empty($_POST['serviceable_zipcodes'])) {
                $serviceable_zipcodes = implode(",", $this->input->post('serviceable_zipcodes', true));
            } else {
                $serviceable_zipcodes = NULL;
            }
            if (isset($_POST['serviceable_cities']) && !empty($_POST['serviceable_cities'])) {
                $serviceable_cities = implode(",", $this->input->post('serviceable_cities', true));
            } else {
                $serviceable_cities = NULL;
            }
            $additional_data = [
                'username' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'serviceable_zipcodes' => $serviceable_zipcodes,
                'serviceable_cities' => $serviceable_cities,
                'type' => 'phone',
                'driving_license' => implode(',', $images_new_name_arr),
            ];

            $this->ion_auth->register($identity, $password, $email, $additional_data, ['3']);
            update_details(['active' => 1], [$identity_column => $identity], 'users');

            $data = fetch_details('users', ['mobile' => $identity], 'driving_license')[0];
            unset($data[0]['password']);
            unset($data[0]['confirm_password']);

            $driving_license_data = [];
            if (isset($data['driving_license']) && !empty($data['driving_license'])) {
                $driving_license = explode(',', $data['driving_license']);
                foreach ($driving_license as $row) {
                    array_push($driving_license_data, base_url($row));
                }
            }
            $response['error'] = false;
            $response['message'] = 'Delivery Boy registered Successfully. Wait for approval of admin.';
            $response['driving_license'] = isset($data['driving_license']) && !empty($data['driving_license']) ? $driving_license_data : [];
            echo json_encode($response);
            return;
        }
    }

    public function login()
    {
        /* Parameters to be passed
            mobile: 9874565478
            password: 12345678
            fcm_id: FCM_ID //{ optional }
        */

        $identity_column = $this->config->item('identity', 'ion_auth');
        if ($identity_column == 'mobile') {
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        } elseif ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        } else {
            $this->form_validation->set_rules('identity', 'Identity', 'trim|required|xss_clean');
        }
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('fcm_id', 'FCM ID', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $login = $this->ion_auth->login($this->input->post('mobile'), $this->input->post('password'), false, 'phone');
        if ($login) {
            $data = fetch_details('users', ['mobile' => $this->input->post('mobile', true)]);
            if ($this->ion_auth->in_group('delivery_boy', $data[0]['id'])) {
                if (isset($_POST['fcm_id']) && $_POST['fcm_id'] != '') {
                    update_details(['fcm_id' => $_POST['fcm_id']], ['mobile' => $_POST['mobile']], 'users');
                }

                $existing_token = ($data[0]['apikey'] !== null && !empty($data[0]['apikey'])) ? $data[0]['apikey'] : "";
                unset($data[0]['password']);

                if ($existing_token == '') {
                    $token = generate_token($this->input->post('mobile'));
                    update_details(['apikey' => $token], ['mobile' => $this->input->post('mobile')], "users");
                }


                foreach ($data as $row) {
                    $row = output_escaping($row);
                    $tempRow = [];

                    // Define keys to check
                    $keys = ['id', 'ip_address', 'username', 'email', 'mobile', 'balance', 'activation_selector', 'activation_code', 'forgotten_password_selector', 'forgotten_password_code', 'forgotten_password_time', 'remember_selector', 'remember_code', 'created_on', 'last_login', 'active', 'company', 'address', 'bonus', 'cash_received', 'dob', 'country_code', 'city', 'area', 'street', 'pincode', 'apikey', 'referral_code', 'friends_code', 'fcm_id', 'latitude', 'longitude', 'created_at', 'type'];

                    // Iterate over keys and assign values to $tempRow
                    foreach ($keys as $key) {
                        $tempRow[$key] = isset($row[$key]) && !empty($row[$key]) ? $row[$key] : '';
                    }

                    // Handle image URL
                    $tempRow['image'] = empty($row['image']) || !file_exists(FCPATH . USER_IMG_PATH . $row['image']) ? base_url() . NO_USER_IMAGE : base_url() . USER_IMG_PATH . $row['image'];

                    $rows[] = $tempRow;
                }

                $delivery_boy_data = fetch_details('users', ['id' => $data[0]['id']]);

                //if the login is successful

                $messages = array("0" => "Your account is not yet approved.", "1" => "Logged in successfully");
                //if the login is successful

                $response['error'] = ($delivery_boy_data[0]['status'] != "" && ($delivery_boy_data[0]['status'] != 0)) ? false : true;
                $response['message'] =  $messages[$delivery_boy_data[0]['status']];
                $response['token'] = $existing_token !== "" ? $existing_token : $token;
                $response['data'] = (isset($delivery_boy_data[0]['status']) && $delivery_boy_data[0]['status'] != "" && ($delivery_boy_data[0]['status'] == 1)) ?  $rows : [];
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = true;
                $response['message'] = 'Incorrect Login.';
                echo json_encode($response);
                return false;
            }
        } else {
            // if the login was un-successful
            // just print json message
            $response['error'] = true;
            $response['message'] = strip_tags($this->ion_auth->errors());
            echo json_encode($response);
            return false;
        }
    }

    public function get_delivery_boy_details()
    {
        if (!$this->verify_token()) {
            return false;
        }
        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';

        $data = fetch_details('users', ['id' => $user_id]);

        unset($data[0]['password']);

        foreach ($data as $row) {

            $driving_license_data = [];
            if (isset($row['driving_license']) && !empty($row['driving_license'])) {
                $driving_license = explode(',', $row['driving_license']);
                foreach ($driving_license as $value) {
                    array_push($driving_license_data, base_url($value));
                }
            }
            $row = output_escaping($row);
            $tempRow['id'] = (isset($row['id']) && !empty($row['id'])) ? $row['id'] : '';
            $tempRow['ip_address'] = (isset($row['ip_address']) && !empty($row['ip_address'])) ? $row['ip_address'] : '';
            $tempRow['username'] = (isset($row['username']) && !empty($row['username'])) ? $row['username'] : '';
            $tempRow['email'] = (isset($row['email']) && !empty($row['email'])) ? $row['email'] : '';
            $tempRow['mobile'] = (isset($row['mobile']) && !empty($row['mobile'])) ? $row['mobile'] : '';
            $tempRow['image'] = (isset($row['image']) && !empty($row['image'])) ? $row['image'] : '';
            $tempRow['balance'] = (isset($row['balance']) && !empty($row['balance'])) ? $row['balance'] : '0';
            $tempRow['activation_selector'] = (isset($row['activation_selector']) && !empty($row['activation_selector'])) ? $row['activation_selector'] : '';
            $tempRow['activation_code'] = (isset($row['activation_code']) && !empty($row['activation_code'])) ? $row['activation_code'] : '';
            $tempRow['forgotten_password_selector'] = (isset($row['forgotten_password_selector']) && !empty($row['forgotten_password_selector'])) ? $row['forgotten_password_selector'] : '';
            $tempRow['forgotten_password_code'] = (isset($row['forgotten_password_code']) && !empty($row['forgotten_password_code'])) ? $row['forgotten_password_code'] : '';
            $tempRow['forgotten_password_time'] = (isset($row['forgotten_password_time']) && !empty($row['forgotten_password_time'])) ? $row['forgotten_password_time'] : '';
            $tempRow['remember_selector'] = (isset($row['remember_selector']) && !empty($row['remember_selector'])) ? $row['remember_selector'] : '';
            $tempRow['remember_code'] = (isset($row['remember_code']) && !empty($row['remember_code'])) ? $row['remember_code'] : '';
            $tempRow['created_on'] = (isset($row['created_on']) && !empty($row['created_on'])) ? $row['created_on'] : '';
            $tempRow['last_login'] = (isset($row['last_login']) && !empty($row['last_login'])) ? $row['last_login'] : '';
            $tempRow['active'] = (isset($row['active']) && !empty($row['active'])) ? $row['active'] : '';
            $tempRow['company'] = (isset($row['company']) && !empty($row['company'])) ? $row['company'] : '';
            $tempRow['address'] = (isset($row['address']) && !empty($row['address'])) ? $row['address'] : '';
            $tempRow['bonus'] = (isset($row['bonus']) && !empty($row['bonus'])) ? $row['bonus'] : '0';
            $tempRow['cash_received'] = (isset($row['cash_received']) && !empty($row['cash_received'])) ? $row['cash_received'] : '0.00';
            $tempRow['dob'] = (isset($row['dob']) && !empty($row['dob'])) ? $row['dob'] : '';
            $tempRow['country_code'] = (isset($row['country_code']) && !empty($row['country_code'])) ? $row['country_code'] : '';
            $tempRow['city'] = (isset($row['city']) && !empty($row['city'])) ? $row['city'] : '';
            $tempRow['area'] = (isset($row['area']) && !empty($row['area'])) ? $row['area'] : '';
            $tempRow['street'] = (isset($row['street']) && !empty($row['street'])) ? $row['street'] : '';
            $tempRow['pincode'] = (isset($row['pincode']) && !empty($row['pincode'])) ? $row['pincode'] : '';
            $tempRow['serviceable_zipcodes'] = (isset($row['serviceable_zipcodes']) && !empty($row['serviceable_zipcodes'])) ? $row['serviceable_zipcodes'] : '';
            $tempRow['serviceable_cities'] = (isset($row['serviceable_cities']) && !empty($row['serviceable_cities'])) ? $row['serviceable_cities'] : '';
            $tempRow['apikey'] = (isset($row['apikey']) && !empty($row['apikey'])) ? $row['apikey'] : '';
            $tempRow['referral_code'] = (isset($row['referral_code']) && !empty($row['referral_code'])) ? $row['referral_code'] : '';
            $tempRow['friends_code'] = (isset($row['friends_code']) && !empty($row['friends_code'])) ? $row['friends_code'] : '';
            $tempRow['fcm_id'] = (isset($row['fcm_id']) && !empty($row['fcm_id'])) ? $row['fcm_id'] : '';
            $tempRow['latitude'] = (isset($row['latitude']) && !empty($row['latitude'])) ? $row['latitude'] : '';
            $tempRow['longitude'] = (isset($row['longitude']) && !empty($row['longitude'])) ? $row['longitude'] : '';
            $tempRow['driving_license'] = isset($row['driving_license']) && !empty($row['driving_license']) && $row['driving_license'] != '' ? $driving_license_data : [];
            $tempRow['created_at'] = (isset($row['created_at']) && !empty($row['created_at'])) ? $row['created_at'] : '';
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = 'Data retrived successfully';
        $response['data'] = $rows;
        print_r(json_encode($response));
        return false;
    }

    /* 11.get_orders

        user_id:101
        active_status: received  {received,delivered,cancelled,processed,returned}     // optional
        consignment_id:2            // optional
        limit:25            // { default - 25 } optional
        offset:0            // { default - 0 } optional
        sort: id / date_added // { default - id } optional
        order:DESC/ASC      // { default - DESC } optional
    */

    public function get_orders()
    {
        if (!$this->verify_token()) {
            return false;
        }
        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'o.id';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
        $this->form_validation->set_rules('active_status', 'status', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $delivery_boy_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
            $multiple_status =   (isset($_POST['active_status']) && !empty($_POST['active_status'])) ? explode(',', $_POST['active_status']) : false;
            $consignment_id =   (isset($_POST['consignment_id']) && !empty($_POST['consignment_id'])) ? $_POST['consignment_id'] : null;

            $res = view_all_consignments(delivery_boy_id: $delivery_boy_id, offset: $offset, limit: $limit, order: $order, in_detail: true, multiple_status: $multiple_status, consignment_id: $consignment_id);

            foreach ($res['data'] as $key => $consignment) {
                $subtotal = 0;
                foreach ($consignment['consignment_items'] as $items) {
                    $subtotal += $items['sub_total'];
                }
                $res['data'][$key]['total'] = $subtotal;
                $delivery_charge = $res['data'][$key]['delivery_charge'];
                $promo_discount = $res['data'][$key]['promo_discount'];
                $final_total = $subtotal + $delivery_charge - $promo_discount;
                $res['data'][$key]['total'] = (string)intval($subtotal);
                $res['data'][$key]['final_total'] = (string)intval($final_total);
            }
            if (!empty($res)) {
                $this->response['error'] = false;
                $this->response['message'] = 'Data retrieved successfully';
                $this->response['total'] = $res['total'];
                $this->response['awaiting'] = strval(delivery_boy_orders_count("awaiting", $delivery_boy_id, "consignments"));
                $this->response['received'] = strval(delivery_boy_orders_count("received", $delivery_boy_id, "consignments"));
                $this->response['processed'] = strval(delivery_boy_orders_count("processed", $delivery_boy_id, "consignments"));
                $this->response['shipped'] = strval(delivery_boy_orders_count("shipped", $delivery_boy_id, "consignments"));
                $this->response['delivered'] = strval(delivery_boy_orders_count("delivered", $delivery_boy_id, "consignments"));
                $this->response['cancelled'] = strval(delivery_boy_orders_count("cancelled", $delivery_boy_id, "consignments"));
                $this->response['returned'] = strval(delivery_boy_orders_count("returned", $delivery_boy_id, "consignments"));
                $this->response['data'] = $res['data'];
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Order Does Not Exists';
                $this->response['total'] = "0";
                $this->response['awaiting'] = "0";
                $this->response['received'] = "0";
                $this->response['processed'] = "0";
                $this->response['shipped'] = "0";
                $this->response['delivered'] = "0";
                $this->response['cancelled'] = "0";
                $this->response['returned'] = "0";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }


    /* 3.get_fund_transfers

        limit:25            // { default - 25 } optional
        offset:0            // { default - 0 } optional
        sort: id / date_added // { default - id } optional
        order:DESC/ASC      // { default - DESC } optional

    */

    public function get_fund_transfers()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'id';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';

        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
        $where = ['delivery_boy_id' => $user_id];
        $this->db->select('count(`id`) as total');
        $total_fund_transfers = $this->db->where($where)->get('fund_transfers')->result_array();

        $this->db->select('*');
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);
        $fund_transfer_details = $this->db->where($where)->get('fund_transfers')->result_array();
        if (!empty($fund_transfer_details)) {

            $this->response['error'] = false;
            $this->response['message'] = 'Data retrieved successfully';
            $this->response['total'] = $total_fund_transfers[0]['total'];
            $this->response['data'] = $fund_transfer_details;
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'No fund transfer has been made yet';
            $this->response['total'] = "0";
            $this->response['data'] = array();
        }

        print_r(json_encode($this->response));
    }

    public function update_user()
    {
        /*
            username:hiten
            mobile:7852347890 {optional}
            email:amangoswami@gmail.com	{optional}
            //optional parameters
            old:12345
            new:345234
            driving_license : FILE {optional}
        */
        if (!$this->verify_token()) {
            return false;
        }
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
            exit();
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
        $this->form_validation->set_rules('email', 'Email', 'xss_clean|trim|valid_email|edit_unique[users.id.' . $user_id . ']');
        $this->form_validation->set_rules('mobile', 'Mobile', 'xss_clean|trim|numeric|edit_unique[users.id.' . $this->input->post('user_id', true) . ']');

        $this->form_validation->set_rules('username', 'Username', 'xss_clean|trim');
        $delivery_boy_data = fetch_details('users', ['id' => $user_id], 'driving_license');
        $driving_license = explode(',', $delivery_boy_data[0]['driving_license']);
        if (isset($user_id)) {
            if (isset($_FILES) && !empty($_FILES) && !empty($_FILES['driving_license']['name'][0]) && count($_FILES['driving_license']['name']) < 2) {
                $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
            } elseif (isset($driving_license) && !empty($driving_license[0]) && count($driving_license) < 2) {
                $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
            }
            if (isset($_FILES) && !empty($_FILES) && !empty($_FILES['driving_license']['name'][0]) && count($_FILES['driving_license']['name']) > 2) {
                $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
            } elseif (isset($driving_license) && !empty($driving_license[0]) && count($driving_license) > 2) {
                $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
            }
        }

        if (!empty($_POST['old']) || !empty($_POST['new'])) {
            $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required|xss_clean');
            $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');
        }


        $tables = $this->config->item('tables', 'ion_auth');
        if (!$this->form_validation->run()) {
            if (validation_errors()) {
                $response['error'] = true;
                $response['message'] = strip_tags(validation_errors());
                echo json_encode($response);
                return false;
                exit();
            }
        } else {
            //Driving license

            if (!file_exists(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH)) {
                mkdir(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH, 0777);
            }

            $temp_array = array();
            $files = $_FILES;
            $images_new_name_arr = array();
            $images_info_error = "";
            $allowed_media_types = implode('|', allowed_media_types());
            $config = [
                'upload_path' =>  FCPATH . DELIVERY_BOY_DOCUMENTS_PATH,
                'allowed_types' => $allowed_media_types,
                'max_size' => 8000,
            ];

            if (!empty($_FILES['driving_license']['name']) && isset($_FILES['driving_license']['name']) && !empty($files['driving_license']['name'][0])) {
                $other_image_cnt = count((array)$_FILES['driving_license']['name']);

                $other_img = $this->upload;
                $other_img->initialize($config);

                for ($i = 0; $i < $other_image_cnt; $i++) {

                    if (!empty($_FILES['driving_license']['name'][$i])) {

                        $_FILES['temp_image']['name'] = $files['driving_license']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['driving_license']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['driving_license']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['driving_license']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['driving_license']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $images_info_error = 'driving_license :' . $images_info_error . ' ' . strip_tags($other_img->display_errors());
                        } else {
                            $temp_array = $other_img->data();
                            resize_review_images($temp_array, FCPATH . DELIVERY_BOY_DOCUMENTS_PATH);
                            $images_new_name_arr[$i] = DELIVERY_BOY_DOCUMENTS_PATH . $temp_array['file_name'];
                        }
                    } else {
                        $_FILES['temp_image']['name'] = $files['driving_license']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['driving_license']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['driving_license']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['driving_license']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['driving_license']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $images_info_error = strip_tags($other_img->display_errors());
                        }
                    }
                }
                //Deleting Uploaded attachments if any overall error occured
                if ($images_info_error != NULL || !$this->form_validation->run()) {
                    if (isset($images_new_name_arr) && !empty($images_new_name_arr || !$this->form_validation->run())) {
                        foreach ($images_new_name_arr as $key => $val) {
                            unlink(FCPATH . DELIVERY_BOY_DOCUMENTS_PATH . $images_new_name_arr[$key]);
                        }
                    }
                }
            }


            if ($images_info_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] =  $images_info_error;
                print_r(json_encode($this->response));
                return false;
            }

            if (!empty($_POST['old']) || !empty($_POST['new'])) {
                $identity = ($identity_column == 'mobile') ? 'mobile' : 'email';
                $res = fetch_details('users', ['id' => $user_id], '*');
                if (!empty($res) && $this->ion_auth->in_group('delivery_boy', $res[0]['id'])) {
                    if (!$this->ion_auth->change_password($res[0][$identity], $this->input->post('old'), $this->input->post('new'))) {
                        // if the login was un-successful
                        $response['error'] = true;
                        $response['message'] = strip_tags($this->ion_auth->errors());
                        echo json_encode($response);
                        return;
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'User does not exists';
                    echo json_encode($response);
                    return;
                }
            }
            $set = [];
            if (isset($_POST['username']) && !empty($_POST['username'])) {
                $set['username'] = $this->input->post('username', true);
            }
            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $set['email'] = $this->input->post('email', true);
            }
            if (isset($_POST['mobile']) && !empty($_POST['mobile'])) {
                $set['mobile'] = $this->input->post('mobile', true);
            }
            if (isset($_FILES['driving_license']) && !empty($_FILES['driving_license'])) {
                $set['driving_license'] = isset($images_new_name_arr) && !empty($images_new_name_arr) ? implode(',', (array)$images_new_name_arr) : implode(',', (array)$delivery_boy_data[0]['driving_license']);;
            }
            $set = escape_array($set);

            $this->db->set($set)->where('id', $user_id)->update($tables['login_users']);
            $data = fetch_details('users', ['id' => $user_id], 'driving_license')[0];
            $driving_license_data = [];
            if (isset($data['driving_license']) && !empty($data['driving_license'])) {
                $driving_license = explode(',', $data['driving_license']);
                foreach ($driving_license as $row) {
                    array_push($driving_license_data, base_url($row));
                }
            }
            $response['error'] = false;
            $response['message'] = 'Profile Update Succesfully';
            $response['driving_license'] = isset($data['driving_license']) && !empty($data['driving_license']) ? $driving_license_data : [];
            echo json_encode($response);
            return;
        }
    }
    // 6. update_fcm
    public function update_fcm()
    {

        /* Parameters to be passed
            fcm_id: FCM_ID
            device_type: android/ios
        */

        if (!$this->verify_token()) {
            return false;
        }
        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
        update_details(['platform_type' => $_POST['device_type']], ['id' => $user_id], 'users');
        $user_res = update_details(['fcm_id' => $_POST['fcm_id']], ['id' => $user_id], 'users');

        if ($user_res) {
            $response['error'] = false;
            $response['message'] = 'Updated Successfully';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        } else {
            $response['error'] = true;
            $response['message'] = 'Updation Failed !';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        }
    }
    // 7. reset_password
    public function reset_password()
    {
        /* Parameters to be passed
            user_id:12
            new: pass@123
        */


        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
            exit();
        }
        $this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('new', 'New Password', 'trim|xss_clean|required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $res = fetch_details('users', ['mobile' => $_POST['mobile_no']]);
        if (!empty($res) && $this->ion_auth->in_group('delivery_boy', $res[0]['id'])) {
            $identity = ($identity_column  == 'email') ? $res[0]['email'] : $res[0]['mobile'];
            if (!$this->ion_auth->reset_password($identity, $_POST['new'])) {
                $response['error'] = true;
                $response['message'] = strip_tags($this->ion_auth->messages());;
                $response['data'] = array();
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = false;
                $response['message'] = 'Password Reset Successfully';
                $response['data'] = array();
                echo json_encode($response);
                return false;
            }
        } else {
            $response['error'] = false;
            $response['message'] = 'User does not exists !';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        }
    }

    /* 8.get_notifications
        id:114
        offset:0        // {optional}
        limit:10        // {optional}
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        search:search_value         // {optional}
        get_notifications:1
    */
    public function get_notifications()
    {


        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'id';
            $res = $this->notification_model->get_notifications($offset, $limit, $sort, $order);
            $this->response['error'] = false;
            $this->response['message'] = 'Notification Retrieved Successfully';
            $this->response['total'] = $res['total'];
            $this->response['data'] = $res['data'];
        }

        print_r(json_encode($this->response));
    }

    //9. verify-user
    public function verify_user()
    {
        /* Parameters to be passed
            mobile: 9874565478
            email: test@gmail.com // { optional }
            is_forgot_password: 1

        */

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('country_code', 'Country code', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        } else {
            $mobile = $this->input->post('mobile', true);
            $country_code = $this->input->post('country_code', true);

            $user_data = fetch_details('users', ['mobile' => $mobile], 'id');

            if ($this->ion_auth->is_delivery_boy($user_data[0]['id'])) {

                if (isset($_POST['is_forgot_password'])  && ($_POST['is_forgot_password'] == 1) && !is_exist(['mobile' => $_POST['mobile']], 'users')) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Mobile is not register yet !';
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return;
                } else {
                    $auth_settings = get_settings('authentication_settings', true);
                    if ($auth_settings['authentication_method'] == "sms") {
                        $mobile_data = array(
                            'mobile' => $mobile // Replace $mobile with the actual mobile value you want to insert
                        );

                        if (isset($_POST['mobile']) && !is_exist(['mobile' => $_POST['mobile']], 'otps')) {
                            $this->db->insert('otps', $mobile_data);
                        }
                        $otps = fetch_details('otps', ['mobile' => $mobile]);
                        $query = $this->db->select(' * ')->where('id', $otps[0]['id'])->get('otps')->result_array();
                        $otp = random_int(100000, 999999);
                        $data = set_user_otp($mobile, $otp, $country_code);
                        $this->response['error'] = false;
                        $this->response['message'] = 'Ready to sent OTP request from sms!';
                        print_r(json_encode($this->response));
                        return;
                    }
                }
                if (isset($_POST['mobile']) && is_exist(['mobile' => $_POST['mobile']], 'users')) {
                    $user_id = fetch_details('users', ['mobile' => $_POST['mobile']], 'id');

                    //Check if this mobile no. is registered as a delivery boy or not.
                    if (!$this->ion_auth->in_group('delivery_boy', $user_id[0]['id'])) {
                        $this->response['error'] = true;
                        $this->response['message'] = 'Mobile number / email could not be found!';
                        print_r(json_encode($this->response));
                        return;
                    } else {
                        $this->response['error'] = false;
                        $this->response['message'] = 'Mobile number is registered. ';
                        print_r(json_encode($this->response));
                        return;
                    }
                }
                if (isset($_POST['email']) && is_exist(['email' => $_POST['email']], 'users')) {
                    $this->response['error'] = false;
                    $this->response['message'] = 'Email is registered.';
                    print_r(json_encode($this->response));
                    return;
                }
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'You are not allowed to verify as a delivery boy!';
                print_r(json_encode($this->response));
                return;
            }

            $this->response['error'] = true;
            $this->response['message'] = 'Mobile number / email could not be found!';
            print_r(json_encode($this->response));
            return;
        }
    }

    //verify_otp
    public function verify_otp()
    {
        /* 
        otp: 123456
        phone number: 9876543210
        */

        $mobile = $this->input->post('mobile');
        $auth_settings = get_settings('authentication_settings', true);
        if ($auth_settings['authentication_method'] == "sms") {
            $otps = fetch_details('otps', ['mobile' => $mobile]);
            $time = $otps[0]['created_at'];
            $time_expire = checkOTPExpiration($time);
            if (isset($otps) && !empty($otps)) {
                if ($time_expire['error'] == 1) {
                    $response['error'] = true;
                    $response['message'] = $time_expire['message'];
                    echo json_encode($response);
                    return false;
                }
                if (($otps[0]['otp'] != $_POST['otp'])) {
                    $response['error'] = true;
                    $response['message'] = "OTP not valid , check again ";
                    echo json_encode($response);
                    return false;
                } else {
                    update_details(['varified' => 1], ['mobile' => $mobile], 'otps');
                    $this->response['error'] = false;
                    $this->response['message'] = 'Otp Verified Successfully';
                    $this->response['data'] = array();
                }
            }
            $this->response['error'] = true;
            $this->response['message'] = 'OTP not valid , check again ';
            $this->response['data'] = array();
        }

        $this->response['error'] = true;
        $this->response['message'] = 'The admin has not enabled any authentication method.';
        $this->response['data'] = array();

        print_r(json_encode($this->response));
    }

    //resend_otp
    public function resend_otp()
    {
        /*
        mobile:9876543210
        */

        $mobile = $this->input->post('mobile');
        $country_code = $this->input->post('country_code');
        $auth_settings = get_settings('authentication_settings', true);
        if ($auth_settings['authentication_method'] == "sms") {
            $otps = fetch_details('otps', ['mobile' => $mobile]);

            $query = $this->db->select(' * ')->where('id', $otps[0]['id'])->get('otps')->result_array();

            $otp = random_int(100000, 999999);
            $data = set_user_otp($mobile, $otp, $country_code);
            $this->response['error'] = false;
            $this->response['message'] = 'Ready to sent OTP request from sms!';
            $this->response['data'] = $otps;
            print_r(json_encode($this->response));
            return;
        }
    }

    public function get_settings()
    {
        /* 
            type : delivery_boy_privacy_policy / delivery_boy_terms_conditions
        */

        $settings = get_settings('system_settings', true);
        $shipping_method = get_settings('shipping_method', true);

        $this->form_validation->set_rules('type', 'Setting Type', 'trim|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $allowed_settings = array('delivery_boy_terms_conditions', 'delivery_boy_privacy_policy', 'currency', 'authentication_settings', 'sms_gateway_settings', 'shipping_method');
            $type = $_POST['type'];
            $settings_res = get_settings($type);

            if (!in_array($type, $allowed_settings)) {
                $this->response['error'] = false;
                $this->response['message'] = 'Currency';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return false;
                exit();
            }

            if (!empty($settings_res)) {

                $this->response['error'] = false;
                $this->response['message'] = 'Settings retrieved successfully';
                $this->response['data'] = $settings_res;
                $this->response['currency'] = get_settings('currency');
                $this->response['authentication_settings'] = get_settings('authentication_settings', true);
                $this->response['system_settings'] = get_settings('system_settings', true);
                $this->response['supported_locals'] = $settings['supported_locals'];
                $this->response['decimal_point'] = $settings['decimal_point'];
                $this->response['is_delivery_boy_app_under_maintenance'] = $settings['is_delivery_boy_app_under_maintenance'];
                $this->response['message_for_delivery_boy_app'] = $settings['message_for_delivery_boy_app'];
                $this->response['shipping_method'] = $shipping_method;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Settings Not Found';
                $this->response['data'] = array();
            }
            print_r(json_encode($this->response));
        }
    }

    //11.send_withdrawal_request
    public function send_withdrawal_request()
    {
        /* 
            payment_address: 12343535
            amount: 560           
        */

        if (!$this->verify_token()) {
            return false;
        }
        $this->form_validation->set_rules('payment_address', 'Payment Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric|greater_than[0]');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
            $payment_address = $this->input->post('payment_address', true);
            $amount = $this->input->post('amount', true);
            $userData = fetch_details('users', ['id' => $user_id], 'balance');

            if (!empty($userData)) {

                if ($_POST['amount'] <= $userData[0]['balance']) {

                    $data = [
                        'user_id' => $user_id,
                        'payment_address' => $payment_address,
                        'payment_type' => 'delivery_boy',
                        'amount_requested' => $amount,
                    ];
                    if (insert_details($data, 'payment_requests')) {
                        $this->Delivery_boy_model->update_balance($amount, $user_id, 'deduct');
                        $userData = fetch_details('users', ['id' => $user_id], 'balance');
                        $this->response['error'] = false;
                        $this->response['message'] = 'Withdrawal Request Sent Successfully';
                        $this->response['data'] = $userData[0]['balance'];
                    } else {
                        $this->response['error'] = true;
                        $this->response['message'] = 'Cannot sent Withdrawal Request.Please Try again later.';
                        $this->response['data'] = array();
                    }
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = 'You don\'t have enough balance to sent the withdraw request.';
                    $this->response['data'] = array();
                }

                print_r(json_encode($this->response));
            }
        }
    }

    //13.get_withdrawal_request
    public function get_withdrawal_request()
    {
        /* 
            limit:10
            offset:10
        */

        if (!$this->verify_token()) {
            return false;
        }


        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : null;
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : null;
            $userData = fetch_details('payment_requests', ['user_id' => $user_id], '*', $limit, $offset, 'payment_requests.id', 'desc');

            $this->response['error'] = false;
            $this->response['message'] = 'Withdrawal Request Retrieved Successfully';
            $this->response['data'] = $userData;
            $this->response['total'] = strval(count($userData));
            print_r(json_encode($this->response));
        }
    }

    /* to update the status of an individual status */
    public function update_order_consignment_status()
    {
        /*
            consignment_id:1
            status : received / processed / shipped / delivered / cancelled / returned
            otp:value      //{required when status is delivered}
         */

        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('consignment_id', 'Consignment ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('otp', 'otp', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|xss_clean|in_list[received,processed,shipped,delivered,cancelled,returned]');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $delivery_boy_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
        if (!is_exist(['id' => $_POST['consignment_id'], 'delivery_boy_id' => $delivery_boy_id], 'consignments')) {
            $this->response['error'] = true;
            $this->response['message'] = "You Don't Have Access to Update Status";
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $res = validate_order_status($_POST['consignment_id'], $_POST['status'], 'consignments');
        if ($res['error']) {
            $this->response['error'] = true;
            $this->response['message'] = $res['message'];
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $system_settings = get_settings('system_settings', true);
        // $otp_system = $system_settings['is_delivery_boy_otp_setting_on'];

        if ($res['error']) {
            $this->response['error'] = true;
            $this->response['message'] = $res['message'];
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $consignment = fetch_details('consignments', ['id' => $_POST['consignment_id']], '*');
        $consignment_items = fetch_details('consignment_items', ['consignment_id' => $consignment[0]['id']], '*');

        if (empty($consignment) && empty($consignment_items)) {
            $this->response['error'] = true;
            $this->response['message'] = "Consignment Not Found.";
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $order_item_ids = array_column($consignment_items, 'order_item_id');
        $order_id = $consignment[0]['order_id'];

        $order_item_res = $this->db->select('oi.*, oi.id AS order_item_id,(SELECT COUNT(id) FROM order_items WHERE order_id = oi.order_id) AS order_counter,(SELECT COUNT(active_status) FROM order_items WHERE active_status = "cancelled" AND order_id = oi.order_id) AS order_cancel_counter,(SELECT COUNT(active_status) FROM order_items WHERE active_status = "returned" AND order_id = oi.order_id) AS order_return_counter,(SELECT COUNT(active_status) FROM order_items WHERE active_status = "delivered" AND order_id = oi.order_id) AS order_delivered_counter,(SELECT COUNT(active_status) FROM order_items WHERE active_status = "processed" AND order_id = oi.order_id) AS order_processed_counter,(SELECT COUNT(active_status) FROM order_items WHERE active_status = "shipped" AND order_id = oi.order_id) AS order_shipped_counter,(SELECT status FROM orders WHERE id = oi.order_id) AS order_status')
            ->from('order_items oi')
            ->where_in('oi.id', $order_item_ids)
            ->get()
            ->result_array();

        $otp_system = $order_item_res[0]['deliveryboy_otp_setting_on'];
        if ($_POST['status'] == 'delivered') {
            if ($otp_system == 1) {

                if (!validate_otp(otp: $_POST['otp'], consignment_id: $_POST['consignment_id'])) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Invalid OTP supplied!';
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
            }
        }

        $order_method = fetch_details('orders', ['id' => $order_id], 'payment_method');
        $firebase_project_id = $this->data['firebase_project_id'];
        $service_account_file = $this->data['service_account_file'];
        if ($order_method[0]['payment_method'] == 'bank_transfer') {
            $bank_receipt = fetch_details('order_bank_transfer', ['order_id' => $order_id]);
            $transaction_status = fetch_details('transactions', ['order_id' => $order_id], 'status');
            if (empty($bank_receipt) || strtolower($transaction_status[0]['status']) != 'success') {
                $this->response['error'] = true;
                $this->response['message'] = "Order Status can not update, Bank verification is remain from transactions.";
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return false;
            }
        }
        if ($this->Order_model->update_order(['status' => $_POST['status']], ['id' => $_POST['consignment_id']], true, 'consignments')) {
            $this->Order_model->update_order(['active_status' => $_POST['status']], ['id' => $_POST['consignment_id']], false, 'consignments');
            if ($_POST['status'] == 'cancelled' || $_POST['status'] == 'returned') {
                process_refund($order_item_res[0]['id'], $_POST['status'], 'order_items');
                if (trim($_POST['status']) == 'cancelled') {
                    $data = fetch_details('order_items', ['id' => $_POST['consignment_id']], 'product_variant_id,quantity');
                    update_stock($data[0]['product_variant_id'], $data[0]['quantity'], 'plus');
                }
            }
            foreach ($consignment_items as $item) {
                $this->Order_model->update_order(['status' => $_POST['status']], ['id' => $item['order_item_id']], true, 'order_items');
                $this->Order_model->update_order(['active_status' => $_POST['status']], ['id' => $item['order_item_id']], false, 'order_items');

                // Update login id in order_item table
                update_details(['updated_by' => $_SESSION['user_id']], ['id' =>  $item['order_item_id']], 'order_items');
            }
            if (($order_item_res[0]['order_counter'] == intval($order_item_res[0]['order_cancel_counter']) + 1 && $_POST['status'] == 'cancelled') ||  ($order_item_res[0]['order_counter'] == intval($order_item_res[0]['order_return_counter']) + 1 && $_POST['status'] == 'returned') || ($order_item_res[0]['order_counter'] == intval($order_item_res[0]['order_delivered_counter']) + 1 && $_POST['status'] == 'delivered') || ($order_item_res[0]['order_counter'] == intval($order_item_res[0]['order_processed_counter']) + 1 && $_POST['status'] == 'processed') || ($order_item_res[0]['order_counter'] == intval($order_item_res[0]['order_shipped_counter']) + 1 && $_POST['status'] == 'shipped')) {

                $user = fetch_details('orders', ['id' => $order_id], 'user_id');
                $user_id = $user[0]['user_id'];
                $settings = get_settings('system_settings', true);
                $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
                $user_res = fetch_details('users', ['id' => $user_id], 'username,fcm_id,email,mobile,platform_type');
                $fcm_ids = array();
                //custom message
                if ($_POST['status'] == 'received') {
                    $type = ['type' => "customer_order_received"];
                } elseif ($_POST['status'] == 'processed') {
                    $type = ['type' => "customer_order_processed"];
                } elseif ($_POST['status'] == 'shipped') {
                    $type = ['type' => "customer_order_shipped"];
                } elseif ($_POST['status'] == 'delivered') {
                    $type = ['type' => "customer_order_delivered"];
                } elseif ($_POST['status'] == 'cancelled') {
                    $type = ['type' => "customer_order_cancelled"];
                } elseif ($_POST['status'] == 'returned') {
                    $type = ['type' => "customer_order_returned"];
                }
                $custom_notification = fetch_details('custom_notifications', $type, '');
                $hashtag_cutomer_name = '< cutomer_name >';
                $hashtag_order_id = '< order_item_id >';
                $hashtag_application_name = '< application_name >';
                $string = json_encode($custom_notification[0]['message'], JSON_UNESCAPED_UNICODE);
                $hashtag = html_entity_decode($string);
                $data = str_replace(array($hashtag_cutomer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]['username'], $order_id, $app_name), $hashtag);
                $message = output_escaping(trim($data, '"'));
                $customer_msg = (!empty($custom_notification)) ? $message :  'Hello Dear ' . $user_res[0]['username'] . 'Order status updated to' . $_POST['status'] . ' for your order ID #' . $order_id . ' please take note of it! Thank you for shopping with us. Regards ' . $app_name . '';
                if (!empty($user_res[0]['fcm_id']) && isset($firebase_project_id) && isset($service_account_file) && !empty($firebase_project_id) && !empty($service_account_file)) {
                    $fcmMsg = array(
                        'title' => (!empty($custom_notification)) ? $custom_notification[0]['title'] : "Order status updated",
                        'body' => $customer_msg,
                        'type' => "order",
                    );

                    // Step 1: Group by platform
                    $groupedByPlatform = [];
                    foreach ($user_res as $item) {
                        $platform = $item['platform_type'];
                        $groupedByPlatform[$platform][] = $item['fcm_id'];
                    }

                    // Step 2: Chunk each platform group into arrays of 1000
                    $fcm_ids = [];
                    foreach ($groupedByPlatform as $platform => $fcmIds) {
                        $fcm_ids[$platform] = array_chunk($fcmIds, 1000);
                    }

                    $fcm_ids[0][] = $fcm_ids;
                    send_notification($fcmMsg, $fcm_ids, $fcmMsg);
                }
                notify_event(
                    $type['type'],
                    ["customer" => [$user_res[0]['email']]],
                    ["customer" => [$user_res[0]['mobile']]],
                    ["orders.id" => $order_id]
                );
            }
        }
        $this->response['error'] = false;
        $this->response['message'] = 'Status Updated Successfully';
        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
        $this->response['data'] = array();
        print_r(json_encode($this->response));
        return false;
    }
    public function get_delivery_boy_cash_collection()
    {
        /* 
        status:             // {delivery_boy_cash (delivery boy collected) | delivery_boy_cash_collection (admin collected)}
        limit:25            // { default - 25 } optional
        offset:0            // { default - 0 } optional
        sort:               // { id } optional
        order:DESC/ASC      // { default - DESC } optional
        search:value        // {optional} 
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
            $filters['delivery_boy_id'] = $user_id;
            $filters['status'] = (isset($_POST['status']) && !empty(trim($_POST['status']))) ? $this->input->post('status', true) : '';
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 10;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'transactions.id';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : '';
            $tmpRow = $rows = array();
            $data = $this->Delivery_boy_model->get_delivery_boy_cash_collection($limit, $offset, $sort, $order, $search, (isset($filters)) ? $filters : null);
            if (isset($data['data']) && !empty($data['data'])) {
                foreach ($data['data'] as $row) {
                    $tmpRow['id'] = $row['id'];
                    $tmpRow['name'] = $row['name'];
                    $tmpRow['mobile'] = $row['mobile'];
                    $tmpRow['order_id'] = $row['order_id'];
                    $tmpRow['cash_received'] = $row['cash_received'];
                    $tmpRow['type'] = $row['type'];
                    $tmpRow['amount'] = $row['amount'];
                    $tmpRow['message'] = $row['message'];
                    $tmpRow['transaction_date'] = $row['transaction_date'];
                    $tmpRow['date'] = $row['date'];
                    if (isset($row['order_id']) && !empty($row['order_id']) && $row['order_id'] != "") {
                        $order_data = fetch_orders($row['id']);
                        $tmpRow['order_details'] = (isset($order_data['order_data'][0])) ? array($order_data['order_data'][0]) : [];
                    } else {
                        $tmpRow['order_details'] = [];
                    }
                    $rows[] = $tmpRow;
                }
                if ($data['error'] == false) {
                    $data['data'] = $rows;
                } else {
                    $data['data'] = array();
                }
            }
            print_r(json_encode($data));
        }
    }


    public function delete_delivery_boy()
    {
        /*
            user_id:15
            mobile:9874563214
            password:12345695
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return false;
        } else {
            $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
            $user_data = fetch_details('users', ['id' => $user_id, 'mobile' => $_POST['mobile']], 'id,username,password,active,mobile');
            if ($user_data) {
                $login = $this->ion_auth->login($this->input->post('mobile'), $this->input->post('password'), false);
                if ($login) {
                    $order_items = fetch_details('order_items', ['delivery_boy_id' => $user_id]);
                    //chec all the assign order item status which is not delivered yet
                    foreach ($order_items as $order_item) {

                        $order_item_status  = $order_item['active_status'];
                        if ($order_item_status != 'delivered' || $order_item_status != 'returned' || $order_item_status != 'cancelled') {
                            $this->response['error'] = true;
                            $this->response['message'] = 'You cannot delete Your account , orders is not delivered, please once assign it to other delivery boy or deliver all the orders';
                            print_r(json_encode($this->response));
                            return;
                            exit();
                        }
                    }
                    $user_group = fetch_details('users_groups', ['user_id' => $user_id], 'group_id');
                    if ($user_group[0]['group_id'] == '3') {
                        delete_details(['id' => $user_id], 'users');
                        delete_details(['user_id' => $user_id], 'users_groups');
                        $response['error'] = false;
                        $response['message'] = 'Delivery Boy Deleted Successfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Details Does\'s Match';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Details Does\'s Match';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'User Not Found';
            }
            echo json_encode($response);
            return;
        }
    }

    /*
    order_item_id:
    status : return_pickedup
*/
    function update_return_order_item_status()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('order_item_id', 'order_item_id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', 'status', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            // Get POST data
            $order_item_id = $this->input->post('order_item_id');
            $new_status = $this->input->post('status');

            // Check if the new status is 'return_pickedup'
            if ($new_status !== 'return_pickedup') {
                $this->response['error'] = true;
                $this->response['message'] = 'Invalid Status';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return;
            }

            // Fetch the current status from the database
            $order_item = $this->Order_model->getOrderItemById($order_item_id);
            $current_status = json_decode($order_item->status, true);

            // Check if the current status is a valid array, otherwise initialize it
            if (!is_array($current_status)) {
                $current_status = [];
            }

            // Check if the last status is return_pickedup
            $last_status = end($current_status);
            if ($last_status[0] === 'return_pickedup') {
                $this->response['error'] = true;
                $this->response['message'] = 'Status already updated';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return;
            }
            if ($last_status[0] == 'returned') {
                $this->response['error'] = true;
                $this->response['message'] = 'Status is already returned you can not set it as pickedup.';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return;
            }

            // Create new status entry with timestamp
            $current_time = date("Y-m-d H:i:s"); // Use desired date format
            $new_entry = [$new_status, $current_time];

            // Append new status entry to the array
            $current_status[] = $new_entry;

            // Encode the updated status array back to JSON
            $updated_status = json_encode($current_status);

            // Prepare data for update
            $update_data = [
                'active_status' => $new_status,
                'status' => $updated_status
            ];

            // Update the status and active_status in the database
            $result = $this->Order_model->updateOrderItemStatus($order_item_id, $update_data);

            if ($result) {
                $order_item_data = $this->Order_model->get_return_order_items_list(from_app: 1, order_item_id: $order_item_id, is_print: 1);
                $this->response['error'] = false;
                $this->response['message'] = 'Status Updated Successfully';
                $this->response['data'] = $order_item_data[0];
                print_r(json_encode($this->response));
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Status Not Updated';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
            }
        }
    }


    public function view_return_order_items()
    {
        if (!$this->verify_token()) {
            return false;
        }
        $deliveryBoyId = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';
        $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : null;
        $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : null;
        return $this->Order_model->get_return_order_items_list($deliveryBoyId, $offset, $limit, from_app: $from_app = '1');
    }
}
