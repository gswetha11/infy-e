<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage Brand</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Brands</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="card content-area p-4">
                        <div class="card-innr" id="list_view_html">
                            <div class="card-head">
                                <h4 class="card-title">Brands</h4>
                            </div>
                            <div class="gaps-1-5x"></div>
                            <table class='table-striped' id='brand_table' data-toggle="table" data-url="<?= $base_brand_url ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]' data-export-options='{
                        "fileName": "brand-list",
                        "ignoreColumn": ["state"]
                        }' data-query-params="brand_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true" data-visible='true'>ID</th>
                                        <th data-field="name" data-sortable="false" data-align='center'>Name</th>
                                        <th data-field="image" data-sortable="true" data-align='center'>Image</th>
                                        <th data-field="status" data-sortable="true" data-align='center'>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div><!-- .card-innr -->
                    </div><!-- .card-innr -->
                </div><!-- .card -->
            </div>
        </div>
        <!-- /.row -->
</div><!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>
