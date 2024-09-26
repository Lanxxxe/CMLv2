<div class="modal fade" id="addBrandsModal" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Add Paint Brands</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" id="insertBrands">
                    <!-- action="insertBrands.php" -->
                    <fieldset>
                        <div class="form-group">
                            <label for="brandName">Brand Name</label>
                            <input class="form-control" placeholder="ex. Boysen" name="brandName" type="text" required>
                        </div>
                        <div class="form-group">
                            <label for="trademark">Brand Trademark URL</label>
                            <input class="form-control"  placeholder="https://store.boysen.com.ph/images/boysenlogo2.png"  name="trademark" type="text" required>
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

<script>
    // Registration form submission
    document.getElementById('insertBrands').addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('insertBrands.php', {
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
                        $('#addBrandsModal').modal('hide'); // Assuming you are using Bootstrap modal
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