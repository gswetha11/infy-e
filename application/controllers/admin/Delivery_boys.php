<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_boys extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model('Delivery_boy_model');
        if (!has_permissions('read', 'delivery_boy')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'delivery-boy';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Add Delivery Boy | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Add Delivery Boy  | ' . $settings['app_name'];
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->data['fetched_data'] = $this->db->select(' u.* ')
                    ->join('users_groups ug', ' ug.user_id = u.id ')
                    ->where(['ug.group_id' => '3', 'ug.user_id' => $_GET['edit_id']])
                    ->get('users u')
                    ->result_array();
            }
            $this->data['shipping_method'] = get_settings('shipping_method', true);
            $this->data['system_settings'] = get_settings('system_settings', true);
            $this->data['cities'] = fetch_details('cities', "", 'name,id');

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_delivery_boy()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-delivery-boy';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Delivery Boy Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Delivery Boy Management  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_delivery_boys()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (isset($_GET['delivery_boy_status']) && !empty($_GET['delivery_boy_status'])) {
                return $this->Delivery_boy_model->get_delivery_boys_list($_GET['delivery_boy_status']);
            }
            return $this->Delivery_boy_model->get_delivery_boys_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_delivery_boys()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('delete', 'delivery_boy'), PERMISSION_ERROR_MSG, 'delivery_boy', false)) {
                return true;
            }
            if (defined('SEMI_DEMO_MODE') && SEMI_DEMO_MODE == 0) {
                $this->response['error'] = true;
                $this->response['message'] = SEMI_DEMO_MODE_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
            if (!isset($_GET['id']) && empty($_GET['id'])) {
                $this->response['error'] = true;
                $this->response['message'] = 'Seller id is required';
                print_r(json_encode($this->response));
                return;
                exit();
            }
            $dboy_id = $this->input->get('id', true);
            $order_items = fetch_details('order_items', ['delivery_boy_id' => $dboy_id]);

            //check all the assign order item status which is not delivered yet 
            foreach ($order_items as $order_item) {
                $order_item_status  = $order_item['active_status'];
                if ($order_item_status != 'delivered' || $order_item_status != 'returned' || $order_item_status != 'cancelled') {
                    $this->response['error'] = true;
                    $this->response['message'] = 'You cannot delete delivery boy , orders is not delivered, please once assign to other delivery boy or deliver all the orders ';
                    print_r(json_encode($this->response));
                    return;
                    exit();
                }
            }
            if (delete_details(['user_id' => $_GET['id']], 'users_groups')) {
                $delete['users'] = 1;
                // if (update_details(['group_id' => '2'], ['user_id' => $_GET['id'], 'group_id' => 3], 'users_groups') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'User removed from delivery boy succesfully';
                print_r(json_encode($this->response));
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something Went Wrong';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function add_delivery_boy()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (isset($_POST['edit_delivery_boy'])) {
                if (print_msg(!has_permissions('update', 'delivery_boy'), PERMISSION_ERROR_MSG, 'delivery_boy')) {
                    return true;
                }
            } else {
                if (print_msg(!has_permissions('create', 'delivery_boy'), PERMISSION_ERROR_MSG, 'delivery_boy')) {
                    return true;
                }
            }

            $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]|max_length[16]');
            $this->form_validation->set_rules('status', 'Status', 'trim|required|xss_clean');
            if (!isset($_POST['edit_delivery_boy'])) {
                $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
                $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required|matches[password]|xss_clean');
            }
            $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
            $this->form_validation->set_rules('bonus_type', 'Bonus Type', 'trim|required|xss_clean');

            if (isset($_POST['pincode_wise_deliverability']) && !empty($_POST['pincode_wise_deliverability']) && ($_POST['pincode_wise_deliverability'] == 1)) {
                $this->form_validation->set_rules('serviceable_zipcodes[]', 'Serviceable Zipcodes', 'trim|required|xss_clean');
            }
            if (isset($_POST['city_wise_deliverability']) && !empty($_POST['city_wise_deliverability']) && ($_POST['city_wise_deliverability'] == 1)) {
                $this->form_validation->set_rules('serviceable_cities[]', 'Serviceable Cities', 'trim|required|xss_clean');
            }

            $bonus = 0;
            if (isset($_POST['bonus_type']) && $_POST['bonus_type'] == 'fixed_amount_per_order_item') {
                $this->form_validation->set_rules('bonus_amount', 'Bonus Amount', 'trim|required|numeric|xss_clean');
                $bonus = $this->input->post('bonus_amount');
            } elseif (isset($_POST['bonus_type']) && $_POST['bonus_type'] == 'percentage_per_order_item') {
                $this->form_validation->set_rules('bonus_percentage', 'Bonus Percentage', 'trim|required|numeric|xss_clean');
                $bonus = $this->input->post('bonus_percentage');
            }
            if (!isset($_POST['edit_delivery_boy'])) {
                if (isset($_FILES) && !empty($_FILES) && count($_FILES['driving_license']['name']) < 2) {
                    $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
                }
                if (isset($_FILES) && !empty($_FILES) && count($_FILES['driving_license']['name']) > 2) {
                    $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
                }
            }

            if (isset($_POST['edit_delivery_boy'])) {
                $delivery_boy_data = fetch_details('users', ['id' => $_POST['edit_delivery_boy']], 'driving_license');
                $driving_license = explode(',', $delivery_boy_data[0]['driving_license']);
            }

            if (isset($_POST['edit_delivery_boy'])) {
                if (isset($_FILES) && !empty($_FILES) && !empty($_FILES['driving_license']['name'][0]) && count($_FILES['driving_license']['name']) < 2) {

                    $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
                } elseif (isset($driving_license) && !empty($driving_license[0]) && $driving_license[0] != 'NULL') {

                    if (count($driving_license) < 2) {

                        $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'Please add front and back image of Driving license'));
                    }
                }
                if (isset($_FILES) && !empty($_FILES) && !empty($_FILES['driving_license']['name'][0]) && count($_FILES['driving_license']['name']) > 2) {
                    $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
                } elseif (isset($driving_license) && !empty($driving_license[0]) && count($driving_license) > 2) {
                    $this->form_validation->set_rules('driving_license', 'driving_license', 'trim|required|xss_clean', array('required' => 'You can only choose two images'));
                }
            }

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
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


                if (isset($files['driving_license']) && !empty($files['driving_license']['name'][0]) && isset($files['driving_license']['name'][0])) {
                    $other_image_cnt = count((array)$files['driving_license']['name']);
                    $other_img = $this->upload;
                    $other_img->initialize($config);


                    if (isset($_POST['edit_delivery_boy']) && !empty($_POST['edit_delivery_boy']) && isset($delivery_boy_data[0]['driving_license']) && !empty($delivery_boy_data[0]['driving_license'])) {
                        $old_logo = explode('/', $delivery_boy_data[0]['driving_license']);

                        delete_images(DELIVERY_BOY_DOCUMENTS_PATH, $old_logo[4]);
                    }
                    for ($i = 0; $i < $other_image_cnt; $i++) {

                        if (!empty($_FILES['driving_license']['name'][$i])) {

                            $_FILES['temp_image']['name'] = $files['driving_license']['name'][$i];
                            $_FILES['temp_image']['type'] = $files['driving_license']['type'][$i];
                            $_FILES['temp_image']['tmp_name'] = $files['driving_license']['tmp_name'][$i];
                            $_FILES['temp_image']['error'] = $files['driving_license']['error'][$i];
                            $_FILES['temp_image']['size'] = $files['driving_license']['size'][$i];
                            if (!$other_img->do_upload('temp_image')) {
                                $images_info_error = 'driving_license :' . $images_info_error . ' ' . $other_img->display_errors();
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
                                $images_info_error = $other_img->display_errors();
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
                if (isset($_POST['edit_delivery_boy'])) {

                    if (!edit_unique($this->input->post('email', true), 'users.email.' . $this->input->post('edit_delivery_boy', true) . '') || !edit_unique($this->input->post('mobile', true), 'users.mobile.' . $this->input->post('edit_delivery_boy', true) . '')) {
                        $response["error"]   = true;
                        $response["message"] = "Email or mobile already exists !";
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }

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

                    $_POST['status'] = $this->input->post('status', true);
                    $_POST['serviceable_zipcodes'] = $serviceable_zipcodes;
                    $_POST['serviceable_cities'] = $serviceable_cities;
                    $_POST['driving_license'] = isset($images_new_name_arr) && !empty($images_new_name_arr) ? implode(',', (array)$images_new_name_arr) : implode(',', (array)$delivery_boy_data[0]['driving_license']);

                    $email_settings = get_settings('email_settings', true);

                    $this->Delivery_boy_model->update_delivery_boy($_POST);
                    if (!empty($_POST['edit_delivery_boy'])) {
                        if (isset($email_settings) && !empty($email_settings)) {
                            $delivery_boy_id = fetch_details('users', ['id' => $_POST['edit_delivery_boy']]);
                            $title = "Congratulations! Your Delivery Boy Account Has Been Approved";
                            $mail_admin_msg = 'We are delighted to inform you that your application to become an approved delivery boy account on our platform has been successful! Congratulations on this significant milestone.';
                            $email_message = array(
                                'username' => 'Hello, Dear <b>' . ucfirst($delivery_boy_id[0]['username']) . '</b>, ',
                                'subject' => $title,
                                'email' => $delivery_boy_id[0]['email'],
                                'message' => $mail_admin_msg
                            );
                            send_mail($delivery_boy_id[0]['email'],  $title, $this->load->view('admin/pages/view/contact-email-template', $email_message, TRUE));
                        }
                    }
                } else {

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
                        'bonus_type' => $this->input->post('bonus_type'),
                        'bonus' => $bonus,
                        'serviceable_zipcodes' => $serviceable_zipcodes,
                        'serviceable_cities' => $serviceable_cities,
                        'type' => 'phone',
                        'driving_license' => implode(',', $images_new_name_arr),
                        'status' => $this->input->post('status', true),
                    ];

                    $this->ion_auth->register($identity, $password, $email, $additional_data, ['3']);
                    update_details(['active' => 1], [$identity_column => $identity], 'users');
                }

                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_delivery_boy'])) ? 'Delivery Boy Update Successfully' : 'Delivery Boy Added Successfully';
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function manage_cash()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'cash-collection';
            $settings = get_settings('system_settings', true);
            $this->data['curreny'] = $settings['currency'];
            $this->data['delivery_boys'] = $this->db->where(['ug.group_id' => '3', 'u.active' => 1])->join('users_groups ug', 'ug.user_id = u.id')->get('users u')->result_array();
            $this->data['title'] = 'View Cash Collection | ' . $settings['app_name'];
            $this->data['meta_description'] = ' View Cash Collection  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_cash_collection()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Delivery_boy_model->get_cash_collection_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_cash_collection()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('create', 'fund_transfer'), PERMISSION_ERROR_MSG, 'fund_transfer')) {
                return false;
            }

            $this->form_validation->set_rules('delivery_boy_id', 'Delivery Boy', 'trim|required|xss_clean|numeric');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric|greater_than[0]');
            $this->form_validation->set_rules('date', 'Date', 'trim|required|xss_clean');
            $this->form_validation->set_rules('message', 'Message', 'trim|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                echo json_encode($this->response);
                return false;
            } else {
                $delivery_boy_id = $this->input->post('delivery_boy_id', true);
                if (!is_exist(['id' => $delivery_boy_id], 'users')) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Delivery Boy is not exist in your database';
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    print_r(json_encode($this->response));
                    return false;
                }
                $res = fetch_details('users', ['id' => $delivery_boy_id], 'cash_received');
                $amount = $this->input->post('amount', true);
                $order_id = $this->input->post('order_id', true);
                $transaction_id = $this->input->post('transaction_id', true);
                $date = $this->input->post('date', true);
                $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $this->input->post('message', true) : "Delivery boy cash collection by admin";

                if ($res[0]['cash_received'] < $amount) {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Amount must be not be greater than cash';
                    echo json_encode($this->response);
                    return false;
                }

                if ($res[0]['cash_received'] > 0 && $res[0]['cash_received'] != null) {
                    update_cash_received($amount, $delivery_boy_id, "deduct");
                    $this->load->model("transaction_model");
                    $transaction_data = [
                        'transaction_type' => "transaction",
                        'user_id' => $delivery_boy_id,
                        'order_id' => $order_id,
                        'type' => "delivery_boy_cash_collection",
                        'txn_id' => "",
                        'amount' => $amount,
                        'status' => "1",
                        'message' => $message,
                        'transaction_date' => $date,
                    ];
                    update_details($transaction_data, ['id' => $transaction_id], 'transactions');
                    // $this->transaction_model->add_transaction($transaction_data);
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Amount Successfully Collected';
                } else {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Cash should be greater than 0';
                }

                echo json_encode($this->response);
                return false;
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
