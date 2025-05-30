<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid p-3">

            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="align-self-center text-warning">
                                        <i class="ion-ios-cart-outline display-4"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h5 class="text-muted text-bold-500">Orders</h5>
                                        <h3 class="text-bold-600"><?= $order_counter ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="align-self-center text-primary">
                                        <i class="ion-ios-personadd-outline display-4"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h5 class="text-muted text-bold-500">New Signups</h5>
                                        <h3 class="text-bold-600"><?= $user_counter ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="align-self-center text-success">
                                        <i class="ion-ios-people-outline display-4"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h5 class="text-muted text-bold-500">Delivery Boys</h5>
                                        <h3 class="text-bold-600"><?= $delivery_boy_counter ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="card pull-up">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media d-flex">
                                    <div class="align-self-center text-info">
                                        <i class="ion-ios-albums-outline display-4 display-4"></i>
                                    </div>
                                    <div class="media-body text-right">
                                        <h5 class="text-muted text-bold-500">Products</h5>
                                        <h3 class="text-bold-600"><?= $product_counter ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-12" id="ecommerceChartView" >
                    <div class="card card-shadow chart-height">
                        <!-- Labels for toggling charts -->

                        <div class="card-body">
                            <div>
                                <h3 class="card-title">Sales Summary</h3>
                                <div class="labels ">
                                    <ul class="nav nav-pills nav-pills-rounded chart-action float-right btn-group sales-tab" role="group">
                                        <li class="nav-item"><a class="btn-sm nav-link px px-2 py-1 active monthlyChart" data-toggle="tab" href="#Monthly">Month</a></li>
                                        <li class="nav-item"><a class="btn-sm nav-link px px-2 py-1 weeklyChart" data-toggle="tab" href="#Weekly">Week</a></li>
                                        <li class="nav-item"><a class="btn-sm nav-link px px-2 py-1 dailyChart" data-toggle="tab" href="#Daily">Day</a></li>
                                    </ul>
                                </div>
                            </div>



                            <!-- Div containers for the charts -->
                            <div id="Chart" class="chart-container mt-5"></div>
                        </div>

                        
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Category Wise Product's Sales -->
                    <div class="card ">
                        <div class="card-header">
                            <h3 class="card-title ">Category Wise Product's Count</h3>
                        </div>
                        <div class="card-body">

                                <div id="piechart_3d" class='piechat_height'></div>

                        </div>

                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <?php
                $settings = get_settings('system_settings', true);
                $currency = (isset($settings['currency']) && !empty($settings['currency'])) ? $settings['currency'] : '';
                ?>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box total-info-box ">
                        <span class="info-box-icon text-white"> <i class="far fa-money-bill-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-white">Total Earnings (<?= $currency ?>) </span>
                            <span class="info-box-number text-white"><?= number_format($total_earnings, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box details-box">
                        <span class="info-box-icon text-white"> <i class="far fa-money-bill-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-white">Admin Earnings (<?= $currency ?>) </span>
                            <span class="info-box-number text-white"><?= number_format($admin_earnings, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"> <i class="far fa-money-bill-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Seller Earnings (<?= $currency ?>) </span>
                            <span class="info-box-number"><?= number_format($seller_earnings, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="alert sold-products ">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h6><i class="icon fa fa-info"></i> <?= $count_products_availability_status ?> Product(s) sold out!</h6>
                        <a href="<?= base_url('admin/product/?flag=sold') ?>" class="text-decoration-none small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <?php $settings = get_settings('system_settings', true); ?>
                <div class="col-md-6 col-xs-12">
                    <div class="alert alert-primary alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h6><i class="icon fa fa-info"></i> <?= $count_products_low_status ?> Product(s) low in stock!<small> (Low stock limit <?= isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : '5' ?>)</small></h6>
                        <a href="<?= base_url('admin/product/?flag=low') ?>" class="text-decoration-none small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <h5 class="col">Sellers Details</h5>
                <div class="row col-12 d-flex">

                    <div class="col-4">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (isset($count_approved_sellers) && !empty($count_approved_sellers)) ?  $count_approved_sellers : 0; ?></h3>
                                <p><button class='btn btn-outline-success text-white border-0' data-toggle="modal" data-target="#approved_sellers">Approved sellers</button></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (isset($count_not_approved_sellers) && !empty($count_not_approved_sellers)) ?  $count_not_approved_sellers : 0; ?></h3>
                                <p><button class='btn btn-outline-secondary text-white border-0' data-toggle="modal" data-target="#not_approved_sellers">Not Approved Sellers</button></p>

                            </div>
                            <div class="icon">

                                <i class="fa fa-xs fa-pause-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= (isset($count_deactive_sellers) && !empty($count_deactive_sellers)) ?  $count_deactive_sellers : 0; ?></h3>
                                <p><button class='btn btn-outline-danger text-white border-0' data-toggle="modal" data-target="#deactive_sellers">Deactiveted sellers</button></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="m-3">Top Sellers</div>
                                <div class="card-body">
                                    <table class='table-striped' id='seller_table' data-toggle="table" data-url="<?= base_url('admin/sellers/top_seller') ?>" data-click-to-select="true" data-side-pagination="server" data-show-columns="true" data-show-refresh="true" data-sort-name="sd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="seller_id" data-sortable="true">ID</th>
                                                <th data-field="seller_name" data-sortable="true">Seller name</th>
                                                <th data-field="store_name" data-sortable="false">Store name</th>
                                                <th data-field="total" data-sortable="false">Total</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="card h-100">
                                <div class="m-3">Top Categories</div>
                                <div class="card-body">
                                    <table class='table-striped' id='seller_table' data-toggle="table" data-url="<?= base_url('admin/Category/top_category')  ?>" data-click-to-select="true" data-side-pagination="server" data-show-columns="true" data-show-refresh="true" data-sort-name="sd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">ID</th>
                                                <th data-field="name" data-sortable="false">Category Name</th>
                                                <th data-field="clicks" data-sortable="false">Clicks</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <h5 class="col mt-3">Order items Outlines</h5>
                <div class="row col-12 d-flex">
                    <div class="col-3">
                        <div class="small-box awaiting-box">
                            <div class="inner">
                                <h3 class="text-white"><?= $status_counts['awaiting'] ?></h3>
                                <p class="text-white">Awaiting</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-history"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box received-box">
                            <div class="inner">
                                <h3 class="text-white"><?= $status_counts['received'] ?></h3>
                                <p class="text-white">Received</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-level-down-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box processed-box">
                            <div class="inner">
                                <h3 class="text-white"><?= $status_counts['processed'] ?></h3>
                                <p class="text-white">Processed</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-people-carry"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box shipped-box">
                            <div class="inner">
                                <h3 class="text-white"><?= $status_counts['shipped'] ?></h3>
                                <p class="text-white">Shipped</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-shipping-fast"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box delivered-box">
                            <div class="inner">
                                <h3 class="text-white"><?= $status_counts['delivered'] ?></h3>
                                <p class="text-white">Delivered</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $status_counts['cancelled'] ?></h3>
                                <p>Cancelled</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3><?= $status_counts['returned'] ?></h3>
                                <p>Returned</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-xs fa-level-up-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-innr">
                            <div class="gaps-1-5x row d-flex adjust-items-center">
                                <div class="row col-md-12">
                                    <div class="form-group col-md-4">
                                        <label>Date range:</label>
                                        <div class="input-group col-md-12">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                                            </div>
                                            <input type="text" class="form-control float-right" id="datepicker">
                                            <input type="hidden" id="start_date" class="form-control float-right">
                                            <input type="hidden" id="end_date" class="form-control float-right">
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <!-- Filter By payment  -->
                                    <div class="form-group col-md-3">
                                        <div>
                                            <label>Filter By Payment Method</label>
                                            <select id="payment_method" name="payment_method" placeholder="Select Payment Method" required="" class="form-control">
                                                <option value="">All Payment Methods</option>
                                                <option value="COD">Cash On Delivery</option>
                                                <option value="Paypal">Paypal</option>
                                                <option value="RazorPay">RazorPay</option>
                                                <option value="Paystack">Paystack</option>
                                                <option value="Flutterwave">Flutterwave</option>
                                                <option value="Paytm">Paytm</option>
                                                <option value="Stripe">Stripe</option>
                                                <option value="bank_transfer">Direct Bank Transfers</option>
                                                <option value="midtrans">Midtrans</option>
                                                <option value="my_fatoorah">My Fatoorah</option>
                                                <option value="instamojo">Instamojo</option>
                                                <option value="phonepe">PhonePe</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="status_date_wise_search()">Filter</button>
                                    </div>
                                </div>
                            </div>
                            <table class='table-striped' data-toggle="table" data-url="<?= base_url('admin/orders/view_orders') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]' data-export-options='{
                        "fileName": "order-list",
                        "ignoreColumn": ["state"] 
                        }' data-query-params="home_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable='true' data-footer-formatter="totalFormatter">Order ID</th>
                                        <th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
                                        <th data-field="sellers" data-sortable='true'>Sellers</th>
                                        <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                        <th data-field="name" data-sortable='true'>User Name</th>
                                        <th data-field="mobile" data-sortable='true' data-visible="false">Mobile</th>
                                        <th data-field="items" data-sortable='true' data-visible="false">Items</th>
                                        <th data-field="total" data-sortable='true' data-visible="true">Total(<?= $curreny ?>)</th>
                                        <th data-field="delivery_charge" data-sortable='true' data-footer-formatter="delivery_chargeFormatter" data-visible="true">D.Charge</th>
                                        <th data-field="wallet_balance" data-sortable='true' data-visible="true">Wallet Used(<?= $curreny ?>)</th>
                                        <th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
                                        <th data-field="promo_discount" data-sortable='true' data-visible="true">Promo disc.(<?= $curreny ?>)</th>
                                        <th data-field="final_total" data-sortable='true'>Final Total(<?= $curreny ?>)</th>
                                        <th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
                                        <th data-field="payment_method" data-sortable='true' data-visible="true">Payment Method</th>
                                        <th data-field="address" data-sortable='true'>Address</th>
                                        <th data-field="delivery_date" data-sortable='true' data-visible='false'>Delivery Date</th>
                                        <th data-field="delivery_time" data-sortable='true' data-visible='false'>Delivery Time</th>
                                        <th data-field="notes" data-sortable='false' data-visible='false'>O. Notes</th>
                                        <th data-field="date_added" data-sortable='true'>Order Date</th>
                                        <th data-field="operate" data-sortable="false">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div><!-- .card-innr -->
                    </div><!-- .card -->
                </div>
            </div>

        </div>
    </section>
    <div class="modal fade" id="order-tracking-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Order Tracking</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="tab-pane " role="tabpanel" aria-labelledby="product-rating-tab">
                        <input type="hidden" name="order_id" id="order_id">
                        <table class='table-striped' id="order_tracking_table" data-toggle="table" data-url="<?= base_url('admin/orders/get-order-tracking') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-query-params="order_tracking_query_params">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="order_id" data-sortable="true">Order ID</th>
                                    <th data-field="order_item_id" data-sortable="false">Order Item ID</th>
                                    <th data-field="courier_agency" data-sortable="false">Courier Agency</th>
                                    <th data-field="tracking_id" data-sortable="false">Tracking ID</th>
                                    <th data-field="url" data-sortable="false">URL</th>
                                    <th data-field="date" data-sortable="false">Date</th>
                                    <th data-field="operate" data-sortable="false">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="deactive_sellers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Deactivate Sellers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class='table-striped' id='seller_table' data-toggle="table" data-url="<?= base_url('admin/sellers/deactive_sellers') ?>" data-click-to-select="true" data-side-pagination="" data-pagination="true" data-page-list="[1,2,3,4]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="sd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="false">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile No</th>
                            <th data-field="date" data-sortable="true">Date</th>
                            <th data-field="operate" data-sortable="false">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="modal col-12 fade" id="approved_sellers" tabindex="-1" role="dialog" aria-labelledby="approved_sellers" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Approved seller</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class='table-striped' id='seller_table' data-toggle="table" data-url="<?= base_url('admin/sellers/approved_sellers') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5,10,15,20,25]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="sd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="false">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile No</th>
                            <th data-field="date" data-sortable="true">Date</th>
                            <th data-field="operate" data-sortable="false">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>
<div class="modal col-12 fade" id="not_approved_sellers" tabindex="-1" role="dialog" aria-labelledby="approved_sellers" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Not Approved Sellers</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class='table-striped' id='seller_table' data-toggle="table" data-url="<?= base_url('admin/sellers/not_approved_sellers') ?>" data-click-to-select="true" data-side-pagination="client" data-pagination="true" data-page-list="[1,3,5,7,10]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="sd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="false">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile No</th>
                            <th data-field="date" data-sortable="true">Date</th>
                            <th data-field="operate" data-sortable="false">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>