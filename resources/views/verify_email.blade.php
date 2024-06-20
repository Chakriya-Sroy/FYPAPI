<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PR Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <div class="container mt-5">
       
        <div class="col-lg-4 p-4 mx-auto">
            @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif
            <div class="ms-auto me-auto mb-3" style="width:100px;">
                <img src="{{ asset('images/logo.png') }}" alt="" class="img-fluid">
            </div>
            <h6 class="text-center fw-bold mb-3" style="color: #179C1D">Click To Verify</h6>
            <form action="{{ route('update-verify-email') }}" class="w-100" enctype="multipart/form-data" method="POST">
                <input type="hidden" name="id" value="{{ $user->id }}">
               <input type="submit" value="verify" class="btn text-white mt-3 mb-3 form-control shadow-sm " style="background:#179C1D">
             </form>
        </div>   
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>