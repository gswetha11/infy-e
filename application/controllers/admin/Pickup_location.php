<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pickup_location extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'file']);
        $this->load->model('Pickup_location_model');
        if (!has_permissions('read', 'pickup_location')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        } else {
            $this->session->set_flashdata('authorize_flag', "");
        }
    }

    public function manage_pickup_locations()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'pickup_location')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }
            $this->data['main_page'] = TABLES . 'manage-pickup_location';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Pickup location Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Pickup location Management  | ' . $settings['app_name'];
            $this->data['sellers'] = $this->db->select(' u.username as seller_name,u.id as seller_id,sd.category_ids,sd.store_name,sd.id as seller_data_id  ')
                ->join('users_groups ug', ' ug.user_id = u.id ')
                ->join('seller_data sd', ' sd.user_id = u.id ')
                ->where(['ug.group_id' => '4'])
                ->get('users u')->result_array();
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('pickup_locations', ['id' => $_GET['edit_id']]);
            }
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_pickup_location()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (isset($_POST['edit_pickup_location'])) {
                if (print_msg(!has_permissions('update', 'pickup_location'), PERMISSION_ERROR_MSG, 'pickup_location')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'pickup_location'), PERMISSION_ERROR_MSG, 'pickup_location')) {
                    return false;
                }
            }

            $this->form_validation->set_rules('pickup_location', ' Pickup Location ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('name', ' Name ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', ' Email ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('phone', ' Phone ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('city', ' City ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('state', ' State ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('country', ' Country ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('pincode', ' Pincode ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('address', ' Address ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('address2', ' Address 2 ', 'trim|xss_clean');
            $this->form_validation->set_rules('latitude', ' Latitude ', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('longitude', ' Longitude ', 'trim|numeric|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
                $fields = [
                    'edit_pickup_location', 'update_id', 'seller_id', 'pickup_location', 'name', 
                    'email', 'phone', 'city', 'state', 'country', 'pincode', 'address', 
                    'address2', 'latitude', 'longitude'
                ];
                
                foreach ($fields as $field) {
                    $pickup_location[$field] = $this->input->post($field, true) ?? "";
                }

                $this->Pickup_location_model->add_pickup_location($pickup_location);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_pickup_location'])) ? 'Update Pickup Location' : 'Add Pickup Location';
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_pickup_location()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Pickup_location_model->get_list($table = 'pickup_locations');
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_seller_pickup_location()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Pickup_location_model->get_list($table = 'pickup_locations', NULL, $_GET['seller_id']);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
