<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Orders</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('delivery_boy/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-innr">
                            <div class="gaps-1-5x row d-flex adjust-items-center">
                                <div class="form-group col-md-4">
                                    <label>Date and time range:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="datepicker">
                                        <input type="hidden" id="start_date" class="form-control float-right">
                                        <input type="hidden" id="end_date" class="form-control float-right">
                                    </div>
                                    <!-- /.input group -->
                                </div>
                                <div class="form-group col-md-8">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Filter By status</label>
                                            <select id="order_status" name="order_status" placeholder="Select Status" required="" class="form-control">
                                                <option value="">All Orders</option>
                                                <option value="processed">Processed</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="returned">Returned</option>
                                            </select>
                                        </div>
                                        <!-- Filter By payment  -->
                                        <div class="form-group col-md-5">
                                            <div>
                                                <label>Filter By Payment Method</label>
                                                <select id="payment_method" name="payment_method" placeholder="Select Payment Method" required="" class="form-control">
                                                    <option value="">All Payment Methods</option>
                                                    <option value="COD">Cash On Delivery</option>
                                                    <option value="online-payment">Online Payment</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 mt-4">
                                            <button type="button" class="btn btn-default mt-2" onclick="status_date_wise_search()">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 main-content">
                                <div class="card content-area p-4">
                                    <div class="card-innr">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#orders_table">Orders</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#order_items_table">Return Order</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <div id="orders_table" class="tab-pane active"><br>
                                                <table class='table-striped' data-toggle="table" data-url="<?= base_url('delivery_boy/orders/consignment_view') ?>"
                                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                    data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-order="desc"
                                                    data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                                    data-export-options='{
                                                        "fileName": "orders-list",
                                                        "ignoreColumn": ["state"] 
                                                        }' data-query-params="home_query_params">
                                                    <thead>
                                                        <tr>
                                                            <th data-field="id" data-sortable='true' data-footer-formatter="totalFormatter">ID</th>
                                                            <th data-field="order_id" data-sortable='true'>Order ID</th>
                                                            <th data-field="username" data-sortable='true'>Buyer Name</th>
                                                            <th data-field="mobile" data-sortable='true'>Buyer Mobile</th>
                                                            <th data-field="product_name" data-sortable='true'>Product Name</th>
                                                            <th data-field="quantity" data-sortable='true'>Quantity</th>
                                                            <th data-field="status" data-sortable='true'>Status</th>
                                                            <th data-field="payment_method" data-sortable='true'>Payment Method</th>
                                                            <th data-field="order_date" data-sortable='true'>Order Date</th>
                                                            <th data-field="operate" data-sortable="false">Action</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                            <div id="order_items_table" class="tab-pane fade"><br>
                                                <table class='table-striped' data-toggle="table" data-url="<?= base_url('delivery_boy/orders/view_orders') ?>"
                                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                    data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="oi.id"
                                                    data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true"
                                                    data-export-types='["txt","excel","csv"]' data-export-options='{"fileName": "order-item-list","ignoreColumn": ["state"] }'
                                                    data-query-params="orders_query_params">
                                                    <thead>
                                                        <tr>
                                                            <th data-field="id" data-sortable='true' data-footer-formatter="totalFormatter">ID</th>
                                                            <th data-field="order_item_id" data-sortable='true'>Order Item ID</th>
                                                            <th data-field="order_id" data-sortable='true'>Order ID</th>
                                                            <th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
                                                            <th data-field="seller_id" data-sortable='true' data-visible="false">Seller ID</th>
                                                            <th data-field="is_credited" data-sortable='true' data-visible="false">Commission</th>
                                                            <th data-field="quantity" data-sortable='true' data-visible="false">Quantity</th>
                                                            <th data-field="username" data-sortable='true'>User Name</th>
                                                            <th data-field="seller_name" data-sortable='true'>Seller Name</th>
                                                            <th data-field="product_name" data-sortable='true'>Product Name</th>
                                                            <th data-field="mobile" data-sortable='true' data-visible='false'>Mobile</th>
                                                            <th data-field="sub_total" data-sortable='true' data-visible="true">Total(<?= $curreny ?>)</th>
                                                            <th data-field="delivery_boy" data-sortable='true' data-visible='false'>Deliver By</th>
                                                            <th data-field="delivery_boy_id" data-sortable='true' data-visible='false'>Delivery Boy Id</th>
                                                            <th data-field="product_variant_id" data-sortable='true' data-visible='false'>Product Variant Id</th>
                                                            <th data-field="delivery_date" data-sortable='true' data-visible='false'>Delivery Date</th>
                                                            <th data-field="delivery_time" data-sortable='true' data-visible='false'>Delivery Time</th>
                                                            <th data-field="updated_by" data-sortable='true' data-visible="false">Updated by</th>
                                                            <th data-field="active_status" data-sortable='true' data-visible='true'>Active Status</th>
                                                            <th data-field="transaction_status" data-sortable='true' data-visible='false'>Transaction Status</th>
                                                            <th data-field="date_added" data-sortable='true'>Order Date</th>
                                                            <th data-field="operate" data-sortable="false">Action</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .card-innr -->
                    </div><!-- .card -->
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>