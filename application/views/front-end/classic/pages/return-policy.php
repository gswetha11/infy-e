<section class="breadcrumb-title-bar colored-breadcrumb deeplink_wrapper">
    <div class="main-content responsive-breadcrumb">
        <h2><?= !empty($this->lang->line('return_policy')) ? str_replace('\\', '', $this->lang->line('return_policy')) : 'Return Policy' ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><?= !empty($this->lang->line('home')) ? str_replace('\\', '', $this->lang->line('home')) : 'Home' ?></a></li>
                <li class="breadcrumb-item active"><?= !empty($this->lang->line('return_policy')) ? str_replace('\\', '', $this->lang->line('return_policy')) : 'Return Policy' ?></li>
            </ol>
        </nav>
    </div>
</section>
<section class="main-content py-5 my-4 bg-white">
    <div class="text-center">
        <h1 class="h4"><?= !empty($this->lang->line('return_policy')) ? str_replace('\\', '', $this->lang->line('return_policy')) : 'Return Policy' ?></h1>
    </div>

    <div class="text-justify">
        <?= $return_policy ?>
    </div>
</section>