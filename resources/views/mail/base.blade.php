<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600&family=Roboto+Mono&display=swap" rel="stylesheet">

    <style>
      body{
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        line-height: 22px;
        background-color: #EEEEEE;
        margin: 0px;
        padding: 0px;
        font-weight: 400;
      }

      h1{
        font-size: 20px;
      }

      h2{
        font-weight: 600;
        font-size: 18px;
        margin-bottom: 0px;
        padding-bottom: 2px;
      }

      h3{
        margin: 0px;
        padding-top: 2px;
        font-size: 15px;
      }

      .icon{
        width: 80px;
        height: 80px;
        display: block;
        margin-top: 40px;
        margin-bottom: 10px;
        position: absolute;
      }

      .monospace{
        font-family: "Roboto Mono", monospace;
      }

      .icon-side-text{
        padding-top: 20px;
        padding-left: 90px;
        display: block;
        box-sizing: border-box;
      }

      .center{
        display: block;
        margin: auto;
        text-align: center;
      }

      .container, .footer{
        max-width: 600px;
        margin: auto;
        display: block;
        box-sizing: border-box;
      }

      .container{
        padding: 30px;
        background-color: #FFFFFF;
      }

      .footer{
        margin-top: 20px;
        margin-bottom: 20px;
        font-size: 14px;
        padding: 10px;
        color: rgba(80,80,80);
      }

      .footer a{
        color: rgba(80,80,80);
      }

      @media(max-width: 600px){
        .container, .footer{
          max-width: 100%;
        }

        .icon-side-text{
          padding: 0px;
        }

        .icon{
          position: relative;
        }
      }

      .button{
        margin-top: 10px;
        margin-bottom: 10px;
        box-sizing: border-box;
        border-radius: 4px;
        text-transform: uppercase;
        color: black;
        padding: 10px;
        text-align: center;
        width: 100%;
        background-color: #EEEEEE;
        display: block;
        text-decoration: none;
        line-height: 1em;
        box-shadow: 0 3px 1px -2px rgba(0,0,0,.2),0 2px 2px 0 rgba(0,0,0,.14),0 1px 5px 0 rgba(0,0,0,.12);
      }

      .primary{
        background: linear-gradient(to top, #308efc, #44c4fb);
        color: white;
      }

      .item-cover{
        max-height: 350px;
        max-width: 95%;
        display: block;
        margin: auto;
      }

      .item-title{
        margin-top: 20px;
        text-align: center;
      }

      .item-price{
        margin-top: 20px;
        margin-bottom: 15px;
        text-align: center;
      }

      .item-authors{
        color: rgba(51,51,51);
        font-weight: normal;
        text-align: center;
      }

      .logo{
        font-size: 17px;
        display: block;
        text-decoration: none;
        color: black;
        margin: auto;
        margin-bottom: 25px;
        min-width: 300px;
        text-align: center;
      }

      .logo img{
        display: block;
        margin: auto;
        width: 35px;
        height: 35px;
        margin-bottom: 5px;
      }

      .uid-large{
        font-size: 50px;
        font-family: "Roboto Mono", monospace;
        font-weight: 600;
        text-align: center;
        display: block;
        line-height: 50px;
        margin-top: 10px;
        margin-bottom: 40px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <a href="https://schoolhub.ch" class="logo">
        <img src="https://schoolhub.ch/images/logo.svg" alt="Logo">Bookstore
      </a>
      @yield('body')
    </div>
    <div class="footer">
      Diese Mail wurde automatisch durch das Bookstore-System der SO Gymnasium Liestal versendet.<br>
      Gymnasium Liestal - Friedensstrasse 20 - 4410 Liestal - Schweiz - <a href="https://www.gymliestal.ch">www.gymliestal.ch</a>
    </div>
  </body>
</html>
