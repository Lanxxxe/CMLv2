<?php
include_once 'config.php';
?>

<!-- Paint Products -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Add Items</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" id="uploadpaints">
                    <!-- action="additems.php" -->
                    <fieldset>
                        <p>Color Name<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Item Color" name="item_name" type="text"
                                required>
                        </div>

                        <p>Brand<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="paint_brand_id" id="paintBrandSelect" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php
                                // Fetch all brands from database
                                $stmt = $DB_con->prepare("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
                                $stmt->execute();
                                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($brands as $brand) {
                                    ?>
                                    <option value='<?php echo htmlspecialchars($brand['brand_id']) ?>'>
                                        <?php echo htmlspecialchars($brand['brand_name']) ?></option>";
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        
                        <p class="mt-3">Type<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="paint_type_id" id="paintTypeSelect" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
                            </select>
                        </div>

                        <p class="mt-3">Pallet<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="pallet" id="pallet" class="form-control" required>
                            <option value="">Select Pallet</option>
                                <?php 
                                    // Fetch all brands from database
                                    $stmt = $DB_con->prepare("SELECT * FROM pallets ORDER BY code");
                                    $stmt->execute();
                                    $pallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($pallets as $pallet) {
                                        ?>
                                            <option value="<?php echo htmlspecialchars($pallet['pallet_id']) ?>" style="display: flex; gap: 7px;">
                                                <?php echo htmlspecialchars($pallet['name']) ?> -
                                                <?php echo htmlspecialchars($pallet['code']) ?>
                                            </option>
                                        <?php
                                    }
                                ?>                                
                            </select>
                        </div>


                        <p>Gallon/Liter<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="gl" id="gl" class="form-control">
                                <option value="" disabled selected>Select an option</option>
                                <option value="Gallon">Gallon</option>
                                <option value="Liter">Liter</option>
                            </select>
                        </div>
                        
                        <p>Price<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price" min="1" type="number">
                        </div>
                        <p>Quantity<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" min="1" required>
                        </div>
                        <p>Expiration Date<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input class="form-control" name="expiration_date" 
                                type="date" required>
                        </div>
                        
                        <p>Choose Image<span style="color: red;">*</span></p>
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

<!-- Tools Products -->
<div class="modal fade" id="uploadItems" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Add Items</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" id="uploadItemsForm">
                    <!-- action="additems.php" -->
                    <fieldset>
                        <p>Item<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Name of Item" name="item_name" type="text" required>
                        </div>

                        <p>Price<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price"
                                type="text">
                        </div>
                        <p>Quantity<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" min="1" required>
                        </div>

                        <p>Brand<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="brand_id" id="brandSelect" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php
                                // Fetch all brands from database
                                $stmt = $DB_con->prepare("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
                                $stmt->execute();
                                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($brands as $brand) {
                                    echo "<option value='" . htmlspecialchars($brand['brand_id']) . "'>" . 
                                        htmlspecialchars($brand['brand_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <p class="mt-3">Type<span style="color: red;">*</span></p>
                        <div class="form-group">
                            <select name="type_id" id="typeSelect" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
                            </select>
                        </div>


                        <p>Choose Image<span style="color: red;">*</span></p>
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
                        location.reload();
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
                        location.reload();
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

    document.querySelectorAll('input[name="quantity"]').forEach(input => {
        input.addEventListener('input', event => {
            const min = +input.getAttribute('min');
            if(+input.value < +min) {
                input.value = min;
            }
        });
    });

    $(document).ready(function() {
        $('#brandSelect').change(handleBrandChange);
        $('#paintBrandSelect').change(handlePaintBrandChange);


        function handleBrandChange() {
            var brandId = $(this).val();
            var typeSelect = $('#typeSelect');
            updateTypeOptions(typeSelect, brandId);
        }

        function handlePaintBrandChange() {
            var brandId = $(this).val();
            var typeSelect = $('#paintTypeSelect');
            updateTypeOptions(typeSelect, brandId);
        }

        function updateTypeOptions(typeSelect, brandId) {
            if (brandId) {
                typeSelect.prop('disabled', false);
                
                $.ajax({
                    url: 'get_types.php',
                    method: 'GET',
                    data: { brand_id: brandId },
                    dataType: 'json'
                })
                .done(function(types) {
                    if (types.error) {
                        console.error('Server Error:', types.error);
                        alert('Error fetching types. Please try again.');
                        return;
                    }
                    
                    typeSelect.empty();
                    typeSelect.append('<option value="">Select Type</option>');
                    
                    types.forEach(function(type) {
                        typeSelect.append(
                            $('<option></option>')
                                .val(type.type_id)
                                .text(type.type_name)
                        );
                    });
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    alert('Error fetching types. Please try again.');
                });
            } else {
                typeSelect.prop('disabled', true);
                typeSelect.empty();
                typeSelect.append('<option value="">Select Brand First</option>');
            }
        }

        document.querySelectorAll('input[name="expiration_date"]').forEach(input => {
            const currentDate = new Date();
            const minDate = new Date(currentDate.setFullYear(currentDate.getFullYear() + 2));
            
            // Format the minDate to YYYY-MM-DD
            const formattedMinDate = minDate.toISOString().split('T')[0];
            input.setAttribute('min', formattedMinDate); // Set the min attribute for the input

            input.addEventListener('input', event => {
                if (input.value < formattedMinDate) {
                    input.value = formattedMinDate;
                }
            })
        });
    });
</script>
