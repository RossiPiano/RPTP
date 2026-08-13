<?php
// profile_handler.php - Handles profile save/load operations

// Allow from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Create profiles folder if it doesn't exist
$profiles_dir = __DIR__ . '/profiles/';
if (!file_exists($profiles_dir)) {
    mkdir($profiles_dir, 0777, true);
}

// Get the action from the request
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'save') {
    // SAVE PROFILE
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['studentName'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $filename = $profiles_dir . $data['studentName'] . '.txt';
    $json_data = json_encode($data, JSON_PRETTY_PRINT);

    if (file_put_contents($filename, $json_data)) {
        echo json_encode(['success' => true, 'message' => 'Profile saved']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not save file']);
    }

} elseif ($action === 'load') {
    // LOAD PROFILE
    $studentName = isset($_GET['name']) ? $_GET['name'] : '';
    if (!$studentName) {
        echo json_encode(['success' => false, 'error' => 'No name provided']);
        exit;
    }

    $filename = $profiles_dir . $studentName . '.txt';
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid file format']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Profile not found']);
    }

} elseif ($action === 'list') {
    // LIST ALL PROFILES
    $profiles = [];
    $files = glob($profiles_dir . '*.txt');
    foreach ($files as $file) {
        $name = basename($file, '.txt');
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if ($data) {
            $profiles[] = [
                'name' => $name,
                'skillPoints' => $data['skillPoints'] ?? 0,
                'unlockedSkills' => $data['unlockedSkills'] ?? [],
                'masteredSkills' => $data['masteredSkills'] ?? []
            ];
        }
    }
    echo json_encode(['success' => true, 'profiles' => $profiles]);

} elseif ($action === 'delete') {
    // DELETE PROFILE
    $studentName = isset($_GET['name']) ? $_GET['name'] : '';
    if (!$studentName) {
        echo json_encode(['success' => false, 'error' => 'No name provided']);
        exit;
    }

    $filename = $profiles_dir . $studentName . '.txt';
    if (file_exists($filename)) {
        if (unlink($filename)) {
            echo json_encode(['success' => true, 'message' => 'Profile deleted']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not delete file']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Profile not found']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
