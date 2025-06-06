<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Chat_Api extends CI_Controller
{

    /*
---------------------------------------------------------------------------
Defined Methods:-
---------------------------------------------------------------------------

    1. get_groups    
    2. get_chat_history
    3. Load_chat
    4. delete_msg
    
---------------------------------------------------------------------------
---------------------------------------------------------------------------

*/
    private  $user_details = [];

    protected $excluded_routes = [
        "app/v1/Chat_Api/search_user",
        "app/v1/Chat_Api/get_supporters",

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

        $this->load->library(['upload', 'jwt', 'ion_auth', 'form_validation', 'Key']);
        $this->load->model(['Customer_model', 'chat_model', 'notification_model', 'Setting_model', 'media_model']);
        $this->load->helper(['language', 'string']);
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
        $this->output->set_content_type(get_mime_by_extension(base_url('api-doc.txt')));
        $this->output->set_output(file_get_contents(base_url('api-doc.txt')));
    }
    public function generate_token()
    {
        $payload = [
            'iat' => time(), /* issued at time */
            'iss' => 'eshop',
            'exp' => time() + (60 * 60 * 24 * 365), /* expires after 1 minute */
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
            JWT::$leeway = 6000000000;
            $flag = true; //For payload indication that it return some data or throws an expection.
            $error = true; //It will indicate that the payload had verified the signature and hash is valid or not.
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

    public function get_chat_history()
    {

        /*
            user_id:15   
            limit : 10
            offset : 0
        */
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';


        $limit = (isset($_POST['limit'])) ? $this->input->post('limit', true) : 10;
        $offset = (isset($_POST['offset'])) ? $this->input->post('offset', true) : 0;
        $user = array();
        $i = 0;
        $type = 'person';
        $members = $this->chat_model->get_chat_history($user_id, $limit, $offset);

        foreach ($members as $row) {
            $to_id = (isset($user_id)) ? $user_id : '';
            $from_id = $row['from_id'];
            if (isset($from_id) && !empty($from_id)) {
                $unread_meg = $this->chat_model->get_unread_msg_count($type, $from_id, $to_id);
            }
            $user[$i] = $row;
            $user[$i]['unread_msg'] = $unread_meg;
            $user[$i]['image'] = base_url() . CHAT_MEDIA_PATH .$user[$i]['image'];
            $user[$i]['opponent_user_profile'] = base_url() . USER_IMG_PATH .$user[$i]['opponent_user_profile'];

            $date = strtotime('now');
            if ($to_id == $row['opponent_user_id']) {
                $user[$i]['is_online'] = 1;
            } else {
                if ($row['last_online'] > $date) {
                    $user[$i]['is_online'] = 1;
                } else {
                    $user[$i]['is_online'] = 0;
                }
            }
            $i++;
        }

        if (isset($members)) {
            $this->response['error'] = false;
            $this->response['message'] = "chat retrieved successfully !";
            $this->response['data'] = $user;
        } else {
            $this->response['error'] = true;
            $this->response['message'] = "chat Not Found !";
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    public function load_chat()
    {
        /*
            
            from_id : 1 // if type is person then pass user id or pass group id
            to_id : 2 //current user_id
            type : person {person / group}
            offset : 0  
            limit : 10
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('from_id', 'From Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('to_id', 'To Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('type', 'Type', 'trim|xss_clean');


        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {

            $limit = (isset($_POST['limit'])) ? $this->input->post('limit', true) : 50;
            $offset = (isset($_POST['offset'])) ? $this->input->post('offset', true) : 0;
            $from_id = (isset($_POST['from_id'])) ? $this->input->post('from_id', true) : '';
            $to_id = (isset($_POST['to_id'])) ? $this->input->post('to_id', true) : '';
            $type = (isset($_POST['type'])) ? $this->input->post('type', true) : '';

            $messages = $this->chat_model->load_chat($from_id, $to_id, $type,  $offset, $limit, 'id', "DESC");

            if ($messages['total_msg'] == 0) {

                $message['error'] = false;
                $message['message'] = "message Not Found !";
                $message['data']['total_msg'] = "0";
                $message['data']['msg'] = [];

                print_r(json_encode($message));
                return false;
            }

            $i = 0;
            $message['total_msg'] = $messages['total_msg'];

            foreach ($messages['msg'] as $row) {

                $media_files = [];
                $bulkData = [];
                $message['msg'][$i] = $row;
                $media_files = $this->chat_model->get_media($row['id']);
                if (isset($media_files) && !empty($media_files)) {
                    for ($j = 0; $j < count($media_files); $j++) {
                        $file_extention = explode('.', $media_files[$j]['original_file_name']);
                        $media_files[$j]['file_extension'] = end($file_extention);
                        $media_files[$j]['file_url'] = base_url('uploads/chat_media/' . $media_files[$j]['original_file_name']);
                    }
                }

                $message['msg'][$i]['media_files'] = !empty($media_files) ? $media_files : [];
                $message['msg'][$i]['text'] = $row['message'];
                if ($row['from_id'] == $to_id) {
                    $message['msg'][$i]['position'] = 'right';
                } else {
                    $message['msg'][$i]['position'] = 'left';
                }
                $i++;
            }

            if (!empty($message)) {
                $this->response['error'] = false;
                $this->response['message'] = "message retrieved successfully !";
                $this->response['data'] = $message;
            } else {
                $this->response['error'] = false;
                $this->response['message'] = "message Not Found !";
                $this->response['data']['total_msg'] = "0";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }
    public function delete_msg()
    {
        /*
           msg_id : 13
           from_id : 1 // current user_id
            
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('msg_id', 'Message Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('from_id', 'From Id', 'trim|numeric|required|xss_clean');


        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {

            $from_id = (isset($_POST['from_id'])) ? $this->input->post('from_id') : '';
            $msg_id = (isset($_POST['msg_id'])) ? $this->input->post('msg_id') : '';

            if ($this->chat_model->delete_msg($from_id, $msg_id)) {
                $this->response['error'] = false;
                $this->response['message'] = "Message deleted successfully !";
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Message not deleted !";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    public function switch_chat()
    {
        /*
            from_id : 1 // if type is person then pass user id or pass group id
            type : person {person / group}
            user_id : 1 {current user_id} //pass when type is group
            
        */
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';

        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('from_id', 'From Id', 'trim|numeric|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {

            $from_id = (isset($_POST['from_id'])) ? $this->input->post('from_id') : '';
            $type = (isset($_POST['type'])) ? $this->input->post('type') : '';
            $users = $this->chat_model->switch_chat($from_id, $type);
            $user = array();
            $i = 0;
            foreach ($users as $row) {

                $user[$i] = $row;
                if ($type == 'person') {
                    $user[$i]['picture'] = $row['username'];

                    $date = strtotime('now');

                    if ($row['last_online'] > $date) {
                        $user[$i]['is_online'] = 1;
                    } else {
                        $user[$i]['is_online'] = 0;
                    }
                } else {
                    $user[$i]['picture'] = '#';

                    if ($this->chat_model->check_group_admin($row['id'], $user_id)) {
                        $user[$i]['is_admin'] = true;
                    } else {
                        $user[$i]['is_admin'] = false;
                    }
                }

                $i++;
            }

            if (!empty($user)) {
                $this->response['error'] = false;
                $this->response['message'] = "Data fetched successfully !";
                $this->response['data'] = $user;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Data not available !";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    public function mark_msg_read()
    {
        /*
            from_id : 1 // if type is person then pass user id or pass group id
            type : person {person / group}
            user_id : 1 {current user_id} //pass when type is group
            
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('from_id', 'From Id', 'trim|numeric|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {
            $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';

            $from_id = (isset($_POST['from_id'])) ? $this->input->post('from_id') : '';
            $to_id = (isset($user_id)) ? $user_id : '';
            $type = (isset($_POST['type'])) ? $this->input->post('type') : '';

            if ($this->chat_model->mark_msg_read($type, $from_id, $to_id)) {
                $this->response['error'] = false;
                $this->response['message'] = "Message marked as read !";
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Message not marked as read !";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    public function send_msg()
    {
        /*
            type : person {person / group}
            from_id : 1 // current user id
            to_id : 1 // receiver user id
              : this is test msg
            documents[] : FILE {optional}
            
        */
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('from_id', 'From Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('to_id', 'To Id', 'trim|numeric|required|xss_clean');
        if (!empty($_FILES)) {
            $this->form_validation->set_rules('message', 'Message', 'trim|xss_clean');
        } else {
            $this->form_validation->set_rules('message', 'Message', 'trim|required|xss_clean');
        }
        
        $this->form_validation->set_rules('documents', 'documents', 'trim|xss_clean');


        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {

            $type = (isset($_POST['type'])) ? $this->input->post('type') : '';
            $from_id = (isset($_POST['from_id'])) ? $this->input->post('from_id') : '';
            $to_id = (isset($_POST['to_id'])) ? $this->input->post('to_id') : '';
            $message = (isset($_POST['message'])) ? $this->input->post('message') : '';

            $data = array(
                'type' => $type,
                'from_id' => $from_id,
                'to_id' => $to_id,
                'message' => $message
            );
            $msg_id = $this->chat_model->send_msg($data);

            if (!empty($_FILES['documents']['name'])) {

                $year = date('Y');
                $target_path = FCPATH . CHAT_MEDIA_PATH  . '/';
                $sub_directory = CHAT_MEDIA_PATH  . '/';

                if (!file_exists($target_path)) {
                    mkdir($target_path, 0777, true);
                }

                $temp_array = $media_ids = $other_images_new_name = array();
                $files = $_FILES;
                $other_image_info_error = "";
                $allowed_media_types = implode('|', allowed_media_types());
                $config['upload_path'] = $target_path;
                $config['allowed_types'] = $allowed_media_types;
                $other_image_cnt = count((array)$_FILES['documents']['name']);
                $other_img = $this->upload;
                $other_img->initialize($config);
                for ($i = 0; $i < $other_image_cnt; $i++) {
                    if (!empty($_FILES['documents']['name'][$i])) {

                        $_FILES['temp_image']['name'] = $files['documents']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['documents']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['documents']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['documents']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['documents']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $other_image_info_error = $other_image_info_error . ' ' . $other_img->display_errors();
                        } else {
                            $temp_array = $other_img->data();

                            $temp_array['sub_directory'] = $sub_directory;
                            $media_ids[] = $media_id = $this->media_model->set_media($temp_array); /* set media in database */
                            if (strtolower($temp_array['image_type']) != 'gif')
                                resize_image($temp_array,  $target_path, $media_id);
                            $other_images_new_name[$i] = $temp_array['file_name'];
                        }
                        $data = array(
                            'original_file_name' => $_FILES['temp_image']['name'],
                            'file_name' => $_FILES['temp_image']['tmp_name'],
                            'file_extension' => $_FILES['temp_image']['type'],
                            'file_size' => $_FILES['temp_image']['size'],
                            'user_id' => $from_id,
                            'message_id' => $msg_id
                        );
                        $file_id = $this->chat_model->add_file($data);
                        $this->chat_model->add_media_ids_to_msg($msg_id, $file_id);
                    } else {

                        $_FILES['temp_image']['name'] = $files['documents']['name'][$i];
                        $_FILES['temp_image']['type'] = $files['documents']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $files['documents']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $files['documents']['error'][$i];
                        $_FILES['temp_image']['size'] = $files['documents']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $other_image_info_error = $other_img->display_errors();
                        }
                        $data = array(
                            'original_file_name' => $_FILES['temp_image']['name'],
                            'file_name' => $_FILES['temp_image']['tmp_name'],
                            'file_extension' => $_FILES['temp_image']['type'],
                            'file_size' => $_FILES['temp_image']['size'],
                            'user_id' => $from_id,
                            'message_id' => $msg_id
                        );
                        $file_id = $this->chat_model->add_file($data);
                        $this->chat_model->add_media_ids_to_msg($msg_id, $file_id);
                    }
                }

                // Deleting Uploaded Images if any overall error occured
                if ($other_image_info_error != NULL) {
                    if (isset($other_images_new_name) && !empty($other_images_new_name)) {
                        foreach ($other_images_new_name as $key => $val) {
                            unlink($target_path . $other_images_new_name[$key]);
                        }
                    }
                }
            }


            $messages = $this->chat_model->get_msg_by_id($msg_id, $to_id, $from_id, $type);
            $message = array();
            $i = 0;
            if ($messages == 1) {
                $response['error'] = true;
                $response['message'] = 'User Not Found';
            } else {
                foreach ($messages as $row) {
                    $message[$i] = $row;
                    $media_files = $this->chat_model->get_media($row['id']);
                    
                    if (isset($media_files) && !empty($media_files)) {
                        for ($j = 0; $j < count($media_files); $j++) {
                            $file_extention = explode('.', $media_files[$j]['original_file_name']);
                            $media_files[$j]['file_extension'] = end($file_extention);
                            $media_files[$j]['file_url'] = base_url('uploads/chat_media/' . $media_files[$j]['original_file_name']);
                        }
                    }
                    $message[$i]['media_files'] = !empty($media_files) ? $media_files : [];
                    $message[$i]['text'] = $row['message'];
                    $i++;
                }
                $new_msg = $message;

                if (!empty($msg_id)) {

                    $to_id = $to_id;
                    $from_id = $from_id;
                    $firebase_project_id = get_settings('firebase_project_id');
                    $service_account_file = get_settings('service_account_file');


                    // single user msg
                    if ($type == 'person') {

                        // this is the user who going to recive FCM msg
                        $user = fetch_details('users', ['active' => 1, 'id' => $to_id]);


                        // this is the user who going to send FCM msg 
                        $senders_info = fetch_details('users', ['active' => 1, 'id' => $from_id]);

                        $data = $notification = array();
                        $notification['title'] = $senders_info[0]['username'];

                        $notification['senders_name'] = $senders_info[0]['username'];

                        $notification['type'] = 'message';
                        $notification['message_type'] = 'person';
                        $notification['from_id'] = $from_id;
                        $notification['to_id'] = $to_id;
                        $notification['msg_id'] = $msg_id;
                        $notification['new_msg'] = json_encode($new_msg);
                        $notification['body'] = $this->input->post('chat-input-textarea');
                        $notification['base_url'] = base_url('chat');
                        $data['data']['data'] = $notification;
                        $data['data']['webpush']['fcm_options']['link'] = base_url('chat');

                        $data['to'] = isset($user[0]['fcm_id']) && !empty($user[0]['fcm_id']) ? $user[0]['fcm_id'] : '';

                        //send notification in app
                        $results = fetch_details('users', null, 'fcm_id,platform_type', 10000, 0, '', '', "id", $to_id);
                        $result = $res = array();
                        for ($i = 0; $i <= count($results); $i++) {
                            if (isset($results[$i]['fcm_id']) && !empty($results[$i]['fcm_id']) && ($results[$i]['fcm_id'] != 'NULL')) {
                                $res = array_merge($result, $results);
                            }
                        }

                        // Step 1: Group by platform
                        $groupedByPlatform = [];
                        foreach ($res as $item) {
                            $platform = $item['platform_type'];
                            $groupedByPlatform[$platform][] = $item['fcm_id'];
                        }

                        // Step 2: Chunk each platform group into arrays of 1000
                        $fcm_ids = [];
                        foreach ($groupedByPlatform as $platform => $fcmIds) {
                            $fcm_ids[$platform] = array_chunk($fcmIds, 1000);
                        }

                        $registrationIDs = $fcm_ids;
                        $fcm_admin_subject = 'New Message from ' . $senders_info[0]['username'];
                        $fcmMsg = array(
                            'title' => $fcm_admin_subject,
                            'body' => $this->input->post('message'),
                            'type' => "chat",
                            'message' => json_encode($new_msg)
                        );
                        $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
                        if (isset($firebase_project_id) && isset($service_account_file) && !empty($firebase_project_id) && !empty($service_account_file)) {
                            $fcmFields = send_notification($fcmMsg, $registrationIDs, $fcmMsg);
                        }
                        $ch = curl_init();
                        $fcm_key = get_settings('fcm_server_key');


                        $fcm_key = !empty($fcm_key) ? $fcm_key : '';

                        curl_setopt($ch, CURLOPT_POST, 1);
                        $headers = array();
                        $headers[] = "Authorization: key = " . $fcm_key;
                        $headers[] = "Content-Type: application/json";
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                        $result['error'] = false;
                        $result['response'] = curl_exec($ch);
                        if (curl_errno($ch))
                            echo 'Error:' . curl_error($ch);

                        curl_close($ch);
                    }

                    $response['error'] = false;
                    $response['message'] = 'Successful';
                    $response['msg_id'] = $msg_id;
                    $response['new_msg'] = $new_msg;
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Not Successful';
                }
            }
        }
        print_r(json_encode($response));
    }

    public function search_user()
    {
        /*
         
          search : test
          limit : 10
          offset : 10
          order : DESC/ASC
          sort : id
        */

        $this->form_validation->set_rules('search', 'Search keyword', 'trim|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');


        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'u.id';
            if (isset($search) and $search != '') {
                $multipleWhere = ['u.`username`' => $search, 'u.`email`' => $search, 'sd.`store_name`' => $search];
            }
            $where = ['u.active' => 1];

            $search_res = $this->db->select('u.id,u.username,u.email,sd.store_name,u.image')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

            if (isset($multipleWhere) && !empty($multipleWhere)) {
                $search_res->group_start();
                $search_res->or_like($multipleWhere);
                $search_res->group_end();
            }
            if (isset($where) && !empty($where)) {

                $search_res->group_start();
                $search_res->where('ug.group_id', 4);
                $search_res->or_where('ug.group_id', 1);
                $search_res->group_end();
            }
            $res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

            if (!empty($res)) {

                foreach ($res as $row) {

                    $tempRow['id'] = $row['id'];
                    $tempRow['username'] = $row['username'];
                    $tempRow['email'] = isset($row['email']) && !empty($row['email']) ? $row['email'] : "";
                    $tempRow['store_name'] = isset($row['store_name']) && !empty($row['store_name']) ? $row['store_name'] : "";
                    $tempRow['image'] = isset($row['image']) && !empty($row['image']) ? base_url(USER_IMG_PATH  . $row['image']) : "";
                    $rows[] = $tempRow;
                }
                $bulkData['rows'] = $rows;
            }

            if (!empty($res)) {
                $this->response['error'] = false;
                $this->response['message'] = "Data fetched successfully !";
                $this->response['data'] = $bulkData['rows'];
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Data not available !";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    public function make_me_online()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';

        $date = strtotime('now');
        $date = $date + 60;
        $data = array(
            'last_online' => $date
        );

        if ($this->chat_model->make_me_online($user_id, $data)) {

            $response['error'] = false;
            $response['message'] = 'Successful';
            $response['data'] = array();
            print_r(json_encode($response));
        } else {
            $response['error'] = true;
            $response['message'] = 'Not Successful';
            $response['data'] = array();
            print_r(json_encode($response));
        }
    }

    public function get_supporters()
    {

        $data = $this->chat_model->get_supporters();
        $items = [];
        $res = [];
        $i = 0;
        foreach ($data as $key => $value) {

            $items['user_permission_id'] = (!empty($value['user_permission_id']) && isset($value['user_permission_id'])) ? $value['user_permission_id'] : "";
            $items['user_role'] = (!empty($value['user_role']) && isset($value['user_role'])) ? $value['user_role'] : "";
            $items['userto_id'] = (!empty($value['userto_id']) && isset($value['userto_id'])) ? $value['userto_id'] : "";
            $items['username'] = (!empty($value['username']) && isset($value['username'])) ? $value['username'] : "";
            $items['last_online'] = (!empty($value['last_online']) && isset($value['last_online'])) ? $value['last_online'] : "";
            $items['id'] = (!empty($value['id']) && isset($value['id'])) ? $value['id'] : "";
            $items['from_id'] = (!empty($value['from_id']) && isset($value['from_id'])) ? $value['from_id'] : "";
            $items['to_id'] = (!empty($value['to_id']) && isset($value['to_id'])) ? $value['to_id'] : "";
            $items['is_read'] = (!empty($value['is_read']) && isset($value['is_read'])) ? $value['is_read'] : "";
            $items['message'] = (!empty($value['message']) && isset($value['message'])) ? $value['message'] : "";
            $items['type'] = (!empty($value['type']) && isset($value['type'])) ? $value['type'] : "";
            $items['media'] = (!empty($value['media']) && isset($value['media'])) ? $value['media'] : "";
            $items['date_created'] = (!empty($value['date_created']) && isset($value['date_created'])) ? $value['date_created'] : "";

            array_push($res, $items);
        }
        if (!empty($items)) {
            $this->response['error'] = false;
            $this->response['message'] = "Data fetched successfully !";
            $this->response['data'] = $res;
        } else {
            $this->response['error'] = true;
            $this->response['message'] = "Data not available !";
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }
}
