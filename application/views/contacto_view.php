<div class="section section-breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h1>Pongase en Contacto con Nosotros</h1>
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <!-- Map -->
                <div id="map" style="height:350px">

                </div>
                <!-- End Map -->
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-6">
                <h3><?= $contacto['nombre'] ?></h3>
                <div class="row">
                    <br>
                    <div class="col-md-offset-1 col-md-4">
                        <p class="text-center">
                            <img src="<?= base_url('assets/img/logo/'.$contacto['logo']) ?>" class="img-responsive" style="width:100%">
                        </p>
                    </div>
                    <div class="col-md-7">
                        <p>
                            <strong>Domicilio: </strong><br> <?= $contacto['domicilio'] ?>
                        </p>
                        <p>
                            <strong>Telefonos: </strong><br> <?= $contacto['nombre'] ?>
                        </p>
                        <p>
                            <strong>Email: </strong><br> <?= $contacto['email'] ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <!-- Contact Form -->
                <h3>Dejenos su Mensaje...</h3>
                <div class="contact-form-wrapper">
                    <form id="form_contacto" class="form-horizontal" role="form">
                         <div class="form-group">
                            <label for="Name" class="col-sm-3 control-label"><b>Nombre:</b></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="nombre" name="nombre" type="text" placeholder="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact-email" class="col-sm-3 control-label"><b>Email:</b></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="email" name="email" type="text" placeholder="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact-message" class="col-sm-3 control-label"><b>Asunto:</b></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="asunto" name="asunto" type="text" placeholder="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact-message" class="col-sm-3 control-label"><b>Mensaje:</b></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="5" id="mensaje" name="mensaje" required></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" id="btn_contacto" class="btn btn-info pull-right">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- End Contact Info -->
            </div>
        </div>
    </div>
</div>