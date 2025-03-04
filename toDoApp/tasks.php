<?php
header("Content-Type: application/json");

$file = "tasks.json";
$action = $_GET['action'] ?? '';

// Get all tasks or a single task
if ($action === "get") {
    if (!file_exists($file)) {
        echo json_encode([]);
        exit;
    }

    $tasks = json_decode(file_get_contents($file), true);

    // Return a single task if ID is provided
    if (isset($_GET['id'])) {
        $task = array_values(array_filter($tasks, fn($t) => $t['id'] == $_GET['id']))[0] ?? null;
        echo json_encode($task);
        exit;
    }

    echo json_encode($tasks);
    exit;
}

// Save or update a task
if ($action === "save") {
    $inputData = json_decode(file_get_contents("php://input"), true);
    
    if (!$inputData) {
        echo json_encode(["error" => "Invalid input"]);
        exit;
    }

    $tasks = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $index = array_search($inputData['id'], array_column($tasks, 'id'));

    if ($index !== false) {
        $tasks[$index] = $inputData; // Update existing task
    } else {
        $tasks[] = $inputData; // Add new task
    }

    file_put_contents($file, json_encode($tasks));
    echo json_encode(["success" => true]);
    exit;
}

// Delete a task
if ($action === "delete") {
    $id = $_GET['id'] ?? '';

    if (!$id) {
        echo json_encode(["error" => "No ID provided"]);
        exit;
    }

    $tasks = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $tasks = array_filter($tasks, fn($task) => $task['id'] != $id);

    file_put_contents($file, json_encode(array_values($tasks)));
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
exit;
