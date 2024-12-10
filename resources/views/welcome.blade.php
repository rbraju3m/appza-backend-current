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
        .global-container{height: 100%;display: flex;align-items: center;justify-content: center;}
        form{padding-top: 10px;font-size: 14px;margin-top: 30px;}
        .card-title{ font-weight:300; }
        .btn{font-size: 14px;margin-top:20px;}
        .login-form{width:50%;margin:20px;}
        .sign-up{text-align:center;padding:20px 0 0;}
        .welcome-btn {
            color: #fff;
            background-color: #FF9209;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background .3s;
        }

        .welcome-btn:hover {
            background-color: #FF9209;
        }
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
                <img class="card-title text-center" width="20%" src="{{asset('Appza.png')}}" alt="">
            </h3>
            <h3 class="card-title text-center" style="margin-top: 10px !important;">Welcome to Appza Wordpress Plugin.</h3>
            <h6 class="card-title text-center" style="margin-top: 30px !important;">
                <a href="https://lazycoders.co/" class="welcome-btn" >Learn More</a>
            </h6>
        </div>
    </div>
</div>

</body>
</html>

