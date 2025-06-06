<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>eShop Purchase Code Validator</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Purchase Code</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <form class="form-horizontal form-submit-event" action="<?= base_url('admin/purchase-code/validator'); ?>" method="POST" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="purchase_code" class="col-sm-2 col-form-label">eShop Purchase Code for web<span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="purchase_code" placeholder="Enter your purchase code here" name="web_purchase_code" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="purchase_code" class="col-sm-2 col-form-label">eShop Purchase Code for app<span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="purchase_code" placeholder="Enter your purchase code here" name="app_purchase_code" value="">
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-success" id="submit_btn"><?= (isset($fetched_data[0]['id'])) ? 'Update Ticket Type' : 'Register Now' ?></button>
                                </div>
                            </div>

                        </form>
                        <?php $doctor_brown = get_settings('doctor_brown', true);
                        if (!empty($doctor_brown) && isset($doctor_brown['code_bravo'])) { ?>
                            <div class="alert alert-success m-2">
                                Your system is successfully registered with us! Enjoy selling online!
                            </div>
                        <?php } ?>
                    </div>
                    <!--/.card-->
                </div>
                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>