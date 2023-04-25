<?php
 
if(isset($_GET['status'])){
    if($_GET['status'] == 'success'){
?>
         <div class="alert alert-success" role="alert">
             Settings saved successfully
         </div>   
<?php            
    }else{
?>
         <div class="alert alert-danger" role="alert">
             Server Error, Try Again
         </div>

<?php 
    }

 }

?>

<div class="row">
    <div class="col-md-6">
        <div class="card " style="border-radius:10px">
            <div class="card-header">
                <h3>Llaves del API</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <form id="recurrente-settings-form" method="post">

                        <?php wp_nonce_field( ); ?> 
                        <div class="form-group">
                            <label for="public_key">Clave Publica</label>
                            <input type="text" name="public_key" id="public_key" class="form-control" value="<?= get_option("recurrente_public_key", '') ?>">
                        </div>
                        
                        <div class="form-group" style="margin-top:10px">
                            <label for="secret_key">Clave Secreta</label>
                            <input type="text" name="secret_key" id="secret_key" class="form-control" value="<?= get_option("recurrente_secret_key", '') ?>">
                        </div>
                        <input type="hidden" name="save_recurrente_credentials" value="submit">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card " style="border-radius:10px">
            <div class="card-header">
                <div class="card-title">
                    <h3>Como lalal asdas ??? </h3>
                </div>
            </div>
            <hr>
            <div class="card-body">
                <p style="color:grey">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce id aliquet sem. In et mattis magna, et sagittis dolor. Duis sed metus orci. Aenean vestibulum arcu sit amet odio elementum, sed lacinia risus fermentum. Cras enim nibh, posuere at eleifend sed, sollicitudin sit amet turpis. Nunc libero velit, mollis eu libero eu, egestas bibendum velit. Nam eu aliquam odio. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Etiam vitae bibendum justo. In venenatis, purus condimentum fringilla aliquet, leo odio porta mauris, volutpat finibus risus nisl sit amet purus. Phasellus luctus lacinia ligula, eu hendrerit tortor convallis sit amet. Aenean semper odio quis elit fermentum ullamcorper quis vitae elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Maecenas euismod odio euismod felis commodo placerat. Donec id egestas orci. </p>
            </div>
        </div>
    </div>
</div>