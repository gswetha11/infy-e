<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Seller_privacy_policy extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'seller-privacy-policy';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Seller Privacy Policy | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Seller Privacy Policy | ' . $settings['app_name'];
            $this->data['privacy_policy'] = get_settings('seller_privacy_policy');
            $this->data['terms_n_condition'] = get_settings('seller_terms_conditions');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function update_privacy_policy_settings()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'privacy_policy'), PERMISSION_ERROR_MSG, 'privacy_policy')) {
                return false;
            }

            $this->form_validation->set_rules('terms_n_conditions_input_description', 'Terms and Condition Description', 'trim|required|xss_clean');

            $this->form_validation->set_rules('privacy_policy_input_description', 'Privay Policy Description', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else { 
                $privacy_policy_input_description['privacy_policy_input_description'] = $this->input->post('privacy_policy_input_description', true);
                $terms_n_conditions_input_description['terms_n_conditions_input_description'] = $this->input->post('terms_n_conditions_input_description', true);

                $this->Setting_model->update_seller_privacy_policy($privacy_policy_input_description);
                $this->Setting_model->update_seller_terms_n_condtions($terms_n_conditions_input_description);

                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'System Setting Updated Successfully';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function privacy_policy_page()
    {
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Privacy Policy | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Privacy Policy | ' . $settings['app_name'];
        $this->data['privacy_policy'] = get_settings('seller_privacy_policy');
        $this->load->view('admin/pages/view/privacy-policy', $this->data);
    }

    public function terms_and_conditions_page()
    {
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Terms & Conditions | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Terms & Conditions | ' . $settings['app_name'];
        $this->data['terms_and_conditions'] = get_settings('seller_terms_conditions');
        $this->load->view('admin/pages/view/terms-and-conditions', $this->data);
    }
}
