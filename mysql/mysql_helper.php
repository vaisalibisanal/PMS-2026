<?php
// Returns associative array for a user by id, or null if not found
function get_user_by_id($conn, $id)
{
    $id = intval($id);
    if ($stmt = $conn->prepare('SELECT id, name, email FROM users WHERE id = ?')) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }
    return null;
    
}
function get_user_count($conn)
{
    if ($stmt = $conn->prepare('SELECT COUNT(*) AS total FROM users')) {
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stmt->close();
            return intval($row['total']);
        }
        $stmt->close();
    }
    return 0;
}

function get_users_page($conn, $limit, $offset)
{
    $limit = intval($limit);
    $offset = intval($offset);
    $users = [];
    if ($stmt = $conn->prepare('SELECT id, name, email FROM users ORDER BY id ASC LIMIT ? OFFSET ?')) {
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $users[] = $r;
        }
        $stmt->close();
    }
    return $users;
}

