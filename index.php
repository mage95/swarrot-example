<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Start the session
session_start();

// Perform Login
if (isset($_POST['username'])) {
    $_SESSION["username"] = $_POST['username'];
}

// Send new message
if (isset($_POST['message'])) {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $exchange_name = "rabbitmq-presentation";
    $channel->exchange_declare($exchange_name, 'fanout', false, false, false);
    $queue_name = "rabbitmq-presentation-".$_SESSION["username"];
    $channel->queue_declare($queue_name, false, false, false, false);
    $channel->queue_bind($queue_name, $exchange_name, '');

    $timestamp = new \DateTime("now");
    $timestamp = $timestamp->format("Y-m-d H:i:s");

    $data = array(
        "sender" => $_SESSION["username"],
        "message" => $_POST['message'],
        "timestamp" => $timestamp
    );

    $msg = new AMQPMessage(json_encode($data));

    $channel->basic_publish($msg, $exchange_name);

    $channel->close();
    $connection->close();
    exit;
}


if (isset($_SESSION["username"])) {
    $loggedIn = true;
} else {
    $loggedIn = false;
}
?>
<html>
<head>
    <title>Chat with RabbitMQ</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            if ($("#chat-table").length) {
                setInterval(function(){
                    $.ajax({
                        type: 'GET',
                        url: '/getchat.php',
                        data: {},
                        dataType: 'json',
                        contentType: "application/json",
                        error: function (request, status, error) {
                            console.log(error);
                        }
                    }).done(function (data) {
                        $.each(data, function(index, data) {
                            $('ul#chat-table').append(
                                '<li>'+data.sender+': '+data.message+' ('+data.timestamp+')</li>'
                            );
                        });
                    });
                }, 1000);
            }

            if ($("#msg-form").length) {
                $('#msg-form').submit(function(event){
                    event.preventDefault();

                    $.ajax({
                        method: 'POST',
                        url: '/index.php',
                        data: { message: $("#message").val() },
                        error: function (request, status, error) {
                            console.log(error);
                        }
                    });
                });
            }
        });
    </script>
</head>
<body>
    <?php if (!$loggedIn) { ?>
        <form id="login-form" style="width: 100%; text-align: center;" method="POST">
            <h3>Login</h3>
            Username: <input type="text" name="username" value="" /> &nbsp;
            <input type="submit" value="Submit" />
        </form>
    <?php } else { ?>
        Logged in!! <?php echo $_SESSION["username"]; ?>
        <hr />

        <form id="msg-form" style="width: 100%; text-align: center;" method="POST">
            Message: <input type="text" id="message" name="message" value="" /> &nbsp;
            <input type="submit" value="Send" />
        </form>

        <hr />

        <ul id="chat-table" style="width:100%;">
        </ul>
    <?php } ?>
</body>
</html>