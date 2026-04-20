<?php
// Set an infinite execution time for the script
set_time_limit(0);

// Function to perform the daemon logic
function runDaemon()
{
    while (true) {
        echo 1;

        // Sleep for a specified interval (e.g., 1 second) before the next iteration
        sleep(1);
    }
}

// Run the daemon
runDaemon();
