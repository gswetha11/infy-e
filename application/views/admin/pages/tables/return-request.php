<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Return Request</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Return Request</li>
                    </ol>
                </div>

            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="request_rating_modal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Update Return Request</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal form-submit-event" action="<?= base_url('admin/return_request/update-return-request'); ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="return_request_id" id="return_request_id">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <input type="hidden" name="order_item_id" id="order_item_id">
                                    <input type="hidden" name="seller_id" id="seller_id">
                                    <input type="hidden" name="delivery_boy_id" id="delivery_boy_id">

                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Status <span class='text-danger text-sm'>*</span></label>
                                        <div class="">
                                            <div id="status" class="">
                                                <label class="btn btn-warning" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="status" value="0" class='pending'> Pending
                                                </label>
                                                <label class="btn btn-danger" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="status" value="2" class='rejected' id="rejected"> Rejected
                                                </label>
                                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="status" value="1" class='approved' id="approved"> Approved
                                                </label>
                                                <label class="btn btn-secondary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="status" value="8" class='return_pickedup' id="return_pickedup"> Return Pickedup
                                                </label>
                                                <label class="btn btn-success" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="status" value="3" class='returned' id="returned"> Returned
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5 d-none" id="return_request_delivery_by">

                                        <select id='deliver_by' name='deliver_by' class='form-control'>
                                            <option value=''>Select Delivery Boy</option>
                                           
                                        </select>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="" for="">Remark</label>
                                        <textarea id="update_remarks" name="update_remarks" class="form-control col-12 "></textarea>
                                    </div>
                                    <input type="hidden" id="id" name="id">
                                    <div class="ln_solid"></div>
                                    <div class="form-group">
                                        <button type="reset" class="btn btn-warning">Reset</button>
                                        <button type="submit" class="btn btn-success" id="submit_btn">Update</button>
                                    </div>

                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 main-content">
            <div class="card content-area p-4">
                <div class="card-innr">
                    <div class="gaps-1-5x"></div>
                    <table class='table-striped' id='return_request_table' data-toggle="table" data-url="<?= base_url('admin/return_request/view_return_request_list') ?>" data-click-to-select="true"
                     data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-query-params="queryParams">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="order_id" data-sortable="true">Order ID</th>
                                <th data-field="order_item_id" data-sortable="true">Order Item ID</th>
                                <th data-field="user_name" data-sortable="false">Username</th>
                                <th data-field="product_name" data-sortable="false">Product Name</th>
                                <th data-field="return_reason" data-sortable="false">Return Reason</th>
                                <th data-field="return_item_image" data-sortable="false">Return item image</th>
                                <th data-field="price" data-sortable="false">Price</th>
                                <th data-field="seller_id" data-sortable="false">Seller ID</th>
                                <th data-field="discounted_price" data-sortable="false" data-visible="false">Discounted Price</th>
                                <th data-field="quantity" data-sortable="false">Quantity</th>
                                <th data-field="sub_total" data-sortable="false">Sub Total</th>
                                <th data-field="status" data-sortable="false">Status</th>
                                <th data-field="operate" data-sortable="false">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- .card-innr -->
            </div><!-- .card -->
        </div>
</div>
<!-- /.row -->
</div><!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>