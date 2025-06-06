<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Offer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Offer_model');

        if (!has_permissions('read', 'new_offer_images')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public  function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'offers';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) ? 'Edit Offer Image | ' . $settings['app_name'] : 'Add Offer Images | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Add Offer Images  | ' . $settings['app_name'];
            $this->data['categories'] = $this->category_model->get_categories();
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('offers', ['id' => $_GET['edit_id']]);
            }
            $this->data['about_us'] = get_settings('about_us');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public  function manage_offer()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-offers';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Offer Images Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Offer Images Management  | ' . $settings['app_name'];
            $this->data['categories'] = $this->category_model->get_categories();
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->response['fetched_data'] = fetch_details('offers', ['id' => $_GET['edit_id']]);
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return;
            }
            $this->data['about_us'] = get_settings('about_us');
            $this->data['csrfName'] = $this->security->get_csrf_token_name();
            $this->data['csrfHash'] = $this->security->get_csrf_hash();
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_offer()
    {
        if (isset($_POST['edit_offer'])) {
            if (print_msg(!has_permissions('update', 'new_offer_images'), PERMISSION_ERROR_MSG, 'new_offer_images')) {
                return false;
            }
        } else {
            if (print_msg(!has_permissions('create', 'new_offer_images'), PERMISSION_ERROR_MSG, 'new_offer_images')) {
                return false;
            }
        }

        $this->form_validation->set_rules('offer_type', 'Offer Type', 'trim|required|xss_clean');
        if (isset($_POST['offer_type']) && $_POST['offer_type'] == 'categories') {
            $this->form_validation->set_rules('category_id', 'Category', 'trim|required|xss_clean');
        }
        if (isset($_POST['offer_type']) && $_POST['offer_type'] == 'offer_url') {
            $this->form_validation->set_rules('link', 'Link', 'trim|required|xss_clean');
        }
        if (isset($_POST['offer_type']) && $_POST['offer_type'] == 'products') {
            $this->form_validation->set_rules('product_id', 'Product', 'trim|required|xss_clean');
        }
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = validation_errors();
            print_r(json_encode($this->response));
        } else {
            $fields = [
                'edit_offer', 'offer_type', 'category_id', 'link', 'product_id', 'image' ];
            
            foreach ($fields as $field) {
                $offer[$field] = $this->input->post($field, true) ?? "";
            }
            $this->Offer_model->add_offer($offer);
            $this->response['error'] = false;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $message = (isset($_POST['edit_offer'])) ? 'Offer Images Update Successfully' : 'Offer Images Added Successfully';
            $this->response['message'] = $message;
            print_r(json_encode($this->response));
        }
    }

    public function view_offers()
    {
        return $this->Offer_model->get_offer_list();
    }

    public function delete_offer()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('delete', 'new_offer_images'), PERMISSION_ERROR_MSG, 'new_offer_images', false)) {
                return false;
            }
            if (delete_details(['id' => $_GET['id']], 'offers') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'Deleted Succesfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something Went Wrong';
            }
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
