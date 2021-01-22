
@if(session()->has('message'))
    <script id="toastScript">
        add_toast("{!!session()->get('message')!!}","{{session()->get('type')}}");
    </script>
    @php
        session()->forget('message');
        session()->forget('type');
    @endphp
@endif
