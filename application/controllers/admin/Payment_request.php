<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_request extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model(['payment_request_model', 'transaction_model']);

        if (!has_permissions('read', 'return_request')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'payment-request';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Payment Request | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Return Request  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_payment_request()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $payment_req_id = $this->input->post('payment_request_id',true);
            $status = fetch_details('payment_requests', ['id' => $payment_req_id]);
            if (print_msg(!has_permissions('update', 'return_request'), PERMISSION_ERROR_MSG, 'return_request')) {
                return false;
            }
            $this->form_validation->set_rules('payment_request_id', 'id', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('status', 'Status', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('update_remarks', 'Remarks ', 'trim|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
                if (false) {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'You have already rejected the amount';
                    print_r(json_encode($this->response));
                } else {
                    $fields = ['payment_request_id', 'status', 'update_remarks', 'id'];





                    foreach ($fields as $field) {
                        $payment_request[$field] = $this->input->post($field, true) ?? "";
                    }


                    $this->payment_request_model->update_payment_request($payment_request);

                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Payment request updated successfully';
                    print_r(json_encode($this->response));
                }
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function view_payment_request_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->payment_request_model->get_payment_request_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
