<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contact_us extends CI_Controller
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
            $this->data['main_page'] = FORMS . 'contact-us';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Contact Us | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Contact Us | ' . $settings['app_name'];
            $this->data['contact_info'] = get_settings('contact_us');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function update_contact_settings()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'contact_us'), PERMISSION_ERROR_MSG, 'contact_us')) {
                return false;
            }

            $this->form_validation->set_rules('contact_input_description', 'Contact Description', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
                $contact_input_description['contact_input_description'] = $this->input->post('contact_input_description', true);

                $this->Setting_model->update_contact_details($contact_input_description);
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

    public function contact_us_page()
    {
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Contact Us | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Contact Us | ' . $settings['app_name'];
        $this->data['contact_us'] = get_settings('contact_us');
        $this->load->view('admin/pages/view/contact-us', $this->data);
    }
}
