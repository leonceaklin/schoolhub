<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600&family=Roboto+Mono&display=swap" rel="stylesheet">

    <style>
      @php echo file_get_contents(public_path()."/css/mail.css"); @endphp
    </style>
  </head>
  <body>
    <div class="container">
      <a href="{{ url('/') }}" class="logo">
        <img src="{{ url('/images/logo.svg') }}" alt="Logo">Bookstore
      </a>
      @yield('body')
    </div>
    <div class="footer">
      Diese Mail wurde automatisch durch das Bookstore-System der SO Gymnasium Liestal versendet.<br>
      Gymnasium Liestal - Friedensstrasse 20 - 4410 Liestal - Schweiz - <a href="https://www.gymliestal.ch">www.gymliestal.ch</a>
    </div>
  </body>
</html>
