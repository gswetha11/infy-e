<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Language_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper', 'timezone_helper']);
    }

    public function create($data)
    {
        $data['language'] = strtolower($data['language']);
        $arr = array(
            'language' => $data['language'],
            'code' => $data['code'],
            'native_language' => $data['native_language'],
            'is_rtl' => (isset($data['is_rtl']) && $data['is_rtl'] == 1) ? 1 : 0,
        );
        return $this->db->insert('languages', $arr);
    }

    public function update($data)
    {
        $arr = array(
            'is_rtl' => (isset($data['is_rtl']) && $data['is_rtl'] == 1) ? 1 : 0,
        );
        return $this->db->where('id', $data['language_id'])->update('languages', $arr);
    }

    public function is_default_for_web($data)
    {
        // Initialize the array to update the 'is_default' field
        $arr = array(
            'is_default' => (isset($data['is_default']) && $data['is_default'] == 1) ? 1 : 0,
        );

        // Set all 'is_default' fields to 0
        $this->db->where('is_default', '1')->set(['is_default' => '0'])->update('languages');

        // Update the specific language with the new 'is_default' value
        return $this->db->where('id', $data['language_id'])->update('languages', $arr);
    }

    public function get_language_list()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $multipleWhere = '';

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
            $multipleWhere = ['id' => $search, 'language' => $search, 'code' => $search, 'is_rtl' => $search];
        }

        $count_res = $this->db->select(' COUNT(id) as `total`');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $address_count = $count_res->get('languages')->result_array();

        foreach ($address_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select('*');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $theme = $search_res->order_by($sort, "DESC")->limit($limit, $offset)->get('languages')->result_array();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($theme as $row) {
            $row = output_escaping($row);
            $operate = '<a href="javascript:void(0)" class="btn btn-primary select-language btn-xs" title="Update for label" data-id="' . $row['id'] . '"><i class="fa fa-eye"></i></a>';
            $operate .= '<a href="javascript:void(0)" class="btn btn-danger delete-language btn-xs ml-2" title="Delete Language" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            $tempRow['id'] = $row['id'];
            $tempRow['language'] = $row['language'];
            $tempRow['native_language'] = $row['native_language'];
            $tempRow['code'] = $row['code'];
            if ($row['is_rtl'] == '1') {
                $tempRow['is_rtl'] = '<a class="badge badge-success text-white" >Yes</a>';
            } else {
                $tempRow['is_rtl'] = '<a class="badge badge-danger text-white" >No</a>';
            }
            $tempRow['created_on'] = $row['created_on'];
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    public function delete_language($id)
    {
        // Delete the language from the database
        $this->db->where('id', $id);
        return $this->db->delete('languages'); // Assuming 'languages' is your table name
    }
}


