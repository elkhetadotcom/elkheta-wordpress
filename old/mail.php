<?PHP
$sender = 'info@elkheta.com';
$recipient = 'cloudways.faizanyounus@gmail.com';

$subject = "PHP mail test";
$message = "php test message";
$headers = 'From:' . $sender;

if (mail($recipient, $subject, $message, $headers))
{
	    echo "Message accepted";
}
else
{
	    echo "Error: Message not accepted";
}
?>
