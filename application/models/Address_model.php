<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Address_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
        $this->load->model(['cart_model']);
    }

    function set_address($data)
    {

        $data = escape_array($data);

        $address_data = [];

        if (isset($data['user_id']) && !empty($data['user_id'])) {
            $address_data['user_id'] = $data['user_id'];
        }
        if (isset($data['id']) && !empty($data['id'])) {
            $address_data['id'] = $data['id'];
        }
        if (isset($data['type']) && !empty($data['type'])) {
            $address_data['type'] = $data['type'];
        }
        if (isset($data['name']) && !empty($data['name'])) {
            $address_data['name'] = $data['name'];
        }
        if (isset($data['mobile']) && !empty($data['mobile'])) {
            $address_data['mobile'] = $data['mobile'];
        }
        $address_data['country_code'] = (isset($data['country_code']) && !empty($data['country_code']) && is_numeric($data['country_code'])) ? $data['country_code'] : 0;

        if (isset($data['alternate_mobile']) && !empty($data['alternate_mobile'])) {
            $address_data['alternate_mobile'] = $data['alternate_mobile'];
        }

        if (isset($data['address']) && !empty($data['address'])) {
            $address_data['address'] = $data['address'];
        }

        if (isset($data['landmark']) && !empty($data['landmark'])) {
            $address_data['landmark'] = $data['landmark'];
        }
        $city = fetch_details('cities', ['id' => $data['city_id']], 'name');
        $area = fetch_details('areas', ['id' => $data['area_id']], 'name');

        if (isset($data['general_area_name']) && $data['general_area_name']) {
            $address_data['area'] = isset($data['general_area_name']) && !empty($data['general_area_name']) ? $data['general_area_name'] : '';
        }
        if (isset($data['edit_general_area_name']) && !empty($data['edit_general_area_name'])) {
            $address_data['area'] = isset($data['edit_general_area_name']) && !empty($data['edit_general_area_name']) ? $data['edit_general_area_name'] : '';
        }
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $address_data['city_id'] = (isset($data['city_id']) & !empty($data['city_id'])) ? $data['city_id'] : 0;
            $address_data['city'] = isset($city) && !empty($city) ? $city[0]['name'] : '';
        }
        if (isset($data['area_name']) && !empty($data['area_name'])) {
            $address_data['area'] = (isset($data['area_name']) & !empty($data['area_name'])) ? $data['area_name'] : $area[0]['name'];
        }
        if (isset($data['other_areas']) && !empty($data['other_areas'])) {
            $address_data['area'] = (isset($data['other_areas']) && !empty($data['other_areas'])) ? $data['other_areas'] : $area[0]['name'];
        }
        if (isset($data['pincode_name']) || isset($data['pincode'])) {
            $address_data['system_pincode'] = (isset($data['pincode_name']) && !empty($data['pincode_name'])) ? 0 : 1;
            if (isset($data['pincode_full'])) {
                $address_data['pincode'] = (isset($data['pincode_name']) && !empty($data['pincode_name'])) ? $data['pincode_name'] : $data['pincode_full'];
            } else {
                $address_data['pincode'] = $data['pincode'];
            }
        }

        if (isset($data['state']) && !empty($data['state'])) {
            $address_data['state'] = $data['state'];
        }

        if (isset($data['country']) && !empty($data['country'])) {
            $address_data['country'] = $data['country'];
        }
        if (isset($data['latitude']) && !empty($data['latitude'])) {
            $address_data['latitude'] = $data['latitude'];
        }
        if (isset($data['longitude']) && !empty($data['longitude'])) {
            $address_data['longitude'] = $data['longitude'];
        }

        if (isset($data['id']) && !empty($data['id'])) {
            if (isset($data['is_default']) && $data['is_default'] == true) {
                $address = fetch_details('addresses', ['id' => $data['id']], '*');
                $this->db->where('user_id', $address[0]['user_id'])->set(['is_default' => '0'])->update('addresses');
                $this->db->where('id', $data['id'])->set(['is_default' => '1'])->update('addresses');
            }

            $this->db->set($address_data)->where('id', $data['id'])->update('addresses');
        } else {
            $this->db->insert('addresses', escape_array($address_data));
            $last_added_id = $this->db->insert_id();
            if (isset($data['is_default']) && $data['is_default'] == true) {
                $this->db->where('user_id', $data['user_id'])->set('is_default', '0')->update('addresses');
                $this->db->where('id', $last_added_id)->set('is_default', '1')->update('addresses');
            }
        }
    }

    function delete_address($data)
    {
        $this->db->delete('addresses', ['id' => $data['id']]);
    }

    function get_address($user_id, $id = false, $fetch_latest = false, $is_default = false)
    {
        $where = [];
        $shipping_settings = get_settings('shipping_method', true);
        $system_settings = get_settings('system_settings', true);
        $default_delivery_charge  = $shipping_settings['default_delivery_charge'];

        if (isset($user_id) || $id != false) {
            if (isset($user_id) && $user_id != null && !empty($user_id)) {
                $where['user_id'] = $user_id;
            }
            if ($id != false) {
                $where['addr.id'] = $id;
            }
            $this->db->select('addr.*')
                ->where($where)
                ->group_by('addr.id')->order_by('addr.id', 'DESC');
            if ($fetch_latest == true) {
                $this->db->limit('1');
            }
            if (!empty($is_default)) {
                $this->db->where('is_default', 1);
            }
            $res = $this->db->get('addresses addr')->result_array();

            if (!empty($res)) {
                for ($i = 0; $i < count($res); $i++) {

                    if (isset($shipping_settings['pincode_wise_deliverability']) && $shipping_settings['pincode_wise_deliverability'] == 1) {

                        $pincode = (isset($res[$i]['pincode']) && ($res[$i]['pincode']) != 0) ? $res[$i]['pincode'] : "";
                        
                        $minimum_free_delivery_order_amount =  fetch_details('zipcodes', ['zipcode' => $pincode], 'minimum_free_delivery_order_amount,delivery_charges');
                        $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                        $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];
                        $res[$i] = output_escaping($res[$i]);
                        $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ? "$amount" : "0";
                        $res[$i]['delivery_charges'] = (isset($delivery_charges) && $delivery_charges != NULL) ? "$delivery_charges" : "0";
                    }
                    if (isset($shipping_settings['city_wise_deliverability']) && $shipping_settings['city_wise_deliverability'] == 1) {
                        $city = (isset($res[$i]['city']) && ($res[$i]['city']) != '') ? $res[$i]['city'] : "";
                        $minimum_free_delivery_order_amount =  fetch_details('cities', ['name' => $city], '*');
                        
                        $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                        $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];

                        $res[$i] = output_escaping($res[$i]);
                        $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ? (string)$amount : "0";
                        $res[$i]['delivery_charges'] = (isset($delivery_charges) && $delivery_charges != NULL) ?  (string)$delivery_charges : "0";
                    }
                }
            }
            return $res;
        }
    }
    function get_address_for_api($user_id, $id = false, $fetch_latest = false, $is_default = false)
    {
        $where = [];
        $shipping_settings = get_settings('shipping_method', true);
        $system_settings = get_settings('system_settings', true);
        $default_delivery_charge  = $shipping_settings['default_delivery_charge'];

        if (isset($user_id) || $id != false) {
            if (isset($user_id) && $user_id != null && !empty($user_id)) {
                $where['user_id'] = $user_id;
            }
            if ($id != false) {
                $where['addr.id'] = $id;
            }
            $this->db->select('addr.*')
                ->where($where)
                ->group_by('addr.id')->order_by('addr.id', 'DESC');
            if ($fetch_latest == true) {
                $this->db->limit('1');
            }
            if (!empty($is_default)) {
                $this->db->where('is_default', 1);
            }
            $res = $this->db->get('addresses addr')->result_array();

            if (!empty($res)) {

                for ($i = 0; $i < count($res); $i++) {

                    if (isset($system_settings['update_seller_flow']) && $system_settings['update_seller_flow'] == '1') {
                        $cart_user_data = $this->cart_model->get_user_cart($user_id);
                        $seller_ids = [];
                        if (!empty($cart_user_data)) {
                            foreach ($cart_user_data as $product) {
                                $seller_ids[] = $product['seller_id']; // Collect seller IDs
                            }
                            $seller_ids = array_unique($seller_ids);

                            $this->db->select('user_id,serviceable_zipcodes,serviceable_cities');
                            $this->db->where_in("user_id", $seller_ids);

                            $fetched_records = $this->db->get('seller_data');
                            $sellers_data = $fetched_records->result_array();
                        }
                        if (isset($shipping_settings['pincode_wise_deliverability']) && $shipping_settings['pincode_wise_deliverability'] == 1) {
                            $pincode_serviceable_count = 0;
                            $pincode = (isset($res[$i]['pincode']) && ($res[$i]['pincode']) != 0) ? $res[$i]['pincode'] : "";
                            $minimum_free_delivery_order_amount =  fetch_details('zipcodes', ['zipcode' => $pincode], '*');

                            $address_zipcode_id = $minimum_free_delivery_order_amount[0]['id'];
                            $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                            $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];

                            foreach ($sellers_data as $seller_data) {
                                $serviceable_zipcodes = explode(',', $seller_data['serviceable_zipcodes']);
                                if (in_array($address_zipcode_id, $serviceable_zipcodes)) {

                                    $pincode_serviceable_count++;
                                    $total_delivery_charges = $pincode_serviceable_count * $delivery_charges;
                                } else {
                                    $total_delivery_charges = $delivery_charges + $default_delivery_charge;
                                }
                            }

                            $res[$i] = output_escaping($res[$i]);
                            $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ? (string)$amount : "0";
                            $res[$i]['delivery_charges'] = (string)$total_delivery_charges;
                        }
                        if (isset($shipping_settings['city_wise_deliverability']) && $shipping_settings['city_wise_deliverability'] == 1) {
                            $city = (isset($res[$i]['city']) && ($res[$i]['city']) != '') ? $res[$i]['city'] : "";
                            $minimum_free_delivery_order_amount =  fetch_details('cities', ['name' => $city], '*');
                            
                            $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                            $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];
                            $address_city_id = $minimum_free_delivery_order_amount[0]['id'];

                            foreach ($sellers_data as $seller_data) {
                                $serviceable_zipcodes = explode(',', $seller_data['serviceable_zipcodes']);
                                if (in_array($address_city_id, $serviceable_zipcodes)) {
                                    $pincode_serviceable_count++;
                                    $total_delivery_charges = $pincode_serviceable_count * $delivery_charges;
                                } else {
                                    $total_delivery_charges = $delivery_charges + $default_delivery_charge;
                                }
                            }

                            $res[$i] = output_escaping($res[$i]);
                            $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ?  (string)$amount : "0";
                            $res[$i]['delivery_charges'] = (string)$total_delivery_charges;
                        }
                    } else {
                        if (isset($shipping_settings['pincode_wise_deliverability']) && $shipping_settings['pincode_wise_deliverability'] == 1) {

                            $pincode = (isset($res[$i]['pincode']) && ($res[$i]['pincode']) != 0) ? $res[$i]['pincode'] : "";
                            
                            $minimum_free_delivery_order_amount =  fetch_details('zipcodes', ['zipcode' => $pincode], 'minimum_free_delivery_order_amount,delivery_charges');
                            $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                            $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];
                            $res[$i] = output_escaping($res[$i]);
                            $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ? (string)$amount : "0";
                            $res[$i]['delivery_charges'] = (isset($delivery_charges) && $delivery_charges != NULL) ? (string)$delivery_charges : "0";
                        }
                        if (isset($shipping_settings['city_wise_deliverability']) && $shipping_settings['city_wise_deliverability'] == 1) {
                            $city = (isset($res[$i]['city']) && ($res[$i]['city']) != '') ? $res[$i]['city'] : "";
                            $minimum_free_delivery_order_amount =  fetch_details('cities', ['name' => $city], '*');
                            
                            $amount = $minimum_free_delivery_order_amount[0]['minimum_free_delivery_order_amount'];
                            $delivery_charges = $minimum_free_delivery_order_amount[0]['delivery_charges'];

                            $res[$i] = output_escaping($res[$i]);
                            $res[$i]['minimum_free_delivery_order_amount'] = (isset($amount) && $amount != NULL) ? (string)$amount : "0";
                            $res[$i]['delivery_charges'] = (isset($delivery_charges) && $delivery_charges != NULL) ?  (string)$delivery_charges : "0";
                        }
                    }
                }
            }
            return $res;
        }
    }

    public function get_address_list($user_id = '')
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';
        $multipleWhere = '';

        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            $where['user_id'] = $_GET['user_id'];
        }

        if (!empty($user_id)) {
            $where['user_id'] = $user_id;
        }

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['addr.name' => $search, 'addr.address' => $search, 'mobile' => $search, 'area' => $search, 'city' => $search, 'state' => $search, 'country' => $search, 'pincode' => $search];
        }

        $count_res = $this->db->select(' COUNT(addr.id) as `total` ,addr.*');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $address_count = $count_res->get('addresses addr')->result_array();

        foreach ($address_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select('addr.*');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $search_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $address_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('addresses addr')->result_array();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($address_search_res as $row) {

            $row = output_escaping($row);
            $default = $row['is_default'] == 1 ? 'Default' : 'Set as default';
            $btn = $row['is_default'] == 1 ? 'info' : 'secondary';
            $class = $row['is_default'] == 1 ? '' : 'default-address ';
            $operate = '<a href="javascript:void(0)" class="edit-address btn btn-success btn-xs mr-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#address-modal"><i class="fa fa-pen uil uil-pen"></i></a>';
            $operate .= '<a href="javascript:void(0)" class="delete-address btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            $operate .= '<a href="javascript:void(0)" class="' . $class . ' btn btn-' . $btn . ' btn-xs mr-1 mb-1" title="' . $default . '" data-id="' . $row['id'] . '"><i class="fa fa-check-square"></i></a>';
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['type'] = $row['type'];
            $tempRow['mobile'] = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['mobile']) - 3) . substr($row['mobile'], -3) : $row['mobile'];
            $tempRow['alternate_mobile'] = $row['alternate_mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['landmark'] = $row['landmark'];
            $tempRow['area'] = $row['area'];
            $tempRow['area_id'] = $row['area_id'];
            $tempRow['city'] = $row['city'];
            $tempRow['city_id'] = $row['city_id'];
            $tempRow['state'] = $row['state'];
            $tempRow['pincode'] = $row['pincode'];
            $tempRow['system_pincode'] = $row['system_pincode'];
            $tempRow['pincode_name'] = $row['pincode'];
            $tempRow['country'] = $row['country'];
            $tempRow['action'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
}
