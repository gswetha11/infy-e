<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Invoice</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home'); ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Invoice</li>
                    </ol>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <?php $sellers = array_values(array_unique(array_column($order_detls, "seller_id"))); ?>

    <section class="content">
        <div class="container-fluid">
            <div class="row  m-3">
                <div class="col-md-12">
                    <div class="card card-info mb-3">
                        <div class="row ml-4 mt-3 text-secondary d-flex justify-content-between">
                            <h4>Order Summary</h4>
                        </div>

                        <!--Order Summary row -->
                        <div class="row m-4 mt-3  d-flex justify-content-between">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered border border-dark text-center text-sm">
                                    <thead class="">
                                        <tr>
                                            <th>Total Order Price(<?= $settings['currency'] ?>)</th>
                                            <?php if ($order_detls[0]['type'] != 'digital_product') { ?>
                                                <th>Delivery Charge (<?= $settings['currency'] ?>)</th>
                                            <?php } ?>
                                            <th>Wallet Used (<?= $settings['currency'] ?>)</th>
                                            <?php
                                            if (isset($promo_code[0]['promo_code'])) { ?>
                                                <th>Promo (
                                                    <?= $promo_code[0]['promo_code'] ?>
                                                    <?= ($promo_code[0]['discount_type'] == 'percentage') ? '%' : ' '; ?> )
                                                </th>
                                            <?php } ?>
                                            <?php
                                            if (isset($order_detls[0]['discount']) && $order_detls[0]['discount'] > 0 && $order_detls[0]['discount'] != NULL) { ?>
                                                <th>Special Discount<?= $settings['currency'] ?>(<?= $order_detls[0]['discount'] ?>)</th>
                                            <?php
                                            }
                                            ?>
                                            <th>Total Payable (<?= $settings['currency'] ?>)</th>
                                            <th>Final Total (<?= $settings['currency'] ?>)</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total = $order_detls[0]['total']; ?>
                                        <tr>
                                            <td><?= $settings['currency'] . '  ' . number_format($order_detls[0]['total'], 2) ?></td>
                                            <?php if ($order_detls[0]['type'] != 'digital_product') { ?>
                                                <td>+
                                                    <?php $total += $order_detls[0]['delivery_charge'];
                                                    echo  $settings['currency'] . '  ' . number_format($order_detls[0]['delivery_charge'], 2); ?>
                                                </td>
                                            <?php } ?>
                                            <td><?php $total -= $order_detls[0]['wallet_balance'];
                                                echo  '- ' . $settings['currency'] . '  ' . number_format($order_detls[0]['wallet_balance'], 2); ?></td>
                                            <?php if (isset($promo_code[0]['promo_code'])) { ?>
                                                <td>-
                                                    <?php
                                                    echo  $settings['currency'] . '  ' . $order_detls[0]['promo_discount'];
                                                    $total = $total - $order_detls[0]['promo_discount'];
                                                    ?>
                                                </td>
                                            <?php } ?>
                                            <?php
                                            if (isset($order_detls[0]['discount']) && $order_detls[0]['discount'] > 0 && $order_detls[0]['discount'] != NULL) { ?>
                                                <td>-
                                                    <?php echo  $settings['currency'] . '  ' . $special_discount = round($total * $order_detls[0]['discount'] / 100, 2);
                                                    $total = floatval($total - $special_discount);
                                                    ?>
                                                </td>
                                            <?php
                                            }
                                            ?>
                                            <td><?= $settings['currency'] . '  ' . number_format($total, 2) ?></td>
                                            <td>
                                                <?php $final_total = $order_detls[0]['total'] - $order_detls[0]['discount'] -  $order_detls[0]['promo_discount'] +  $order_detls[0]['delivery_charge']; ?>
                                                <?= $settings['currency'] . '  ' . number_format($final_total, 2) ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                    <!-- Table row -->
                    <!-- seller container -->

                    <?php for ($i = 0; $i < count($sellers); $i++) {
                        $s_user_data = fetch_details('users', ['id' => $sellers[$i]], 'email,mobile,address,country_code,city,pincode');
                        $seller_data = fetch_details('seller_data', ['user_id' => $sellers[$i]], 'store_name,pan_number,tax_name,tax_number,authorized_signature');

                        $order_caharges_data = fetch_details('order_charges', ['order_id' => $order_detls[0]['order_id'], 'seller_id' => $sellers[$i]]);

                    ?>
                        <div class="card card-info mb-4" id="<?= 'invoice-' . $sellers[$i] ?>">
                            <div class="container-fluid">
                                <div class="row mt-2" id="section-not-to-print">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4 text-center">
                                        <h3><strong>Invoice : <?= $i + 1 ?></strong></h3>
                                    </div>
                                    <div class="col-md-4"></div>
                                </div>
                                <div class="print-section">
                                    <div class="row m-3">
                                        <div class="col-md-12 d-flex justify-content-between">
                                            <h2 class="text-left">
                                                <img src="<?= base_url()  . get_settings('logo') ?>" class="d-block invoice_logo">
                                            </h2>
                                            <h4 class="text-right">
                                                Mo. <?= $order_detls[0]['mobile_number'] ?>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="row m-2 mt-3">
                                        <div class="col-md-3">
                                            <strong>
                                                <p>Sold By</p>
                                            </strong>
                                            <?= ucfirst($seller_data[0]['store_name']); ?><br>
                                            <?=  str_replace('\\', '', $seller_data[0]['address']); ?>
                                            <br><?= isset($s_user_data[0]['city']) ? ucfirst($s_user_data[0]['city']) . ',' : ''; ?><?= $s_user_data[0]['pincode'] ?>
                                            <p>Email: <?= $s_user_data[0]['email']; ?><br>
                                                Customer Care : <?= $s_user_data[0]['mobile']; ?></p>

                                            <?php if (isset($seller_data[0]['pan_number']) && !empty($seller_data[0]['pan_number'])) { ?>
                                                <strong>
                                                    <p>Pan Number :
                                                </strong><?= $seller_data[0]['pan_number']; ?></p>
                                            <?php } ?>
                                            <strong>
                                                <p><?= $seller_data[0]['tax_name']; ?> :
                                            </strong> <?= $seller_data[0]['tax_number']; ?></p>

                                            <?php if ($order_detls[0]['type'] != 'digital_product' && !empty($items[$i]['delivery_boy'])) { ?>
                                                <strong>
                                                    <p>Delivery By :
                                                </strong> <?= $items[$i]['delivery_boy']; ?></p>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-3">
                                            <strong>
                                                <p>Shipping Address</p>
                                            </strong>
                                            <address>
                                                <?= ($order_detls[0]['user_name'] != "") ? $order_detls[0]['user_name'] : $order_detls[0]['uname'] ?><br>
                                                <?= $order_detls[0]['address'] ?><br>
                                            </address>
                                            <br> <b>Order No : </b>#
                                            <?= $order_detls[0]['id'] ?>
                                            <br> <b>Order Date: </b>
                                            <?= $order_detls[0]['date_added'] ?>
                                        </div>

                                    </div>
                                    <div class="row m-3">
                                        <p>Product Details:</p>
                                    </div>
                                    <div class="row m-3">
                                        <div class="col-md-12 table-responsive">
                                            <table class="table borderless text-center text-sm">
                                                <thead class="">
                                                    <tr>
                                                        <th>Sr No.</th>
                                                        <th>Product Code</th>
                                                        <th>Name</th>
                                                        <th>variants</th>
                                                        <th>HSN Code</th>
                                                        <th>Price</th>
                                                        <th>Tax (%)</th>
                                                        <th>Tax Amount (₹)</th>
                                                        <th>Qty</th>
                                                        <th>SubTotal (₹)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $j = 1;
                                                    $total = $quantity  = $total_discount = $final_sub_total = 0;

                                                    foreach ($items as $row) {

                                                        $total_tax = 0;
                                                        $product_id = $row['product_id'];  // ensure $row['product_id'] contains the correct product ID

                                                        $order_tax_ids = (isset($row['tax_ids']) && !empty($row['tax_ids'])) ? explode(',', $row['tax_ids']) : array();
                                                        
                                                        $taxes = [];
                                                        foreach ($order_tax_ids as $tax_id) {
                                                            $tax = getTtaxById($tax_id);
                                                            if ($tax) {
                                                                $taxes[] = $tax;
                                                            }
                                                        }

                                                        $total += floatval($row['price'] + $tax_amount) * floatval($row['quantity']);
                                                        if ($sellers[$i] == $row['seller_id']) {
                                                            $product_variants = get_variants_values_by_id($row['product_variant_id']);
                                                            $product_variants = isset($product_variants[0]['variant_values']) && !empty($product_variants[0]['variant_values']) ? str_replace(',', ' | ', $product_variants[0]['variant_values']) : '-';

                                                            $tax_amount  = $row['price'] - ($row['price'] * (100 / (100 + floatval($row['tax_percent']))));

                                                            $hsn_code = ($row['hsn_code']) ? $row['hsn_code'] : '-';
                                                            $quantity += floatval($row['quantity']);

                                                            $tax = (floatval($row['price']) * floatval($row['tax_percent'])) / 100;

                                                            $price_without_tax = $row['price'] - $tax_amount;

                                                            $sub_total = floatval($row['price']) * $row['quantity'];
                                                            $final_sub_total += $sub_total;
                                                    ?>
                                                            <tr>
                                                                <td><?= $j ?><br></td>
                                                                <td><?= $row['product_variant_id'] ?><br></td>
                                                                <td class="w-25"><?= $row['pname'] ?><br></td>
                                                                <td class="w-25"><?= $product_variants ?><br></td>
                                                                <td><?= $hsn_code ?><br></td>
                                                                <td><?= $settings['currency'] . ' ' . number_format($price_without_tax, 2) ?><br></td>
                                                                <td><?php foreach ($taxes as $tax) { ?>
                                                                        <div class="d-flex"><span><?= $tax['title'] ?></span>
                                                                            <span>-</span>
                                                                            <span><?= $tax['percentage'] . '%' ?> </span>
                                                                        </div>
                                                                    <?php } ?>
                                                                </td>

                                                                <td><?php foreach ($taxes as $tax) { ?>
                                                                        <div class="d-flex"><span><?= $tax['title'] ?></span>
                                                                            <span>-</span>
                                                                            <?php $total_tax += ($price_without_tax * $tax['percentage']) / 100 ?>
                                                                            <span><?= number_format(($price_without_tax * $tax['percentage']) / 100, 2)  ?> </span>
                                                                        </div>
                                                                    <?php }
                                                                    ?>
                                                                    <div class="d-flex">
                                                                        <span><b><?= 'Total - ' . number_format($total_tax, 2)   ?></b></span>
                                                                    </div>
                                                                </td>
                                                                <td><?= $row['quantity'] ?><br></td>
                                                                <td><?= $settings['currency'] . ' ' . number_format($sub_total, 2); ?><br></td>
                                                                <td class="d-none"><?= $row['active_status'] ?><br></td>
                                                            </tr>
                                                    <?php $j++;
                                                        }
                                                    } ?>
                                                </tbody>
                                                <tbody>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th>Total</th>
                                                        <th> <?= $quantity ?>
                                                            <br>
                                                        </th>
                                                        <th> <?= $settings['currency'] . ' ' . number_format($final_sub_total, 2) ?><br></th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="row m-3">
                                                <div class="col-md-6 text-left">
                                                    <b>Payment Method : </b> <?= $order_detls[0]['payment_method'] ?>
                                                </div>
                                                <div class="col-md-6 text-right">
                                                    <div class="table-responsive">
                                                        <table class="table table-borderless">
                                                            <tbody>
                                                                <tr>
                                                                    <th>Total Order Price (
                                                                        <?= $settings['currency'] ?>)</th>
                                                                    <td>+
                                                                        <?= number_format($final_sub_total, 2) ?>
                                                                    </td>
                                                                </tr>
                                                                <?php if ($order_detls[0]['type'] != 'digital_product') { ?>
                                                                    <tr>
                                                                        <th>Delivery Charge (
                                                                            <?= $settings['currency'] ?>)</th>
                                                                        <td>+
                                                                            <?php $total += $order_caharges_data[0]['delivery_charge'];
                                                                            echo number_format($order_caharges_data[0]['delivery_charge'], 2); ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                
                                                                <tr class="d-none">
                                                                    <th>Wallet Used (
                                                                        <?= $settings['currency'] ?>)</th>
                                                                    <td><?php $total -= $order_detls[0]['wallet_balance'];
                                                                        echo  '- ' . number_format($order_detls[0]['wallet_balance'], 2); ?> </td>
                                                                </tr>
                                                                <?php
                                                                if (isset($promo_code[0]['promo_code'])) { ?>
                                                                    <tr>
                                                                        <th>Promo (
                                                                            <?= $promo_code[0]['promo_code'] ?>) Discount (
                                                                            <?= floatval($promo_code[0]['discount']); ?>
                                                                            <?= ($promo_code[0]['discount_type'] == 'percentage') ? '%' : ' '; ?> )
                                                                        </th>
                                                                        <td>-
                                                                            <?php
                                                                            echo $order_caharges_data[0]['promo_discount'];
                                                                            $total = $total - $order_caharges_data[0]['promo_discount'];
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php } ?>
                                                                <?php
                                                                if (isset($order_detls[0]['discount']) && $order_detls[0]['discount'] > 0 && $order_detls[0]['discount'] != NULL) { ?>
                                                                    <tr>
                                                                        <th>Special Discount
                                                                            <?= $settings['currency'] ?>(<?= $order_detls[0]['discount'] ?> %)</th>
                                                                        <td>-
                                                                            <?php echo $special_discount = round($total * $order_detls[0]['discount'] / 100, 2);
                                                                            $total = floatval($total - $special_discount);
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                }
                                                                ?>
                                                                <tr class="d-none">
                                                                    <th>Total Payable (
                                                                        <?= $settings['currency'] ?>)</th>
                                                                    <td>
                                                                        <?= $settings['currency'] . '  ' . $total ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Final Total (
                                                                        <?= $settings['currency'] ?>)</th>
                                                                    <td>
                                                                        <?php $final_total = $final_sub_total - $order_detls[0]['discount'] - $order_caharges_data[0]['promo_discount'] + $order_caharges_data[0]['delivery_charge']; ?>
                                                                        <?= $settings['currency'] . '  ' . number_format($final_total, 2) ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (isset($seller_data[0]['authorized_signature']) && !empty($seller_data[0]['authorized_signature'])) { ?>
                                                <div class="row m-3">
                                                    <div class="col-md-6"></div>
                                                    <div class="col-md-6 text-right">
                                                        <p><strong>For <?= ucfirst($seller_data[0]['store_name']); ?> :</strong></p>
                                                        <div class="h-25"><img src='<?= base_url($seller_data[0]['authorized_signature']) ?>' id="<?= $sellers[$i] ?>" class="float-right product-image"></div><br><br>
                                                        <p><strong>Authorized Signatory</strong></p>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="row m-3" id="section-not-to-print">
                                                <div class="col-xs-12">
                                                    <button type=' button' value='Print this page' onclick='{printDiv("<?= "invoice-" . $sellers[$i] ?>")};' class="btn btn-default"><i class="fa fa-print"></i> Print</button>
                                                    </div>
                                            </div>
                                        </div>
                                        <!-- /.col -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <!--/.card-->
                </div>
                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>