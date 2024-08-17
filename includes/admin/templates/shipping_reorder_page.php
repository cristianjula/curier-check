<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$avail_shipping_methods = curiero_get_available_shipping_methods();
$sorted_shipping_methods = [];
$selected_order = get_option('curiero_shipping_methods_order', '');
if (!empty($selected_order)) {
    foreach (explode(',', $selected_order) as $method) {
        if (isset($avail_shipping_methods[$method])) {
            $sorted_shipping_methods[$method] = $avail_shipping_methods[$method];
            unset($avail_shipping_methods[$method]);
        }
    }
}
$sorted_shipping_methods = array_merge($sorted_shipping_methods, $avail_shipping_methods);
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<style>
    .list-group-item{padding:6px 10px;background:white;margin:5px 0;border:1px solid lightgray;cursor:pointer}
    .curiero-background-class{background:var(--wc-blue);opacity:.5;color:white;border-color:white}
    .form-table td{padding:1rem 1.5rem!important}
    .form-table tfoot td{padding:1.5rem!important}
    .form-table .woocommerce-help-tip::after{line-height: inherit;}
</style>

<div class="wrap">
    <h1>CurieRO - Ordoneaza metodele de livrare</h1>
    <br>
    <form action="options.php" method="post">
        <?php
            settings_fields('curiero_shipping_methods_order');
            do_settings_sections('curiero_shipping_methods_order');
        ?>
        <input type="hidden" name="curiero_shipping_methods_order" value="<?= $selected_order; ?>">
        <table class="form-table wp-list-table widefat striped" style="max-width: 850px;">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name" style="width: 50%">
                        Ordinea metodelor de livrare
                        <?= wc_help_tip('Pentru a reordona lista de curieri din checkout tineti apasat si trageti de element in sus/jos.'); ?>
                    </th>
                    <td style="text-align:right;font-weight:600">
                        <span>
                            Status:
                        </span>
                        <?php if (empty($selected_order)) { ?>
                        <span style="color: red;"> Ordine implicita </span>
                        <?php } else { ?>
                        <span style="color: green;"> Ordine personalizata </span>
                        <?php } ?>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">
                        <div id="shipping-methods-list">
                            <?php foreach ($sorted_shipping_methods as $shipping_method) : ?>
                            <div class="list-group-item" data-id="<?= $shipping_method->id; ?>"><?= trim($shipping_method->get_title()) ?: trim($shipping_method->get_method_title()); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span style="font-weight:600">
                            <span class="dashicons dashicons-info"></span> In aceasta lista se regasesc si metodele de livrare implicite WooCommerce inactive.
                        </span>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?php submit_button('Salveaza modificarile'); ?>
                        <p><input type="button" class="button button-secondary resetOrder" value="Reseteaza ordinea"></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <p>© Copyright <script>document.write(new Date().getFullYear());</script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" integrity="sha256-ymhDBwPE9ZYOkHNYZ8bpTSm1o943EH2BAOWjAQB+nm4=" crossorigin="anonymous"></script>

<script>
    const orderInput = document.querySelector('input[name=curiero_shipping_methods_order]');
    const sortable = new Sortable(document.getElementById('shipping-methods-list'), {
        animation: 350,
        ghostClass: 'curiero-background-class',
        onChange: () => orderInput.value = sortable.toArray().toString()
    });

    document.querySelector('.resetOrder').addEventListener("click", function() {
        orderInput.value = '';
        document.getElementById('submit').click();
    });

    document.addEventListener("load", (event) => {
        orderInput.value = sortable.toArray().toString();
    });
</script>

<?php
