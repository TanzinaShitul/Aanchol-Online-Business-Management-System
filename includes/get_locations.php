<?php
require_once '../config/database.php';
require_once 'functions.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type === 'districts') {
    $division_id = $_GET['division_id'] ?? null;
    if ($division_id) {
        $districts = getDistricts($division_id);
        echo json_encode($districts);
    } else {
        echo json_encode([]);
    }
} elseif ($type === 'upazilas') {
    $district_id = $_GET['district_id'] ?? null;
    if ($district_id) {
        $upazilas = getUpazilas($district_id);
        echo json_encode($upazilas);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>