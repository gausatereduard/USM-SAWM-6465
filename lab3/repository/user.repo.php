<?php
declare(strict_types=1);

require_once __DIR__ . '/../helper/db.helper.php';

function createUser(string $username, string $passwordHash, string $role = 'user'): int
{
    $stmt = getDB()->prepare(
        'INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)'
    );
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $passwordHash,
        ':role' => $role,
    ]);

    return (int) getDB()->lastInsertId();
}

function getUserById(int $id): ?array
{
    $stmt = getDB()->prepare('SELECT id, username, password_hash, role FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}

function getUserByUsername(string $username): ?array
{
    $stmt = getDB()->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}
