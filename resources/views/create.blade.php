<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add New Store</title>
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0">Add a New Store</h4>
            </div>

            <div class="card-body">
                <form>
                    <h5 class="mb-3 text-success">Basic Information</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Store Name</label>
                            <input type="text" class="form-control" placeholder="Enter the store name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Branch Number</label>
                            <input type="text" class="form-control" placeholder="Enter branch number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <input type="text" class="form-control" placeholder="Enter the province">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" placeholder="Enter the city">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Detailed Address</label>
                            <input type="text" class="form-control" placeholder="Enter the detailed address">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" placeholder="09xxxxxxxx">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="example@email.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Store Status</label>
                            <select class="form-select">
                                <option selected disabled>Choose the status</option>
                                <option>Active</option>
                                <option>Stopped</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Opening Date</label>
                            <input type="date" class="form-control">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3 text-success">Administrative Information</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Responsible Manager</label>
                            <input type="text" class="form-control" placeholder="Manager name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Number of Employees</label>
                            <input type="number" class="form-control" placeholder="Number of employees">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3 text-success">Files</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Store Logo</label>
                            <input type="file" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Store Brochure (PDF)</label>
                            <input type="file" class="form-control">
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-success px-5">Save Data</button>
                        <a href="{{ route('dashboard', [], false) }}" class="btn btn-secondary px-5">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
