<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>View Sale Reports</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Sales Reports</li>
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
                                <div class="row col-md-12">
                                    <div class="form-group col-md-4">
                                        <label>From & To Date</label>
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
                                    <div class="form-group col-md-4">
                                        <div>
                                            <label>Seller Name</label>
                                            <select class='form-control' name='seller_id' id="seller_id">
                                                <option value="">Select Seller </option>
                                                <?php foreach ($sellers as $seller) { ?>
                                                    <option value="<?= $seller['seller_id'] ?>" <?= (isset($product_details[0]['seller_id']) && $product_details[0]['seller_id'] == $seller['seller_id']) ? 'selected' : "" ?>><?= $seller['seller_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="status_date_wise_search()">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped" data-detail-view="true" data-detail-formatter="salesReport" data-auto-refresh="true" data-toggle="table" 
                        data-url="<?= base_url('admin/Sales_report/get_sales_report_list') ?>" data-side-pagination="server" data-pagination="true" 
                        data-page-list="[5, 10, 25, 50, 100, 200, All]" data-search="true" data-show-columns="true" data-show-columns-search="true" 
                        data-show-refresh="true" data-sort-name="id" data-sort-order="DESC" data-maintain-selected="true" data-show-export="true" data-export-types='["txt","excel"]' data-export-options='{
                        "fileName": "Saller-sale-list",
                        "ignoreColumn": ["operate"]
                        }'  data-query-params="sales_report_query_params">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable='true'><?= labels('id', 'Order item ID') ?></th>
                                    <th data-field="user_id" data-sortable='true' ><?= labels('user_id', 'User ID') ?></th>
                                    <th data-field="name" data-sortable='true'><?= labels('name', 'User Name') ?></th>
                                    <th data-field="product_name" data-sortable='true'><?= labels('product_name', 'Product name') ?></th>
                                    <th data-field="mobile" data-visiable="false" data-sortable='true'><?= labels('mobile', ' Mobile') ?></th>
                                    <th data-field="address" data-sortable='true'><?= labels('address', 'Address') ?></th>
                                    <th data-field="final_total" data-sortable='true'><?= labels('final_total', 'Final Total') ?></th>
                                    <th data-field="date_added" data-sortable='true'><?= labels('date_added', 'Order Date') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div><!-- .card-innr -->
                </div><!-- .card -->
            </div>

        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>