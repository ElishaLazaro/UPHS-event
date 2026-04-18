<?php
// Unit test for adding events
require 'actions/conn.php';

// Function to add event
function addEvent($conn, $user_id, $event_name, $activity, $venue, $date_start, $date_end, $time_start, $time_end, $status) {
    $sql = "INSERT INTO events (u_id, event_name, activity, venue, date_start, date_end, time_start, time_end, event_status)
            VALUES (?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("isssssssi", $user_id, $event_name, $activity, $venue, $date_start, $date_end, $time_start, $time_end, $status);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Function to check if event exists
function eventExists($conn, $event_name, $user_id) {
    $sql = "SELECT event_id FROM events WHERE event_name = ? AND u_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $event_name, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to delete test event
function deleteEvent($conn, $event_name, $user_id) {
    $sql = "DELETE FROM events WHERE event_name = ? AND u_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $event_name, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Test cases
$testCases = [
    [
        'user_id' => 1,
        'event_name' => "Test Event Valid " . time(),
        'activity' => "Test Activity",
        'venue' => "Test Venue",
        'date_start' => "2026-04-20",
        'date_end' => "2026-04-20",
        'time_start' => "10:00:00",
        'time_end' => "12:00:00",
        'status' => 3,
        'expected' => true,
        'description' => 'Valid event addition'
    ],
    [
        'user_id' => 1,
        'event_name' => "", // Empty name
        'activity' => "Test Activity",
        'venue' => "Test Venue",
        'date_start' => "2026-04-20",
        'date_end' => "2026-04-20",
        'time_start' => "10:00:00",
        'time_end' => "12:00:00",
        'status' => 3,
        'expected' => false, // Should fail due to empty name
        'description' => 'Invalid event with empty name'
    ],
    [
        'user_id' => 999, // Non-existent user
        'event_name' => "Test Event Invalid User " . time(),
        'activity' => "Test Activity",
        'venue' => "Test Venue",
        'date_start' => "2026-04-20",
        'date_end' => "2026-04-20",
        'time_start' => "10:00:00",
        'time_end' => "12:00:00",
        'status' => 3,
        'expected' => true, // Foreign key might allow or not, but insertion should work if no constraint
        'description' => 'Event with non-existent user ID'
    ]
];

echo "Running unit tests for adding events...\n\n";

foreach ($testCases as $i => $test) {
    echo "Test " . ($i + 1) . ": " . $test['description'] . "\n";

    $added = addEvent($conn, $test['user_id'], $test['event_name'], $test['activity'], $test['venue'], $test['date_start'], $test['date_end'], $test['time_start'], $test['time_end'], $test['status']);

    if ($added === $test['expected']) {
        if ($added) {
            if (eventExists($conn, $test['event_name'], $test['user_id'])) {
                echo "SUCCESS: Event '" . $test['event_name'] . "' was added successfully!\n";
                // Clean up
                deleteEvent($conn, $test['event_name'], $test['user_id']);
                echo "Test event cleaned up.\n";
            } else {
                echo "ERROR: Event was reported added but not found in database.\n";
            }
        } else {
            echo "SUCCESS: Event addition correctly failed as expected.\n";
        }
    } else {
        echo "ERROR: Unexpected result. Expected " . ($test['expected'] ? 'success' : 'failure') . ", got " . ($added ? 'success' : 'failure') . ".\n";
    }
    echo "\n";
}

$conn->close();
?>