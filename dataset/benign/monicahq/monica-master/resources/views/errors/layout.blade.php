<!DOCTYPE html>
<html lang="{{ \App::getLocale() }}" dir="{{ htmldir() }}">
    <head>
        <base href="{{ url('/') }}/" />
        <title>@yield('title')</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
                font-weight: bold;
            }

            .message {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">
                    @yield('message')
                </div>
                <div>
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
