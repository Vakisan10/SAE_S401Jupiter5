<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../../lib-tools/Auth/User.php';

class UserRepository
{
    private static ?PDO $db = null;

    private static function getDb(): PDO
    {
        if (self::$db === null) {
            $model = Model::getModel();
            self::$db = $model->bd;
        }
        return self::$db;
    }

    public static function findByUidCas(string $uid): ?User
    {
        $stmt = self::getDb()->prepare("
            SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email, r.libelle as role, u.departement_id
            FROM utilisateur u
            INNER JOIN role r ON u.role_id = r.id_role
            WHERE u.uid_cas = :uid
        ");

        $stmt->execute(['uid' => $uid]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new User(
            (int) $data['id_utilisateur'],
            $data['uid_cas'],
            $data['fullName'],
            $data['email'],
            strtolower($data['role']),
            $data['departement_id'] ? (int) $data['departement_id'] : null
        );
    }

    public static function findById(int $id): ?User
    {
        $stmt = self::getDb()->prepare("
            SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email, r.libelle as role, u.departement_id
            FROM utilisateur u
            INNER JOIN role r ON u.role_id = r.id_role
            WHERE u.id_utilisateur = :id
        ");

        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new User(
            (int) $data['id_utilisateur'],
            $data['uid_cas'],
            $data['fullName'],
            $data['email'],
            strtolower($data['role']),
            $data['departement_id'] ? (int) $data['departement_id'] : null
        );
    }

    public static function create(string $uid, array $casAttributes, string $role): User
    {
        $db = self::getDb();

        $roleMap = [
            'admin' => 1,
            'postal_iut' => 2,
            'postal_univ' => 3,
            'finance' => 4,
            'directeur' => 5,
            'departement' => 6,
        ];

        $roleId = $roleMap[$role] ?? 6;
        $fullName = $casAttributes['displayName'] ?? $casAttributes['cn'] ?? $uid;
        $email = $casAttributes['mail'] ?? "{$uid}@univ-paris13.fr";
        $accessToken = $casAttributes['access_token'] ?? 'token_' . bin2hex(random_bytes(16));

        $stmt = $db->prepare("
            INSERT INTO utilisateur (uid_cas, access_token_api_cas, fullName, email, role_id)
            VALUES (:uid, :access_token, :nom, :email, :role_id)
        ");

        $stmt->execute([
            'uid' => $uid,
            'access_token' => $accessToken,
            'nom' => $fullName,
            'email' => $email,
            'role_id' => $roleId,
        ]);

        return new User((int) $db->lastInsertId(), $uid, $fullName, $email, $role, null, $casAttributes);
    }

    public static function update(int $userId, array $data): bool
    {
        $db = self::getDb();
        $fields = [];
        $params = ['id' => $userId];

        if (isset($data['fullName'])) { $fields[] = 'fullName = :nom'; $params['nom'] = $data['fullName']; }
        if (isset($data['email'])) { $fields[] = 'email = :email'; $params['email'] = $data['email']; }
        if (isset($data['departement_id'])) { $fields[] = 'departement_id = :dep'; $params['dep'] = $data['departement_id']; }
        if (isset($data['role_id'])) { $fields[] = 'role_id = :role'; $params['role'] = $data['role_id']; }

        if (empty($fields)) return false;

        $sql = "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE id_utilisateur = :id";
        return $db->prepare($sql)->execute($params);
    }

    public static function findAll(): array
    {
        $stmt = self::getDb()->query("
            SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email, r.libelle as role, u.departement_id
            FROM utilisateur u
            INNER JOIN role r ON u.role_id = r.id_role
            ORDER BY u.fullName
        ");

        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                (int) $data['id_utilisateur'],
                $data['uid_cas'],
                $data['fullName'],
                $data['email'],
                strtolower($data['role']),
                $data['departement_id'] ? (int) $data['departement_id'] : null
            );
        }
        return $users;
    }
}
