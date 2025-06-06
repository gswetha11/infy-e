<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
        $this->load->model(['Home_model', 'Order_model']);
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $user_id = $this->session->userdata('user_id');
            $user_res = $this->db->select('balance,username')->where('id', $user_id)->get('users')->result_array();
            $this->data['main_page'] = FORMS . 'home';
            $settings = get_settings('system_settings', true);
            $this->data['curreny'] = get_settings('currency');
            $this->data['title'] = 'Seller Panel | ' . $settings['app_name'];
            $this->data['order_counter'] = orders_count("", $user_id);
            $this->data['user_counter'] = (get_seller_permission($this->ion_auth->get_user_id(), 'customer_privacy')) ? $this->Home_model->count_new_users() : 0;
            $this->data['balance'] = ($user_res[0]['balance'] == NULL) ? 0 : $user_res[0]['balance'];
            $this->data['products'] = $this->Home_model->count_products($user_id);
            $this->data['seller_earnings'] = $this->Home_model->total_earnings($type = 'seller');
            $this->data['username'] =  $user_res[0]['username'];
            $this->data['ratings'] =  fetch_details("seller_data", ['user_id' => $user_id], "rating,no_of_ratings");
            $this->data['meta_description'] = 'Seller Panel | ' . $settings['app_name'];
            $this->data['count_products_low_status'] = $this->Home_model->count_products_stock_low_status($user_id);
            $this->data['count_products_availability_status'] = $this->Home_model->count_products_availability_status($user_id);
            $orders_count['awaiting'] = orders_count("awaiting", $user_id);
            $orders_count['received'] = orders_count("received", $user_id);
            $orders_count['processed'] = orders_count("processed", $user_id);
            $orders_count['shipped'] = orders_count("shipped", $user_id);
            $orders_count['delivered'] = orders_count("delivered", $user_id);
            $orders_count['cancelled'] = orders_count("cancelled", $user_id);
            $orders_count['returned'] = orders_count("returned", $user_id);
            $this->data['status_counts'] = $orders_count;
            $this->load->view('seller/template', $this->data);
        } elseif (isset($_SESSION) && isset($_SESSION["user_id"])) {
            $user_id = $_SESSION["user_id"];
            $user_group = fetch_details('users_groups', ['user_id' => $user_id], 'group_id');
            $group_id = $user_group[0]['group_id'];
            if ($group_id == 2) {
                redirect('home', 'refresh');
            } else {
                redirect('seller/login', 'refresh');
            }
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function profile()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $identity_column = $this->config->item('identity', 'ion_auth');
            $settings = get_settings('system_settings', true);
            $user_id = $this->session->userdata('user_id');
            $this->data['identity_column'] = $identity_column;
            $this->data['main_page'] = FORMS . 'profile';
            $this->data['title'] = 'Seller Profile | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Seller Profile | ' . $settings['app_name'];
            $shipping_method = get_settings('shipping_method', true);
            $this->data['shipping_method'] = $shipping_method;


            $this->data['fetched_data'] = $this->db->select(' u.*,sd.* ')
                ->join('users_groups ug', ' ug.user_id = u.id ')
                ->join('seller_data sd', ' sd.user_id = u.id ')
                ->where(['ug.group_id' => '4', 'ug.user_id' => $user_id])
                ->get('users u')
                ->result_array();
            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/home', 'refresh');
        }
    }

    public function update_status()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = DEMO_VERSION_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
            if ($_GET['status'] == '1') {
                $_GET['status'] = 0;
            } else if ($_GET['status'] == '0') {
                $_GET['status'] = 1;
            }
            $this->db->trans_start();
            if ($_GET['table'] == 'users') {
                $this->db->set('active', $this->db->escape($_GET['status']));
            } else {
                $this->db->set('status', $this->db->escape($_GET['status']));
            }

            $this->db->where('id', $_GET['id'])->update($_GET['table']);
            $this->db->trans_complete();
            $error = false;
            $message = str_replace('_', ' ', $_GET['table']);
            if ($this->db->trans_status() === true) {
                $error = true;
            }
            $response['error'] = $error;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = $message;
            print_r(json_encode($response));
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function fetch_sales()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $sales[] = array();

            $all_months = [
                'Jan' => 0,
                'Feb' => 0,
                'Mar' => 0,
                'Apr' => 0,
                'May' => 0,
                'Jun' => 0,
                'Jul' => 0,
                'Aug' => 0,
                'Sep' => 0,
                'Oct' => 0,
                'Nov' => 0,
                'Dec' => 0
            ];


            $month_res = $this->db->select('SUM(sub_total) AS total_sale,DATE_FORMAT(date_added,"%b") AS month_name ')
                ->where('seller_id', $_SESSION['user_id'])
                ->group_by('year(CURDATE()),MONTH(date_added)')
                ->order_by('year(CURDATE()),MONTH(date_added)')
                ->get('`order_items`')->result_array();

            foreach ($month_res as $sale) {
                if (isset($all_months[$sale['month_name']])) {
                    $all_months[$sale['month_name']] = (float)$sale['total_sale'];
                }
            }

            // Format the data for the final response
            $month_wise_sales = [
                'total_sale' => array_values($all_months),  // Get just the sales figures
                'month_name' => array_keys($all_months)     // Get just the month names
            ];

            $sales[0] = $month_wise_sales;


            //week wise sales

            $all_days = [
                'Sunday' => 0,
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0
            ];
            $d = strtotime("today");
            $start_week = strtotime("last sunday midnight", $d);
            $end_week = strtotime("next saturday", $d);
            $start = date("Y-m-d", $start_week);
            $end = date("Y-m-d", $end_week);
            $week_res = $this->db->select("DATE_FORMAT(date_added, '%d-%b') as date, SUM(sub_total) as total_sale")
                ->where('seller_id', $_SESSION['user_id'])
                ->where("date(date_added) >='$start' and date(date_added) <= '$end' ")
                ->group_by('day(date_added)')->get('`order_items`')->result_array();


            // $week_wise_sales['total_sale'] = array_map('intval', array_column($week_res, 'total_sale'));
            // $week_wise_sales['week'] = array_column($week_res, 'date');

            // Map the week results to day names and update the sales data
            foreach ($week_res as $sale) {
                // Convert the 'date' field to a timestamp to get the day of the week
                $day_name = date('l', strtotime($sale['date'])); // 'l' gives the full day name (Monday, Tuesday, etc.)

                // Add the sales total to the correct day
                if (isset($all_days[$day_name])) {
                    $all_days[$day_name] = (float)$sale['total_sale'];
                }
            }

            // Format the data for the final response
            $week_wise_sales = [
                'total_sale' => array_values($all_days),  // Get just the sales figures
                'week' => array_keys($all_days)       // Get just the day names
            ];
            $sales[1] = $week_wise_sales;


            // day wise
            $day_res = $this->db->select("DAY(date_added) as date, SUM(sub_total) as total_sale")
                ->where('seller_id', $_SESSION['user_id'])
                ->where('date_added >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)')
                ->group_by('day(date_added)')->get('`order_items`')->result_array();

            // $day_wise_sales['total_sale'] = array_map('intval', array_column($day_res, 'total_sale'));
            // $day_wise_sales['day'] = array_column($day_res, 'date');

            // Initialize an array to store sales data for each day of the last 30 days (0 sales by default)
            $all_days = array_fill(0, 30, 0);  // Initialize with 0 sales for each day

            // Map the day_res results to the corresponding day
            foreach ($day_res as $sale) {
                // $sale['date'] gives the day of the month, so we need to map it to the corresponding index in $all_days
                $day_of_month = (int)$sale['date'];

                // Store the total sales for that day
                $all_days[$day_of_month - 1] = (float)$sale['total_sale'];  // Subtract 1 to match the array index (0-based)
            }

            // Format the final data for response
            $day_wise_sales = [
                'total_sale' => $all_days,  // Sales values for each day
                'day' => range(1, 30)       // Days of the month (1 to 30)
            ];


            $sales[2] = $day_wise_sales;
            print_r(json_encode($sales));
        } else {
            redirect('seller/login', 'refresh');
        }
    }


    public function category_wise_product_count()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $user_id = $this->session->userdata('user_id');
            $this->db->select('category_ids');
            $where = 'user_id = ' . $user_id;
            $this->db->where($where);
            $result = $this->db->get('seller_data')->result_array();
            $result = explode(",", $result[0]['category_ids']);

            $res = $this->db->select('c.name as name,count(c.id) as counter')->group_Start()->where_in('c.id', $result)->group_End()->where(['p.status' => '1', 'c.status' => '1'])->join('products p', 'p.category_id=c.id')->group_by('c.id')->get('categories c')->result_array();
            $result = array();
            $result[0][] = 'Task';
            $result[0][] = 'Hours per Day';
            array_walk($res, function ($v, $k) use (&$result) {
                $result[$k + 1][] = $v['name'];
                $result[$k + 1][] = intval($v['counter']);
            });
            echo json_encode(array_values($result));
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function logout()
    {
        $this->ion_auth->logout();
        redirect('seller/login', 'refresh');
    }
}
