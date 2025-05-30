<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Customer_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    public function get_customer_list()
    {

        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'ASC';
        $multipleWhere = '';
        $where = ['ug.group_id' => 2];

        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        }
        if (isset($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = [
                '`u.id`' => $search, '`u.username`' => $search, '`u.email`' => $search, '`u.mobile`' => $search, '`c.name`' => $search, '`a.name`' => $search, '`u.street`' => $search
            ];
        }

        if (isset($_GET['order_status']) && ($_GET['order_status'] != '')) {
            $where['u.active'] = $_GET['order_status'];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ,a.name as area_name,c.name as city_name')->join('cities c', 'u.city=c.id', 'left')->join('areas a', 'u.area=a.id', 'left');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }
        $count_res->join('`users_groups` `ug`', '`u`.`id` = `ug`.`user_id`');

        $cat_count = $count_res->get('users u')->result_array();
       
        foreach ($cat_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,a.name as area_name,c.name as city_name')->join('cities c', 'u.city=c.id', 'left')->join('areas a', 'u.area=a.id', 'left');;
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $search_res->join('`users_groups` `ug`', '`u`.`id` = `ug`.`user_id`');

        $cat_search_res = $search_res->order_by($sort, "desc")->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($cat_search_res as $row) {
            $row = output_escaping($row);
            if (!$this->ion_auth->is_seller()) {
                $operate = '<a href="' . base_url('admin/orders?user_id=' . $row['id']) . '" class="btn btn-primary action-btn btn-xs mr-1 mb-1 ml-1" title="View Orders" ><i class="fa fa-eye"></i></a>';
                $operate .= '<a  href="' . base_url('admin/transaction/view-transaction?user_id=' . $row['id']) . '" class="btn btn-danger action-btn btn-xs mb-1 ml-1" title="View Transactions"  ><i class="fa fa-money-bill-wave"></i></a>';
                $operate .= ' <a href="javascript:void(0)" class="view_address  btn btn-warning btn-xs action-btn mr-1 mb-1 ml-1" title="View Address" data-id="' . $row['id'] . '"  data-toggle="modal" data-target="#customer-address-modal" ><i class="far fa-address-book"></i></a>';
                if ($row['active'] == '1') {
                    $operate .= '<a class="btn btn-success btn-xs action-btn update_active_status mr-1 mb-1 ml-1" data-table="users" title="Deactivate" href="javascript:void(0)" data-id="' . $row['id'] . '" data-status="' . $row['active'] . '" ><i class="fa fa-toggle-on"></i></a>';
                } else {
                    $operate .= '<a class="btn btn-secondary mr-1 mb-1 ml-1 btn-xs update_active_status action-btn" data-table="users" href="javascript:void(0)" title="Active" data-id="' . $row['id'] . '" data-status="' . $row['active'] . '" ><i class="fa fa-toggle-off"></i></a>';
                }
            }
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            if (isset($row['email']) && !empty($row['email']) && $row['email'] != "" && $row['email'] != " ") {
                $tempRow['email'] = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['email']) - 3) . substr($row['email'], -3) : ucfirst($row['email']);
            } else {
                $tempRow['email'] = "";
            }
            if (isset($row['mobile']) && !empty($row['mobile']) && $row['mobile'] != "" && $row['mobile'] != " ") {
                $tempRow['mobile'] =  (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['mobile']) - 3) . substr($row['mobile'], -3) : $row['mobile'];
            }else{
                $tempRow['mobile'] = "";
            }
            $tempRow['balance'] = $row['balance'];
            $tempRow['city'] = $row['city_name'];
            $tempRow['area'] = $row['area_name'];
            $tempRow['street'] = $row['street'];
            $tempRow['status'] = ($row['active'] == '1') ? '<a class="badge badge-success text-white" >Active</a>' : '<a class="badge badge-danger text-white" >Inactive</a>';
            $tempRow['date'] = date('d-m-Y', strtotime($row['created_at']));
            if (!$this->ion_auth->is_seller()) {
                $tempRow['actions'] = $operate;
            }

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function update_balance($amount, $delivery_boy_id, $action)
    {
        /**
         * @param
         * action = deduct / add
         */

        if ($action == "add") {
            $this->db->set('balance', 'balance+' . $amount, FALSE);
        } elseif ($action == "deduct") {
            $this->db->set('balance', 'balance-' . $amount, FALSE);
        }
        return $this->db->where('id', $delivery_boy_id)->update('users');
    }

    public function get_customers($id, $search, $offset, $limit, $sort, $order)
    {
        $multipleWhere = '';
        $where['ug.group_id'] =  2;
        if (!empty($search)) {
            $multipleWhere = [
                '`u.id`' => $search, '`u.username`' => $search, '`u.email`' => $search, '`u.mobile`' => $search, '`c.name`' => $search, '`a.name`' => $search, '`u.street`' => $search
            ];
        }
        if (!empty($id)) {
            $where['u.id'] = $id;
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ,a.name as area_name,c.name as city_name')->join('cities c', 'u.city=c.id', 'left')->join('areas a', 'u.area=a.id', 'left');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }
        $count_res->join('`users_groups` `ug`', '`u`.`id` = `ug`.`user_id`');

        $cat_count = $count_res->get('users u')->result_array();

        foreach ($cat_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,a.name as area_name,c.name as city_name')->join('cities c', 'u.city=c.id', 'left')->join('areas a', 'u.area=a.id', 'left');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $search_res->join('`users_groups` `ug`', '`u`.`id` = `ug`.`user_id`');

        $cat_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();
        $rows = array();
        $tempRow = array();
        $bulkData = array();
        $bulkData['error'] = (empty($cat_search_res)) ? true : false;
        $bulkData['message'] = (empty($cat_search_res)) ? 'Customer(s) does not exist' : 'Customers retrieved successfully';
        $bulkData['total'] = (empty($cat_search_res)) ? 0 : $total;
        if (!empty($cat_search_res)) {
            foreach ($cat_search_res as $row) {
                $row = output_escaping($row);
                $tempRow['id'] = $row['id'];
                $tempRow['name'] = $row['username'];
                $tempRow['mobile'] = $row['mobile'];
                $tempRow['email'] = $row['email'];
                $tempRow['balance'] = $row['balance'];
                $tempRow['city'] = $row['city_name'];
                $tempRow['image'] = isset($row['image']) && $row['image'] != '' ? base_url(USER_IMG_PATH . '/' . $row['image']) : '';
                if (empty($row['image']) || file_exists(FCPATH . USER_IMG_PATH . $row['image']) == FALSE) {
                    $tempRow['image'] = base_url() . NO_IMAGE;
                } else {
                    $tempRow['image'] = base_url() . USER_IMG_PATH . $row['image'];
                }
                $tempRow['area'] = $row['area_name'];
                $tempRow['street'] = $row['street'];
                $tempRow['status'] = $row['active'];
                $tempRow['date'] = date('d-m-Y', strtotime($row['created_at']));

                $rows[] = $tempRow;
            }
            $bulkData['data'] = $rows;
        } else {
            $bulkData['data'] = [];
        }
        print_r(json_encode($bulkData));
    }

    // withdrawal_request
    function update_balance_customer($amount, $user_id, $action)
    {
        /**
         * @param
         * action = deduct / add
         */

        if ($action == "add") {
            $this->db->set('balance', 'balance+' . $amount, FALSE);
        } elseif ($action == "deduct") {
            $this->db->set('balance', 'balance-' . $amount, FALSE);
        }
        return $this->db->where('id', $user_id)->update('users');
    }
}
