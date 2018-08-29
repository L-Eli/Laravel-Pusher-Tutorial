<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Pusher</title>
    </head>

    <body>
        <div id="app">
            <p>This is the Event Listener page and when the event is fired off, this page will listen to the status update, and fire off the related listener command.</p>
        </div>
        <script src="js/app.js" charset="utf-8"></script>
    </body>
</html>
