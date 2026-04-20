<!DOCTYPE html>
<html>

<head>
  <title>Birthday Message Sender</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
  <script>
    // Function to periodically check for birthdays and send messages
    function sendBirthdayMessages() {
      // Make an AJAX request to the PHP script
      $.ajax({
        url: "send_birthday_messages.php",
        success: function(response) {
          // Display the response (optional)
          console.log(response);
        },
      });
    }

    // Call the function initially when the page loads
    sendBirthdayMessages();

    // Call the function every 24 hours
    setInterval(sendBirthdayMessages, 5000);
  </script>
</body>

</html>