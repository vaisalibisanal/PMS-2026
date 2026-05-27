<?php
// project_functions.php

/**
 * Fetches projects with pagination and optional filters.
 *
 * @param mysqli $conn The database connection object.
 * @param int $pageSize The number of records per page.
 * @param int $offset The offset for pagination.
 * @param string $projectNameFilter Optional filter for project name.
 * @param int|null $createdByFilter Optional filter for created by user ID.
 * @return array An array of project rows.
 */
function get_projects_paginated(mysqli $conn, int $pageSize, int $offset, string $projectNameFilter = '', ?int $createdByFilter = null): array
{
    $sql = "SELECT p.id, p.project_name, p.project_code, p.created_on, p.status, u.name as created_by_name
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.status = 0"; // Only active projects

    $params = [];
    $types = "";

    if (!empty($projectNameFilter)) {
        $sql .= " AND p.project_name LIKE ?";
        $params[] = '%' . $projectNameFilter . '%';
        $types .= "s";
    }

    if ($createdByFilter !== null) {
        $sql .= " AND p.created_by = ?";
        $params[] = $createdByFilter;
        $types .= "i";
    }

    $sql .= " ORDER BY p.created_on DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $pageSize;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for get_projects_paginated: " . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $projects;
}

/**
 * Gets the total count of active projects, with optional filters.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectNameFilter Optional filter for project name.
 * @param int|null $createdByFilter Optional filter for created by user ID.
 * @return int The total number of active projects.
 */
function get_project_count(mysqli $conn, string $projectNameFilter = '', ?int $createdByFilter = null): int
{
    $sql = "SELECT COUNT(id) AS total FROM projects WHERE status = 0"; // Only active projects

    $params = [];
    $types = "";

    if (!empty($projectNameFilter)) {
        $sql .= " AND project_name LIKE ?";
        $params[] = '%' . $projectNameFilter . '%';
        $types .= "s";
    }

    if ($createdByFilter !== null) {
        $sql .= " AND created_by = ?";
        $params[] = $createdByFilter;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for get_project_count: " . $conn->error);
        return 0;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total'] ?? 0;
}

/**
 * Adds a new project to the database.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectName The name of the project.
 * @param string $projectCode The unique code of the project.
 * @param int $status The status of the project (0 for active, 1 for inactive).
 * @param int $createdBy The ID of the user who created the project.
 * @return bool True on success, false on failure.
 */
function add_project(mysqli $conn, string $projectName, string $projectCode, int $status, int $createdBy): bool
{
    $sql = "INSERT INTO projects (project_name, project_code, status, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for add_project: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ssii", $projectName, $projectCode, $status, $createdBy);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Checks if a project code already exists.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectCode The project code to check.
 * @return bool True if the project code exists, false otherwise.
 */
function is_project_code___duplicate(mysqli $conn, string $projectCode): bool
{
    $sql = "SELECT COUNT(id) AS count FROM projects WHERE project_code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for is_project_code_duplicate: " . $conn->error);
        return true; // Assume duplicate on error for safety
    }
    $stmt->bind_param("s", $projectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return ($row['count'] > 0);
}

/**
 * Performs a soft delete on a project by updating its status to 1 (inactive).
 *
 * @param mysqli $conn The database connection object.
 * @param int $projectId The ID of the project to soft delete.
 * @return bool True on success, false on failure.
 */
function soft_delete_project(mysqli $conn, int $projectId): bool
{
    $sql = "UPDATE projects SET status = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for soft_delete_project: " . $conn->error);
        return false;
    }
    $stmt->bind_param("i", $projectId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
/**
 * Fetches a single project's details by its ID.
 *
 * @param mysqli $conn The database connection object.
 * @param int $projectId The ID of the project to fetch.
 * @return array|null An associative array of project data, or null if not found.
 */
function get_project_by_id(mysqli $conn, int $projectId): ?array
{
    $sql = "SELECT id, project_name, project_code, status FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for get_project_by_id: " . $conn->error);
        return null;
    }
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
    return $project;
}
/**
 * Fetches projects with pagination and optional filters.
 *
 * @param mysqli $conn The database connection object.
 * @param int $pageSize The number of records per page.
 * @param int $offset The offset for pagination.
 * @param string $projectNameFilter Optional filter for project name.
 * @param int|null $createdByFilter Optional filter for created by user ID.
 * @return array An array of project rows.
 */
