
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#000000">
    <meta name="keywords" content="Login Form">
    <meta name="description" content="">
    <link rel="apple-touch-icon" href="/logo192.png">
    <link rel="manifest" href="/manifest.json">
    <title>Login - EMR</title>
    <link href="{!! asset('vendors/bootstrap/css/bootstrap.min.css')!!}" rel="stylesheet">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,700&amp;display=swap" rel="stylesheet">
    <link href="{!! asset('static/css/main.c039d16e.chunk.css') !!}" rel="stylesheet">
    <style data-emotion="css"></style>
    <script type="text/javascript" async="" charset="utf-8" src="{!! asset('build/js/app-85bea3e584.js')!!}"></script>
    <script charset="utf-8" src="{!! asset('static/js/0.c12290f3.chunk.js')!!}"></script>
    <script charset="utf-8" src="{!! asset('static/js/1.84acd8ee.chunk.js')!!}"></script>
    <script charset="utf-8" src="{!! asset('static/js/2.6ee65657.chunk.js')!!}"></script>
    <script charset="utf-8" src="{!! asset('static/js/3.205abf9b.chunk.js')!!}"></script>
    <script charset="utf-8" src="{!! asset('static/js/24.aca7c255.chunk.js')!!}"></script>
    <style type="text/css">
        sociomile-chat .btn-new-chat.sociomile-btn,
        [riot-tag="sociomile-chat"] .btn-new-chat.sociomile-btn,
        [data-is="sociomile-chat"] .btn-new-chat.sociomile-btn {
            width: max-content !important;
        }

        sociomile-chat .sociomile-container,
        [riot-tag="sociomile-chat"] .sociomile-container,
        [data-is="sociomile-chat"] .sociomile-container {
            -webkit-border-top-left-radius: 4px;
            -webkit-border-top-right-radius: 4px;
            -moz-border-radius-topleft: 4px;
            -moz-border-radius-topright: 4px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        sociomile-chat .chat-on .sociomile-title,
        [riot-tag="sociomile-chat"] .chat-on .sociomile-title,
        [data-is="sociomile-chat"] .chat-on .sociomile-title {
            display: none;
        }

        sociomile-chat .chat-on .sociomile-agent,
        [riot-tag="sociomile-chat"] .chat-on .sociomile-agent,
        [data-is="sociomile-chat"] .chat-on .sociomile-agent {
            float: left;
            display: flex;
            align-items: center;
            width: 100%;
            height: auto !important;
            min-height: 64px;
            padding: 20px !important;
        }

        sociomile-chat .chat-on .sociomile-top,
        [riot-tag="sociomile-chat"] .chat-on .sociomile-top,
        [data-is="sociomile-chat"] .chat-on .sociomile-top {
            width: auto !important;
            float: right;
            position: absolute;
            right: 0;
        }

        sociomile-chat .adjust-text-cs,
        [riot-tag="sociomile-chat"] .adjust-text-cs,
        [data-is="sociomile-chat"] .adjust-text-cs {
            margin-top: 12px !important;
        }

        sociomile-chat .auto-height,
        [riot-tag="sociomile-chat"] .auto-height,
        [data-is="sociomile-chat"] .auto-height {
            height: auto !important;
        }

        sociomile-chat .p-new-chat,
        [riot-tag="sociomile-chat"] .p-new-chat,
        [data-is="sociomile-chat"] .p-new-chat {
            margin-top: 10px;
            text-align: center;
        }

        sociomile-chat .btn-new-chat,
        [riot-tag="sociomile-chat"] .btn-new-chat,
        [data-is="sociomile-chat"] .btn-new-chat {
            background-color: #3a96d2;
            border-radius: 4px;
            color: #ffffff;
        }

        sociomile-chat .sociomile-bottom,
        [riot-tag="sociomile-chat"] .sociomile-bottom,
        [data-is="sociomile-chat"] .sociomile-bottom {
            border-top: 1px solid #cccccc4a;
        }

        sociomile-chat .white-space-pre,
        [riot-tag="sociomile-chat"] .white-space-pre,
        [data-is="sociomile-chat"] .white-space-pre {
            white-space: pre-wrap;
        }

        sociomile-chat .botside,
        [riot-tag="sociomile-chat"] .botside,
        [data-is="sociomile-chat"] .botside {
            text-align: left !important;
        }

        sociomile-chat .sociomile-item-body .right a,
        [riot-tag="sociomile-chat"] .sociomile-item-body .right a,
        [data-is="sociomile-chat"] .sociomile-item-body .right a {
            color: #ffffff;
        }

        sociomile-chatbot .botloading,
        [riot-tag="sociomile-chatbot"] .botloading,
        [data-is="sociomile-chatbot"] .botloading {
            width: 100% !important;
            border-radius: 6px;
        }

        sociomile-chatbot .btn-chat,
        [riot-tag="sociomile-chatbot"] .btn-chat,
        [data-is="sociomile-chatbot"] .btn-chat {
            width: 100%;
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            border: 0;
            padding: 6px;
            color: #0070ff;
            text-align: center;
        }

        sociomile-chatbot .bsolid,
        [riot-tag="sociomile-chatbot"] .bsolid,
        [data-is="sociomile-chatbot"] .bsolid {
            border-bottom: solid 1px #ccc;
        }

        sociomile-chatbot .btop,
        [riot-tag="sociomile-chatbot"] .btop,
        [data-is="sociomile-chatbot"] .btop {
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        sociomile-chatbot .bbottom,
        [riot-tag="sociomile-chatbot"] .bbottom,
        [data-is="sociomile-chatbot"] .bbottom {
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }

        sociomile-chatbot ul.list-btn-action,
        [riot-tag="sociomile-chatbot"] ul.list-btn-action,
        [data-is="sociomile-chatbot"] ul.list-btn-action {
            list-style: none;
            padding: 4px;
            margin: 0;
        }

        sociomile-chatbot .sociomile-container,
        [riot-tag="sociomile-chatbot"] .sociomile-container,
        [data-is="sociomile-chatbot"] .sociomile-container {
            -webkit-border-top-left-radius: 4px;
            -webkit-border-top-right-radius: 4px;
            -moz-border-radius-topleft: 4px;
            -moz-border-radius-topright: 4px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        sociomile-chatbot .chat-on .sociomile-title,
        [riot-tag="sociomile-chatbot"] .chat-on .sociomile-title,
        [data-is="sociomile-chatbot"] .chat-on .sociomile-title {
            display: none;
        }

        sociomile-chatbot .chat-on .sociomile-agent,
        [riot-tag="sociomile-chatbot"] .chat-on .sociomile-agent,
        [data-is="sociomile-chatbot"] .chat-on .sociomile-agent {
            float: left;
            display: flex;
            align-items: center;
            width: 100%;
            height: auto !important;
            min-height: 64px;
            padding: 20px !important;
        }

        sociomile-chatbot .chat-on .sociomile-top,
        [riot-tag="sociomile-chatbot"] .chat-on .sociomile-top,
        [data-is="sociomile-chatbot"] .chat-on .sociomile-top {
            width: auto !important;
            float: right;
            position: absolute;
            right: 0;
        }

        sociomile-chatbot .adjust-text-cs,
        [riot-tag="sociomile-chatbot"] .adjust-text-cs,
        [data-is="sociomile-chatbot"] .adjust-text-cs {
            margin-top: 12px !important;
        }

        sociomile-chatbot .auto-height,
        [riot-tag="sociomile-chatbot"] .auto-height,
        [data-is="sociomile-chatbot"] .auto-height {
            height: auto !important;
        }

        sociomile-chatbot .p-new-chat,
        [riot-tag="sociomile-chatbot"] .p-new-chat,
        [data-is="sociomile-chatbot"] .p-new-chat {
            margin-top: 10px;
            text-align: center;
        }

        sociomile-chatbot .btn-new-chat,
        [riot-tag="sociomile-chatbot"] .btn-new-chat,
        [data-is="sociomile-chatbot"] .btn-new-chat {
            background-color: #3a96d2;
            border-radius: 4px;
            color: #ffffff;
        }

        sociomile-chatbot .sociomile-bottom,
        [riot-tag="sociomile-chatbot"] .sociomile-bottom,
        [data-is="sociomile-chatbot"] .sociomile-bottom {
            border-top: 1px solid #cccccc4a;
        }

        sociomile-chatbot .typing-loader,
        [riot-tag="sociomile-chatbot"] .typing-loader,
        [data-is="sociomile-chatbot"] .typing-loader {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            animation: typing 1s linear infinite alternate;
            position: relative;
            left: -3rem;
            margin: 0 auto;
        }

        @keyframes typing {
            0% {
                background-color: rgba(0, 0, 0, 1);
                box-shadow: 3.2rem 0 0 0 rgba(0, 0, 0, 0.2), 6.4rem 0 0 0 rgba(0, 0, 0, 0.2);
            }

            25% {
                background-color: rgba(0, 0, 0, 0.4);
                box-shadow: 3.2rem 0 0 0 rgba(0, 0, 0, 2), 6.4rem 0 0 0 rgba(0, 0, 0, 0.2);
            }

            75% {
                background-color: rgba(0, 0, 0, 0.4);
                box-shadow: 3.2rem 0 0 0 rgba(0, 0, 0, 0.2), 6.4rem 0 0 0 rgba(0, 0, 0, 1);
            }
        }

        sociomile-init .auto_width,
        [riot-tag="sociomile-init"] .auto_width,
        [data-is="sociomile-init"] .auto_width {
            padding-left: 10px;
            padding-right: 10px;
            width: auto !important;
        }

        sociomile-init .custom_widget_container,
        [riot-tag="sociomile-init"] .custom_widget_container,
        [data-is="sociomile-init"] .custom_widget_container {
            position: fixed;
            z-index: 100000000;
            display: block;
            bottom: 0;
            right: 12px;
            cursor: pointer;
        }

        ,
        sociomile-init .custom_widget,
        [riot-tag="sociomile-init"] .custom_widget,
        [data-is="sociomile-init"] .custom_widget {
            width: 140px;
        }

        sociomile-login #chat-app .sociomile-body.adjust-body-height form,
        [riot-tag="sociomile-login"] #chat-app .sociomile-body.adjust-body-height form,
        [data-is="sociomile-login"] #chat-app .sociomile-body.adjust-body-height form {
            min-height: calc(100% - 70px) !important;
        }

        sociomile-login select,
        [riot-tag="sociomile-login"] select,
        [data-is="sociomile-login"] select {
            color: #777;
        }

        sociomile-login .placeholder,
        [riot-tag="sociomile-login"] .placeholder,
        [data-is="sociomile-login"] .placeholder {
            color: #777
        }

        sociomile-login .opt-black,
        [riot-tag="sociomile-login"] .opt-black,
        [data-is="sociomile-login"] .opt-black {
            color: #000;
        }

        sociomile-login .login-disable,
        [riot-tag="sociomile-login"] .login-disable,
        [data-is="sociomile-login"] .login-disable {
            background-color: #ccc !important;
            cursor: default !important;
        }

        sociomile-login .sociomile-body .sociomile-f-ctrl select,
        [riot-tag="sociomile-login"] .sociomile-body .sociomile-f-ctrl select,
        [data-is="sociomile-login"] .sociomile-body .sociomile-f-ctrl select,
        sociomile-login .sociomile-body .sociomile-f-ctrl textarea,
        [riot-tag="sociomile-login"] .sociomile-body .sociomile-f-ctrl textarea,
        [data-is="sociomile-login"] .sociomile-body .sociomile-f-ctrl textarea {
            border: 0;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 8px;
            font-size: 14px;
            background-image: none;
            display: block;
            outline-style: none;
            border-radius: 4px;
            background: #f4f4f4;
            margin-bottom: 4px;
            border: 1px solid #fff !important;
        }

        sociomile-multi-chat-selector .sociomile-select-chat,
        [riot-tag="sociomile-multi-chat-selector"] .sociomile-select-chat,
        [data-is="sociomile-multi-chat-selector"] .sociomile-select-chat {
            height: 250px !important;
        }

        sociomile-survey .sociomile-body .sociomile-f-ctrl textarea,
        [riot-tag="sociomile-survey"] .sociomile-body .sociomile-f-ctrl textarea,
        [data-is="sociomile-survey"] .sociomile-body .sociomile-f-ctrl textarea {
            border: 0;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 8px;
            font-size: 14px;
            background-image: none;
            display: block;
            outline-style: none;
            border-radius: 4px;
            background: #fff;
            margin-bottom: 4px;
            border: 1px solid #000 !important;
        }

        sociomile-survey .sociomile-btn,
        [riot-tag="sociomile-survey"] .sociomile-btn,
        [data-is="sociomile-survey"] .sociomile-btn {
            margin-top: 120px;
        }

        sociomile-survey .sociomile-container,
        [riot-tag="sociomile-survey"] .sociomile-container,
        [data-is="sociomile-survey"] .sociomile-container,
        sociomile-survey .sociomile-container.sociomile-survey.sociomile-survey-one-page,
        [riot-tag="sociomile-survey"] .sociomile-container.sociomile-survey.sociomile-survey-one-page,
        [data-is="sociomile-survey"] .sociomile-container.sociomile-survey.sociomile-survey-one-page {
            height: none !important;
        }

    </style>
    <style data-emotion="css"></style>
    <style>
        .css-yatt51 {
            background-repeat: no-repeat;
            background-image: url('{!! asset('/images/bg3.png') !!}');
            background-size: 800px;
            background-position: center bottom;
        }
    </style>
</head>

<body><noscript>You need to enable JavaScript to run this app.</noscript>
<div id="root">
    <div class="css-1bz4ouw">
        <div class="tw-min-h-screen tw-bg-white tw-px-6 tw-py-5 css-0 ebd3p831">
            <a href="#" class="tw-flex tw-items-center tw-mb-6 sm:tw-mb-12 hover:tw-no-underline tw-text-primary hover:tw-text-primary-lighter tw-text-sm"><img src="{!! asset('images/svg-icons/left-arrow.svg') !!}" alt="left-arrow">
                <span class="tw-block tw-pl-2">Kembali</span></a>
            <a href="#" class="tw-block tw-mb-8 sm:tw-mb-12">
                <img src="{!! asset('images/logo-kartu-prakerja.png')!!}" alt="Logo" class="css-avxzix"></a>
            <h3 class="tw-text-primary tw-text-lg tw-font-bold">Login</h3>
            <p class="tw-text-primary tw-mb-10">Bagi kamu yang sudah terdaftar, silakan login</p>
            <form action="#" autocomplete="off" novalidate="">
                <div class="tw-mb-4 css-o2rotx e48vd4k1">
                    <div class="tw-relative">
                        <input name="email" type="email" placeholder="alamat@email.com" class="css-jonc7y e48vd4k0" value="">
                        <label class="css-by8fd3 e48vd4k2">Email</label></div>
                    <div class="tw-relative">
                        <div class="tw-relative">
                            <input name="password" type="password" placeholder="Masukkan password" class="css-jonc7y e48vd4k0" value="">
                            <label class="css-by8fd3 e48vd4k2">Password</label>
                        </div>
                        <img src="{!! asset('images/svg-icons/eye.svg')!!}" alt="show password" class="css-d21dn8">
                    </div>
                </div>
                <div class="tw-mb-5 css-1391vhs">
                    <div class="tw-mt-4 tw-flex tw-justify-between">
                        <div class="tw-text-xs tw-text-abu css-1scdakx">
                            <input type="checkbox" name="remember">
                            <label>Ingat saya</label>
                        </div>
                        <a class="tw-text-xs tw-text-primary" href="/lupa-password">Lupa password?</a>
                    </div>
                </div>
                <div class="tw-my-4">
                    <button type="submit" class="mr-4 css-1iefhk0">Login</button>
                    <button disabled="" class="css-gcesyf">Daftar</button>
                </div>
            </form>
        </div>
        <div class="css-0 ebd3p832">
            <div class="css-yatt51 ebd3p830"></div>
        </div>
    </div>
</div>
<script async="" src="https://www.google-analytics.com/analytics.js"></script>
<script type="text/javascript">
    ! function(e) {
        function t(t) {
            for (var n, o, f = t[0], u = t[1], d = t[2], i = 0, s = []; i < f.length; i++) o = f[i], Object.prototype.hasOwnProperty.call(c, o) && c[o] && s.push(c[o][0]), c[o] = 0;
            for (n in u) Object.prototype.hasOwnProperty.call(u, n) && (e[n] = u[n]);
            for (l && l(t); s.length;) s.shift()();
            return a.push.apply(a, d || []), r()
        }

        function r() {
            for (var e, t = 0; t < a.length; t++) {
                for (var r = a[t], n = !0, o = 1; o < r.length; o++) {
                    var u = r[o];
                    0 !== c[u] && (n = !1)
                }
                n && (a.splice(t--, 1), e = f(f.s = r[0]))
            }
            return e
        }
        var n = {},
            o = {
                9: 0
            },
            c = {
                9: 0
            },
            a = [];

        function f(t) {
            if (n[t]) return n[t].exports;
            var r = n[t] = {
                i: t,
                l: !1,
                exports: {}
            };
            return e[t].call(r.exports, r, r.exports, f), r.l = !0, r.exports
        }
        f.e = function(e) {
            var t = [];
            o[e] ? t.push(o[e]) : 0 !== o[e] && {
                4: 1,
                11: 1,
                12: 1
            } [e] && t.push(o[e] = new Promise((function(t, r) {
                for (var n = "static/css/" + ({} [e] || e) + "." + {
                    0: "31d6cfe0",
                    1: "31d6cfe0",
                    2: "31d6cfe0",
                    3: "31d6cfe0",
                    4: "b1d1adcb",
                    5: "31d6cfe0",
                    6: "31d6cfe0",
                    7: "31d6cfe0",
                    11: "b3571596",
                    12: "345d2108",
                    13: "31d6cfe0",
                    14: "31d6cfe0",
                    15: "31d6cfe0",
                    16: "31d6cfe0",
                    17: "31d6cfe0",
                    18: "31d6cfe0",
                    19: "31d6cfe0",
                    20: "31d6cfe0",
                    21: "31d6cfe0",
                    22: "31d6cfe0",
                    23: "31d6cfe0",
                    24: "31d6cfe0",
                    25: "31d6cfe0",
                    26: "31d6cfe0",
                    27: "31d6cfe0",
                    28: "31d6cfe0"
                } [e] + ".chunk.css", c = f.p + n, a = document.getElementsByTagName("link"), u = 0; u < a.length; u++) {
                    var d = (l = a[u]).getAttribute("data-href") || l.getAttribute("href");
                    if ("stylesheet" === l.rel && (d === n || d === c)) return t()
                }
                var i = document.getElementsByTagName("style");
                for (u = 0; u < i.length; u++) {
                    var l;
                    if ((d = (l = i[u]).getAttribute("data-href")) === n || d === c) return t()
                }
                var s = document.createElement("link");
                s.rel = "stylesheet", s.type = "text/css", s.onload = t, s.onerror = function(t) {
                    var n = t && t.target && t.target.src || c,
                        a = new Error("Loading CSS chunk " + e + " failed.\n(" + n + ")");
                    a.code = "CSS_CHUNK_LOAD_FAILED", a.request = n, delete o[e], s.parentNode.removeChild(s), r(a)
                }, s.href = c, document.getElementsByTagName("head")[0].appendChild(s)
            })).then((function() {
                o[e] = 0
            })));
            var r = c[e];
            if (0 !== r)
                if (r) t.push(r[2]);
                else {
                    var n = new Promise((function(t, n) {
                        r = c[e] = [t, n]
                    }));
                    t.push(r[2] = n);
                    var a, u = document.createElement("script");
                    u.charset = "utf-8", u.timeout = 120, f.nc && u.setAttribute("nonce", f.nc), u.src = function(e) {
                        return f.p + "static/js/" + ({} [e] || e) + "." + {
                            0: "c12290f3",
                            1: "84acd8ee",
                            2: "6ee65657",
                            3: "205abf9b",
                            4: "9155d963",
                            5: "112fe0d3",
                            6: "72ec9c01",
                            7: "e5dfe6b6",
                            11: "55936496",
                            12: "6b1fc289",
                            13: "735ba32d",
                            14: "bb46846e",
                            15: "f6573822",
                            16: "407fbbc0",
                            17: "be67831b",
                            18: "92e3c34d",
                            19: "c9561d7b",
                            20: "76450bb7",
                            21: "a54f496c",
                            22: "35b13e83",
                            23: "d1a7e862",
                            24: "aca7c255",
                            25: "6525908f",
                            26: "fb252d6d",
                            27: "3de47fb2",
                            28: "9a62effd"
                        } [e] + ".chunk.js"
                    }(e);
                    var d = new Error;
                    a = function(t) {
                        u.onerror = u.onload = null, clearTimeout(i);
                        var r = c[e];
                        if (0 !== r) {
                            if (r) {
                                var n = t && ("load" === t.type ? "missing" : t.type),
                                    o = t && t.target && t.target.src;
                                d.message = "Loading chunk " + e + " failed.\n(" + n + ": " + o + ")", d.name = "ChunkLoadError", d.type = n, d.request = o, r[1](d)
                            }
                            c[e] = void 0
                        }
                    };
                    var i = setTimeout((function() {
                        a({
                            type: "timeout",
                            target: u
                        })
                    }), 12e4);
                    u.onerror = u.onload = a, document.head.appendChild(u)
                } return Promise.all(t)
        }, f.m = e, f.c = n, f.d = function(e, t, r) {
            f.o(e, t) || Object.defineProperty(e, t, {
                enumerable: !0,
                get: r
            })
        }, f.r = function(e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
                value: "Module"
            }), Object.defineProperty(e, "__esModule", {
                value: !0
            })
        }, f.t = function(e, t) {
            if (1 & t && (e = f(e)), 8 & t) return e;
            if (4 & t && "object" == typeof e && e && e.__esModule) return e;
            var r = Object.create(null);
            if (f.r(r), Object.defineProperty(r, "default", {
                enumerable: !0,
                value: e
            }), 2 & t && "string" != typeof e)
                for (var n in e) f.d(r, n, function(t) {
                    return e[t]
                }.bind(null, n));
            return r
        }, f.n = function(e) {
            var t = e && e.__esModule ? function() {
                return e.default
            } : function() {
                return e
            };
            return f.d(t, "a", t), t
        }, f.o = function(e, t) {
            return Object.prototype.hasOwnProperty.call(e, t)
        }, f.p = "/", f.oe = function(e) {
            throw console.error(e), e
        };
        var u = this["webpackJsonpprakerja-user-area"] = this["webpackJsonpprakerja-user-area"] || [],
            d = u.push.bind(u);
        u.push = t, u = u.slice();
        for (var i = 0; i < u.length; i++) t(u[i]);
        var l = d;
        r()
    }([])
</script>
<script src="{!! asset('static/js/10.5f2cc1a9.chunk.js') !!}" type="text/javascript"></script>
<script src="{!! asset('static/js/main.fb38cddd.chunk.js') !!}" type="text/javascript"></script>
<div id="prakerja-modal"></div>
<script src="{!! asset('5f5893cc801ddf6dd90bd762.js')!!}" async="true"></script>
<link rel="stylesheet" href="https://chat.sociomile.com/build/css/app-240bae397a.css" media="all">
<link rel="stylesheet" href="{!! asset('css/ionicons.min.css') !!}" media="all">
<link rel="stylesheet" href="https://smcdn.s45.in/2020/11/25//tmp/chat_5f5893cc801ddf6dd90bd762.css?id=5fbe13bc23fa8" media="all">
<div data-is="sociomile-init" riot-tag="sociomile-init">
    <div class="sociomile-container sociomile-init auto_width ">
        <span><i class="ion ion-chatboxes"></i> Minta Bantuan</span>
    </div>
</div>
</body>
</html>
