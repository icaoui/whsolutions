<?php
$adminTitle = 'Messages';
require_once 'includes/header.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([intval($_GET['delete'])]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_message', "Suppression message #" . intval($_GET['delete']));
}
if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([intval($_GET['read'])]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'read_message', "Lecture message #" . intval($_GET['read']));
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="card">
    <h3><i class="fas fa-envelope"></i> Messages (<?= count($messages) ?>)</h3>
    <table class="admin-table">
        <thead><tr><th>Statut</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Sujet</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($messages as $m): ?>
        <tr style="<?= !$m['is_read'] ? 'background:#f0fdf4;font-weight:600;' : '' ?>">
            <td><?= !$m['is_read'] ? '<span class="badge badge-warning">Nouveau</span>' : '<span class="badge badge-secondary">Lu</span>' ?></td>
            <td><?= sanitize($m['name']) ?></td>
            <td><a href="mailto:<?= sanitize($m['email']) ?>"><?= sanitize($m['email']) ?></a></td>
            <td><?= sanitize($m['phone'] ?? '-') ?></td>
            <td><?= sanitize($m['subject'] ?? '-') ?></td>
            <td title="<?= sanitize($m['message']) ?>"><?= sanitize(mb_strimwidth($m['message'], 0, 60, '...')) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
            <td class="actions">
                <?php if(!$m['is_read']): ?><a href="?read=<?= $m['id'] ?>" class="btn-icon btn-edit" title="Marquer lu"><i class="fas fa-check"></i></a><?php endif; ?>
                <a href="?delete=<?= $m['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($messages)): ?><tr><td colspan="8" style="text-align:center;">Aucun message</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>