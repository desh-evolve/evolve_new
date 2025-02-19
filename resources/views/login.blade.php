<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name') }} {{ __('Time and Attendance - Secure Login') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('global.css.php') }}">
    
    <script>
        function bookmarkSite(title, url) {
            if (window.sidebar) {
                window.sidebar.addPanel(title, url, "");
            } else if (window.opera && window.print) {
                let elem = document.createElement('a');
                elem.setAttribute('href', url);
                elem.setAttribute('title', title);
                elem.setAttribute('rel', 'sidebar');
                elem.click();
            } else if (document.all) {
                window.external.AddFavorite(url, title);
            }
        }
    </script>
    
    <style>
        body {
            background: url('{{ asset('images/payroll_bg1.png') }}') !important;
            color: #000;
            margin: 0;
            padding: 0;
            font-family: Verdana, sans-serif;
            font-size: 11px;
        }
        img {
            border: 0;
        }
    </style>
</head>
<body onload="document.login.user_name.focus()">
    <div id="container">
        <div id="contentBoxOne">
            <img src="{{ asset('images/hrm_logo.png') }}" alt="Secure Login" class="imgLock">
        </div>
        <div id="rowContentLogin">
            <form method="post" name="login" action="/authenticate">
                @csrf
                <div id="contentBoxLogin">
                    <div class="textTitle2">
                        <img src="{{ asset('images/login_top.png') }}" alt="Secure Login" class="imgLock">
                    </div>
                    <div id="contentBoxTwo">
                        @if(session('password_reset'))
                            <div id="rowWarning" valign="center">
                                <br>
                                <b>{{ __('Your password has been changed successfully, you may now login.') }}</b>
                                <br>&nbsp;
                            </div>
                        @endif
                        <div class="row">
                            <div class="cellLeft">{{ __('LOGIN') }}</div>
                            <div class="cellRight"><input type="text" name="user_name" value="{{ old('user_name', $user_name ?? '') }}" size="18"></div>
                        </div>
                        <div class="row">
                            <div class="cellLeft">{{ __('PASSWORD') }}</div>
                            <div class="cellRight"><input type="password" name="password" size="18"></div>
                        </div>
                        <div class="row">
                            <div class="cellLeft">{{ __('LANGUAGE') }}</div>
                            <div class="cellRight">
                                <select name="language">
                                    @foreach($language_options ?? [] as $key => $value)
                                        <option value="{{ $key }}" {{ (isset($language) && $language == $key) ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="submit" class="btnLogin" name="action:submit" value="{{ __('Login') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
    @include('footer')
</body>
</html>
