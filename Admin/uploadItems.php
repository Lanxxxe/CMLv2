<?php
include_once 'config.php';
?>

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

                        <p>Brand:</p>
                        <div class="form-group">
                            <select name="paint_brand_id" id="paintBrandSelect" class="form-control" required>
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
                        
                        <p class="mt-3">Type:</p>
                        <div class="form-group">
                            <select name="paint_type_id" id="paintTypeSelect" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
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
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" min="1" required>
                        </div>
                        <p>Expiration Date:</p>
                        <div class="form-group">
                            <input class="form-control" name="expiration_date"
                                type="date" required>
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

                        <p>Price:</p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price"
                                type="text">
                        </div>
                        <p>Quantity:</p>
                        <div class="form-group">
                            <input type="number" placeholder="Quantity" class="form-control" name="quantity" min="1" required>
                        </div>

                        <p>Brand:</p>
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
                        
                        <p class="mt-3">Type:</p>
                        <div class="form-group">
                            <select name="type_id" id="typeSelect" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
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

    $(document).ready(function() {
    $('#brandSelect').change(handleBrandChange);
    $('#paintBrandSelect').change(handlePaintBrandChange);

    document.querySelectorAll('input[name="quantity"]').forEach(input => {
        input.addEventListener('input', event => {
            const min = +input.getAttribute('min');
            if(+input.value < +min) {
                input.value = min;
            }
        });
    });

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
});
</script>
