<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Upload Paint Products</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" id="uploadpaints">
                    <!-- action="additems.php" -->
                    <fieldset>
                        <p>Color Name:</p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Item Color" name="item_name" type="text"
                                required>
                        </div>
                        <p>Brand Name:</p>
                        <div class="form-group">
                            <!-- <input class="form-control" placeholder="Brand Name" name="brand_name" type="text"
                                required> -->
                            <select name="brand_name" id="brand_name" class="form-control">
                                <option value="" disabled selected>Select an option</option>
                                <?php 
                                include 'config.php';

                                $query = "SELECT * FROM brands";

                                $statement = $DB_con->prepare($query);
                                if($statement->execute()){
                                    $contents = $statement->fetchAll();

                                    foreach($contents as $brand){
                                        ?>
                                            <option value="<?php echo htmlspecialchars($brand['brand_name']) ?>"><?php echo htmlspecialchars($brand['brand_name']) ?></option>
                                        <?php
                                    }
                                }
                                
                                ?>
                            </select>
                        </div>
                        <p>Gallon/Liter:</p>
                        <div class="form-group">
                            <select name="gl" id="gl" class="form-control">
                                <option value="" disabled selected>Select an option</option>
                                <option value="Gallon">Gallon</option>
                                <option value="Liter">Liter</option>
                            </select>
                        </div>
                        
                        <p>Price:</p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price"
                                type="text">
                        </div>
                        <p>Quantity:</p>
                        <div class="form-group">
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" required>
                        </div>
                        <p>Expiration Date:</p>
                        <div class="form-group">
                            <input class="form-control" name="expiration_date"
                                type="date" required>
                        </div>
                        
                        <p>Type:</p>
                        <div class="form-group">
                            <select name="type" id="" class="form-control">
                                <option value="" disabled selected>Select type</option>
                                <option value="Gloss">Gloss</option>
                                <option value="Acrytex">Acrytex</option>
                                <option value="Oil Paint">Oil Paint</option>
                                <option value="Enamel">Enamel</option>
                                <option value="QDE">QDE</option>
                                <option value="Primer">Primer</option>
                                <option value="Acrylic">Acrylic</option>
                                <option value="Flat/Matte">Flat/Matte</option>
                                <option value="Latex">Latex</option>
                                <option value="Brush">Brush</option>
                                <option value="Alkyds">Alkyds</option>
                                <option value="Tape">Tape</option>
                            </select>
                        </div>
                        <p>Choose Image:</p>
                        <div class="form-group">
                            <input class="form-control" type="file" name="item_image" accept="image/*" required />
                        </div>
                    </fieldset>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-md" name="item_save">Save</button>
                        <button type="button" class="btn btn-danger btn-md" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadItems" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Upload Items</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" id="uploadItemsForm">
                    <!-- action="additems.php" -->
                    <fieldset>
                        <p>Item:</p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Name of Item" name="item_name" type="text" required>
                        </div>
                        <!-- <p>Brand Name:</p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Name of Item" name="item_name" type="text" required>
                        </div> -->
                        <p>Price:</p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price"
                                type="text">
                        </div>
                        <p>Quantity:</p>
                        <div class="form-group">
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" required>
                        </div>
                        
                        <p>Type:</p>
                        <div class="form-group">
                            <select name="type" id="" class="form-control">
                                <option value="Brush">Brush</option>
                                <option value="Tape">Tape</option>
                            </select>
                        </div>
                        <p>Choose Image:</p>
                        <div class="form-group">
                            <input class="form-control" type="file" name="item_image" accept="image/*" required />
                        </div>
                    </fieldset>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-md" name="item_save" type="submit">Save</button>
                        <button type="button" class="btn btn-danger btn-md" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Registration form submission
    document.getElementById('uploadpaints').addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('addpaints.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Close the modal or perform other actions
                        $('#uploadModal').modal('hide'); // Assuming you are using Bootstrap modal
                        // Additional actions if needed
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    document.querySelector('#uploadItemsForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('additems.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Close the modal or perform other actions
                        $('#uploadItems').modal('hide'); // Assuming you are using Bootstrap modal
                        // Additional actions if needed
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
</script>