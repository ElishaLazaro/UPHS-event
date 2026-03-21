<?php
// getEvent.php
$sql = 
"SELECT event_id, event_name, activity as description, date_start, date_end, time_start, time_end, 
        CONCAT(date_start, ' - ', date_end) as date, 
        CONCAT(time_start, ' - ', time_end) as time, 
        venue, event_image, 
        '' as register_link, 
        '' as organizer
 FROM events 
 WHERE event_status = 1
 ORDER BY date_start ASC;
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Store events for JavaScript
$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['event_id'],
        'title' => $row['event_name'],
        'start' => $row['date'],
        'description' => $row['description'] ?? '',
        'time' => $row['time'],
        'venue' => $row['venue'] ?? '',
        'image' => $row['event_image'] ?? ''
    ];
}

// Reset result pointer for the main while loop
$stmt->execute();
$result = $stmt->get_result();
?>