<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Error_404 extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['address_model', 'category_model', 'cart_model', 'faq_model']);
        $this->data['is_logged_in'] = ($this->ion_auth->logged_in()) ? 1 : 0;
        $this->data['user'] = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();
        $this->data['settings'] = get_settings('system_settings', true);
        $this->data['web_settings'] = get_settings('web_settings', true);
        $this->data['auth_settings'] = get_settings('authentication_settings', true);
        $this->data['web_logo'] = get_settings('web_logo');
        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
    }

    public function index()
    {

        $web_doctor_brown = get_settings('web_doctor_brown', true);

        $this->data['main_page'] = '404_page';
        $this->data['title'] = 'Not Found | ' . $this->data['web_settings']['site_title'];
        $this->data['keywords'] = 'Not Found, ' . $this->data['web_settings']['meta_keywords'];
        $this->data['description'] = 'Not Found | ' . $this->data['web_settings']['meta_description'];

        if (isset($web_doctor_brown) && !empty($web_doctor_brown)) {

            $this->load->view('front-end/' . THEME . '/template', $this->data);
        } else {

            $this->load->view('front-end/' . THEME . '/landing_page.php');
        }
    }
}