function get_projects___paginated(mysqli $conn, int $pageSize, int $offset, string $projectNameFilter = '', ?int $createdByFilter = null): array
{
    $sql = "SELECT p.id, p.project_name, p.project_code, p.created_on, p.status, u.name as created_by_name
            FROM projects p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.status = 0"; // Only active projects

    $params = [];
    $types = "";

    if (!empty($projectNameFilter)) {
        $sql .= " AND p.project_name LIKE ?";
        $params[] = '%' . $projectNameFilter . '%';
        $types .= "s";
    }

    if ($createdByFilter !== null) {
        $sql .= " AND p.created_by = ?";
        $params[] = $createdByFilter;
        $types .= "i";
    }

    $sql .= " ORDER BY p.created_on DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $pageSize;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for get_projects_paginated: " . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $projects;
}

/**
 * Gets the total count of active projects, with optional filters.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectNameFilter Optional filter for project name.
 * @param int|null $createdByFilter Optional filter for created by user ID.
 * @return int The total number of active projects.
 */
function get_project___count(mysqli $conn, string $projectNameFilter = '', ?int $createdByFilter = null): int
{
    $sql = "SELECT COUNT(id) AS total FROM projects WHERE status = 0"; // Only active projects

    $params = [];
    $types = "";

    if (!empty($projectNameFilter)) {
        $sql .= " AND project_name LIKE ?";
        $params[] = '%' . $projectNameFilter . '%';
        $types .= "s";
    }

    if ($createdByFilter !== null) {
        $sql .= " AND created_by = ?";
        $params[] = $createdByFilter;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for get_project_count: " . $conn->error);
        return 0;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total'] ?? 0;
}

/**
 * Adds a new project to the database.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectName The name of the project.
 * @param string $projectCode The unique code of the project.
 * @param int $status The status of the project (0 for active, 1 for inactive).
 * @param int $createdBy The ID of the user who created the project.
 * @return bool True on success, false on failure.
 */
function add___project(mysqli $conn, string $projectName, string $projectCode, int $status, int $createdBy): bool
{
    $sql = "INSERT INTO projects (project_name, project_code, status, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for add_project: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ssii", $projectName, $projectCode, $status, $createdBy);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Checks if a project code already exists.
 *
 * @param mysqli $conn The database connection object.
 * @param string $projectCode The project code to check. The project code to check.
 * @param int|null $excludeProjectId Optional. If provided, the project with this ID will be excluded from the check.
 * @return bool True if the project code exists, false otherwise.
 */
function is_project_code_duplicate(mysqli $conn, string $projectCode, ?int $excludeProjectId = null): bool
{
    $sql = "SELECT COUNT(id) AS count FROM projects WHERE project_code = ?";
    $types = "s";
    $params = [$projectCode];

    if ($excludeProjectId !== null) {
        $sql .= " AND id != ?";
        $types .= "i";
        $params[] = $excludeProjectId;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for is_project_code_duplicate: " . $conn->error);
        return true; // Assume duplicate on error for safety
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return ($row['count'] > 0);
}
/**
 * Updates an existing project in the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $projectId The ID of the project to update.
 * @param string $projectName The new name of the project.
 * @param string $projectCode The new unique code of the project.
 * @param int $status The new status of the project (0 for active, 1 for inactive).
 * @return bool True on success, false on failure.
 */
function update_project(mysqli $conn, int $projectId, string $projectName, string $projectCode, int $status): bool
{
    $sql = "UPDATE projects SET project_name = ?, project_code = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement for update_project: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ssii", $projectName, $projectCode, $status, $projectId); // Corrected parameter binding
    $success = $stmt->execute(); // Execute the update query
    $stmt->close();
    return $success; // Return true on successful execution, false otherwise
}
?>