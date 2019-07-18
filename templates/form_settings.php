<link rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
    crossorigin="anonymous">
    <?php
if ($_GET['message']):
?>
        <div class="notice notice-success">
            <strong>
                <?php show_message(str_replace('-', ' ', $_GET['message']))?>
                </strong>
            </div>
            <?php
endif;
?>
<div class="wrap">

                <form method="post">
                    <h4>Configuración integración con Mercado Libre</h4>
                    <ol>
                    <li>Ingrese el token provisto por TechBox.</li>
                    <li>Ingrese las credenciales provistas por Mercado libre al crear una app en
                    https://developers.mercadolibre.com.pe/apps.</li>
                    </ol>

                    <img src="<?php echo $url_img ?>">
                    <hr>
                    <div class="form-group row">
                        <label class="col-sm-2
                            col-form-label">
                            <span class="dashicons dashicons-warning"
                                data-toggle="tooltip" data-placement="right"
                                title="Token
                                provisto por TechBox"></span>
                            techbox token</label>
                        <div class="col-sm-10">
                            <input name="techbox_token" class="form-control"
                                value="<?php echo $setting->techbox_token ?>">

                        </div>
                    </div>

                    <div class="form-group row">
                        <label
                            class="col-sm-2 col-form-label">
                            <span class="dashicons dashicons-warning"
                                data-toggle="tooltip" data-placement="right"
                                title="App id provisto por Mercado Libre"></span>
                            ML app id</label>
                        <div class="col-sm-10">
                            <input name="meli_app_id" class="form-control"
                                value="<?php
echo $setting->meli_app_id ?>">

                        </div>

                    </div>

                    <div class="form-group row">
                        <label
                            class="col-sm-2 col-form-label">
                            <span class="dashicons dashicons-warning"
                                data-toggle="tooltip" data-placement="right"
                                title="Secret
                                Key provisto por Mercado Libre"></span>
                            ML secret key</label>
                        <div class="col-sm-10">
                            <input name="meli_secret_key" class="form-control"
                                value="<?php
echo $setting->meli_secret_key ?>">

                        </div>

                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2
                            col-form-label"><span class="dashicons
                                dashicons-warning"
                                data-toggle="tooltip"
                                data-placement="right"
                                title="País en la cual se encuentra su tienda de Mercado Libre"></span>País</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="meli_site_id">
                                <option value="">Seleccione</option>
                                <?php
foreach ($siteIds as $siteId => $pais):
?>
                                    <option value="<?php echo $siteId ?>"
                                    <?php echo $siteId == $setting->meli_site_id ? 'selected' : '' ?>><?php
echo $pais ?>
</option>
                                        <?php
endforeach;
?>
                                        </select>
                                    </div>
                                </div>

                                <?=submit_button()?>
                                </form>
                                <hr>
                                <?php if ($setting->meli_app_id && $setting->meli_secret_key && $setting->meli_site_id): ?>

 <?php
echo $link_integration;
endif; ?>
                            </div>
                            <script
                                src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
                                integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
                                crossorigin="anonymous"></script>
                            <script
                                src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                                integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
                                crossorigin="anonymous"></script>
                                <script src="https://kit.fontawesome.com/f403a39c1d.js"></script>