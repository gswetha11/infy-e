<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Customer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['Customer_model', 'address_model']);

        if (!has_permissions('read', 'customers')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-customer';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'View Customer | ' . $settings['app_name'];
            $this->data['meta_description'] = ' View Customer  | ' . $settings['app_name'];
            $this->data['about_us'] = get_settings('about_us');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_customer()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Customer_model->get_customer_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_customer_wallet()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            $this->data['main_page'] = TABLES . 'manage-customer-wallet';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'View Customer | ' . $settings['app_name'];
            $this->data['meta_description'] = ' View Customer  | ' . $settings['app_name'];
            $this->data['about_us'] = get_settings('about_us');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_customer_wallet()
    {

        if (print_msg(!has_permissions('update', 'customers'), PERMISSION_ERROR_MSG, 'customers', false)) {
            return false;
        }

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->form_validation->set_rules('user_id', 'User ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric');
            $this->form_validation->set_rules('message', 'Message', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
                $user_id = $this->input->post('user_id', true);
                $type = $this->input->post('type', true);
                $amount = $this->input->post('amount', true);

                if ($type == 'debit' || $type == 'credit') {
                    $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $this->input->post('message', true) : "Balance " . $type . "ed.";
                    $status = 'success';
                    $response = update_wallet_balance($type, $user_id, $amount, $message, '', '', 'wallet', $status);
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    print_r(json_encode($response));
                }
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function search_user()
    {
        // Fetch users
        $this->db->select('*');
        $this->db->where("username like '%" . $_GET['search'] . "%'");
        $this->db->where("active=1");
        $fetched_records = $this->db->get('users');
        $users = $fetched_records->result_array();

        // Initialize Array with fetched data
        $data = array();
        foreach ($users as $user) {
            $data[] = array("id" => $user['id'], "text" => $user['username']);
        }
        echo json_encode($data);
    }
    public function addresses()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-address';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'View Address | ' . $settings['app_name'];
            if (isset($_GET['view_id'])) {
                $this->data['view_id'] = (isset($_GET['view_id'])) ? $_GET['view_id'] : null;
            }
            $this->data['meta_description'] = ' View Address  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_address()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            $this->address_model->get_address_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
