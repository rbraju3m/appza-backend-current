<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.87.0">
    <title>Appza</title>
    <link href="{{ asset('assets/backend/css/bootstrap.min.css') }}" rel="stylesheet">
    <meta name="theme-color" content="#7952b3">
    <link rel="icon" href="{{ asset('Fav.svg') }}" type="image/x-icon"/>


    <style>
        html,body {height: 100%;}
        .global-container{height:100%;display: flex;align-items: center;justify-content: center;background-color: #f5f5f5;}
        form{padding-top: 10px;font-size: 14px;margin-top: 30px;}
        .card-title{ font-weight:300; }
        .btn{font-size: 14px;margin-top:20px;}
        .login-form{width:330px;margin:20px;}
        .sign-up{text-align:center;padding:20px 0 0;}
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/fontawesome.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" />
    <link href="{{ asset('assets/backend/css/login.css') }}" rel="stylesheet">
</head>
<body class="app">
<div class="global-container">
    <div class="card login-form">
        <div class="card-body">

            <h3 class="card-title text-center">
                <img class="card-title text-center" width="50%" src="{{asset('Appza.png')}}" alt="">
            </h3>
            <h3 class="card-title text-center">Log in to Appza.</h3>
            <div class="card-text">                <form method="POST" action="{{ route('login') }}">
                @csrf
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-sm @error('email') is-invalid @enderror" id="exampleInputEmail1" aria-describedby="emailHelp" >
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <a href="{{route('password.request')}}" style="float:right;font-size:12px;">Forgot password?</a>
                        <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" id="exampleInputPassword1" >
                    </div>

                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
                    </div>

                    <div class="sign-up">
                        Don't have an account? <a href="#">Create One</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/backend/js/login.js') }}"></script>

</body>
</html>

