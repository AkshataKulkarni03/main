<?php
function findAvailableTime($appointments, $duration, $originalTimeRange)
{
 // Setting the timezone to UTC
 date_default_timezone_set('UTC');
 list($originalStart, $originalEnd) = explode("/", $originalTimeRange);
 $originalStartTime = strtotime($originalStart);
 $originalEndTime = strtotime($originalEnd);
 $busyTimes = [];
 foreach ($appointments as $appointment) {
 $start = strtotime($appointment['start']);
 $end = strtotime($appointment['end']);
 $busyTimes[] = ['start' => $start, 'end' => $end];
 }
 // Sort busy times by start time
 $fetchStartKey = array_column($busyTimes, 'start');
 array_multisort($fetchStartKey, SORT_ASC, $busyTimes);
 $tempTime = $originalStartTime;
 $availableSlots = []; // list of available time slots
 foreach ($busyTimes as $appointment) {
 $appointmentStartTime = $appointment['start'];
 $appointmentEndTime = $appointment['end'];
 // Check if there is a gap between appointments greater than or equal to the duration
 if ($appointmentStartTime - $tempTime >= $duration) {
 $availableSlots[] = [
 date("d-m-Y-H-i-s", $tempTime),
 date("d-m-Y-H-i-s", $appointmentStartTime)
 ];
 }
 $tempTime = $appointmentEndTime;
 }
 // Check if there is a gap between the last appointment and the end time
 if ($originalEndTime - $tempTime >= $duration) {
 $availableSlots[] = [
 date("d-m-Y-H-i-s", $tempTime),
 date("d-m-Y-H-i-s", $originalEndTime)
 ];
 }
 return $availableSlots;
}
// Sample JSON data taken from the json file
// else we can file_puts_content and read the json by using json_decode and fetch the calendar_ids
$appointments = [
 [
 "id" => "1847c677-1be8-4e44-a84b-edd6751e8302",
 "patient_id" => "1bee2bf0-9751-11e5-bc1b-c8e0eb18c1e9",
 "calendar_id" => "48cadf26-975e-11e5-b9c2-c8e0eb18c1e9",
 "start" => "2019-04-23T12:15:00",
 "end" => "2019-04-23T12:30:00",
 ],
 [
 "id" => "bdad6fcd-d9d2-4418-aceb-ee3180f04450",
 "patient_id" => "1bee2bf0-9751-11e5-bc1b-c8e0eb18c1e9",
 "calendar_id" => "48cadf26-975e-11e5-b9c2-c8e0eb18c1e9",
 "start" => "2019-04-23T18:30:00",
 "end" => "2019-04-23T18:45:00",
 ]
];
$duration = 30;
$originalTimeRange = "2019-03-01T13:00:00Z/2019-05-11T15:30:00Z";
$availableSlots = findAvailableTime($appointments, $duration, $originalTimeRange);
print_r($availableSlots);
