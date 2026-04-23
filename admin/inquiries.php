<?php
$adminTitle = 'Demandes de Renseignement';
require_once 'includes/header.php';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([intval($_GET['delete'])]);
}
if (isset($_GET['status']) && isset($_GET['id'])) {
    $status = in_array($_GET['status'], ['new','contacted','completed','cancelled']) ? $_GET['status'] : 'new';
    $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?")->execute([$status, intval($_GET['id'])]);
}

$inquiries = $pdo->query("SELECT i.*, p.name as product_name FROM inquiries i LEFT JOIN products p ON i.product_id = p.id ORDER BY i.created_at DESC")->fetchAll();

$statusLabels = ['new' => 'Nouveau', 'contacted' => 'Contacté', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];
$statusBadges = ['new' => 'warning', 'contacted' => 'primary', 'completed' => 'success', 'cancelled' => 'secondary'];
?>

<div class="card">
    <h3><i class="fas fa-question-circle"></i> Demandes (<?= count($inquiries) ?>)</h3>
    <table class="admin-table">
        <thead><tr><th>Client</th><th>Téléphone</th><th>Email</th><th>Produit</th><th>Qté</th><th>Message</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($inquiries as $inq): ?>
        <tr>
            <td><strong><?= sanitize($inq['customer_name']) ?></strong></td>
            <td><?= sanitize($inq['customer_phone']) ?></td>
            <td><?= sanitize($inq['customer_email'] ?? '-') ?></td>
            <td><?= sanitize($inq['product_name'] ?? 'N/A') ?></td>
            <td><?= $inq['quantity'] ?></td>
            <td title="<?= sanitize($inq['message'] ?? '') ?>"><?= sanitize(mb_strimwidth($inq['message'] ?? '', 0, 50, '...')) ?></td>
            <td>
                <span class="badge badge-<?= $statusBadges[$inq['status']] ?? 'secondary' ?>">
                    <?= $statusLabels[$inq['status']] ?? $inq['status'] ?>
                </span>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($inq['created_at'])) ?></td>
            <td class="actions">
                <?php if($inq['status'] === 'new'): ?>
                <a href="?id=<?= $inq['id'] ?>&status=contacted" class="btn-icon btn-edit" title="Marquer contacté"><i class="fas fa-phone"></i></a>
                <?php elseif($inq['status'] === 'contacted'): ?>
                <a href="?id=<?= $inq['id'] ?>&status=completed" class="btn-icon btn-edit" title="Marquer terminé"><i class="fas fa-check"></i></a>
                <?php endif; ?>
                <?php if($inq['status'] !== 'cancelled' && $inq['status'] !== 'completed'): ?>
                <a href="?id=<?= $inq['id'] ?>&status=cancelled" class="btn-icon" style="color:var(--danger);" title="Annuler" onclick="return confirm('Annuler cette demande ?')"><i class="fas fa-ban"></i></a>
                <?php endif; ?>
                <a href="https://wa.me/<?= preg_replace('/\D/', '', $inq['customer_phone']) ?>" target="_blank" class="btn-icon" style="color:#25D366;" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                <a href="?delete=<?= $inq['id'] ?>" class="btn-icon btn-delete" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($inquiries)): ?><tr><td colspan="9" style="text-align:center;">Aucune demande</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>