<?php
$adminTitle = 'Rapports';
require_once 'includes/header.php';

$success = '';
$error = '';

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM reports WHERE id = ?")->execute([$id]);
    logAdminActivity($pdo, $_SESSION['admin_id'], 'delete_report', "Rapport #$id supprimé");
    header('Location: reports.php?msg=deleted');
    exit;
}

// Export as HTML file
if (isset($_GET['export'])) {
    $id = (int)$_GET['export'];
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    $report = $stmt->fetch();
    if ($report) {
        $filename = 'rapport_' . preg_replace('/[^a-z0-9]/i', '_', $report['title']) . '_' . date('Ymd') . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>' . htmlspecialchars($report['title']) . '</title>';
        echo '<style>body{font-family:Segoe UI,Arial,sans-serif;max-width:800px;margin:40px auto;padding:0 20px;color:#333;line-height:1.7;}';
        echo 'h1{color:#1B3A5C;border-bottom:3px solid #4ECDC4;padding-bottom:10px;}';
        echo 'h2,h3,h4{color:#1B3A5C;margin-top:20px;}';
        echo '.meta{color:#888;font-size:0.9rem;margin-bottom:30px;}';
        echo '.content{font-size:1rem;} .content p{margin:10px 0;} .content ul,.content ol{padding-left:25px;margin:10px 0;}';
        echo '.content blockquote{border-left:4px solid #4ECDC4;margin:15px 0;padding:10px 20px;background:#f8f9fa;border-radius:0 8px 8px 0;}';
        echo '.content table{width:100%;border-collapse:collapse;margin:15px 0;} .content th,.content td{border:1px solid #ddd;padding:8px 12px;text-align:left;} .content th{background:#f0f0f0;font-weight:600;}';
        echo '.content pre{background:#2d2d2d;color:#f8f8f2;padding:15px;border-radius:8px;overflow-x:auto;}';
        echo '.content hr{border:none;border-top:2px solid #e0e0e0;margin:20px 0;}';
        echo '.footer{margin-top:40px;padding-top:15px;border-top:1px solid #ddd;color:#aaa;font-size:0.8rem;text-align:center;}';
        echo '</style></head><body>';
        echo '<h1>' . htmlspecialchars($report['title']) . '</h1>';
        echo '<div class="meta">Auteur : ' . htmlspecialchars($report['author']) . ' | Date : ' . date('d/m/Y H:i', strtotime($report['created_at'])) . '</div>';
        echo '<div class="content">' . sanitizeHtml($report['content']) . '</div>';
        echo '<div class="footer">Rapport généré par WH Solutions Admin — ' . date('d/m/Y') . '</div>';
        echo '</body></html>';
        exit;
    }
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $author = $_SESSION['admin_name'] ?? 'Admin';

    if (empty($title) || empty($content)) {
        $error = 'Le titre et le contenu sont requis.';
    } else {
        $content = sanitizeHtml($content);
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO reports (title, content, category, author, admin_id) VALUES (?,?,?,?,?)");
            $stmt->execute([$title, $content, $category, $author, $_SESSION['admin_id']]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'add_report', "Rapport '$title' créé");
            $success = 'Rapport créé avec succès !';
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE reports SET title=?, content=?, category=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $content, $category, $id]);
            logAdminActivity($pdo, $_SESSION['admin_id'], 'edit_report', "Rapport #$id modifié");
            $success = 'Rapport modifié avec succès !';
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $success = 'Rapport supprimé.';
}

// Filters
$filterCat = $_GET['cat'] ?? '';
$search = trim($_GET['search'] ?? '');
$where = [];
$params = [];
if ($filterCat) { $where[] = "r.category = ?"; $params[] = $filterCat; }
if ($search) { $where[] = "(r.title LIKE ? OR r.content LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$reports = $pdo->prepare("SELECT r.* FROM reports r $whereSQL ORDER BY r.created_at DESC");
$reports->execute($params);
$reports = $reports->fetchAll();

// Edit mode
$editReport = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editReport = $stmt->fetch();
}

// View mode
$viewReport = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([(int)$_GET['view']]);
    $viewReport = $stmt->fetch();
}

$categories = ['general' => 'Général', 'commercial' => 'Commercial', 'technique' => 'Technique', 'stock' => 'Stock & Inventaire', 'client' => 'Client', 'financier' => 'Financier'];
$catColors = ['general' => '#1B3A5C', 'commercial' => '#27ae60', 'technique' => '#e67e22', 'stock' => '#8e44ad', 'client' => '#2980b9', 'financier' => '#c0392b'];
?>

<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<?php if($viewReport): ?>
<!-- View Report -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;"><?= sanitize($viewReport['title']) ?></h3>
        <div style="display:flex; gap:8px;">
            <a href="reports.php?export=<?= $viewReport['id'] ?>" class="btn-admin btn-admin-primary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-download"></i> Exporter HTML</a>
            <a href="reports.php?edit=<?= $viewReport['id'] ?>" class="btn-admin btn-admin-secondary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-edit"></i> Modifier</a>
            <a href="reports.php" class="btn-admin btn-admin-secondary" style="font-size:0.85rem; padding:8px 16px;"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:flex; gap:15px; margin-bottom:20px; color:#888; font-size:0.88rem;">
            <span><i class="fas fa-user"></i> <?= sanitize($viewReport['author']) ?></span>
            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($viewReport['created_at'])) ?></span>
            <span style="background:<?= $catColors[$viewReport['category']] ?? '#1B3A5C' ?>20; color:<?= $catColors[$viewReport['category']] ?? '#1B3A5C' ?>; padding:2px 12px; border-radius:20px; font-weight:600;">
                <?= $categories[$viewReport['category']] ?? $viewReport['category'] ?>
            </span>
            <?php if($viewReport['updated_at'] && $viewReport['updated_at'] !== $viewReport['created_at']): ?>
            <span><i class="fas fa-edit"></i> Modifié le <?= date('d/m/Y H:i', strtotime($viewReport['updated_at'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="report-view-content" style="background:#f8f9fa; border-radius:12px; padding:30px; line-height:1.8; font-size:0.95rem; color:#333;">
<?= sanitizeHtml($viewReport['content']) ?>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Form -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-header">
        <h3><?= $editReport ? 'Modifier le rapport' : 'Rédiger un rapport' ?></h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editReport ? 'edit' : 'add' ?>">
            <?php if($editReport): ?><input type="hidden" name="id" value="<?= $editReport['id'] ?>"><?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 200px; gap:15px; margin-bottom:15px;">
                <div class="form-group">
                    <label>Titre du rapport *</label>
                    <input type="text" name="title" class="form-control" value="<?= sanitize($editReport['title'] ?? '') ?>" required placeholder="Ex: Rapport mensuel - Avril 2026">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control">
                        <?php foreach($categories as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($editReport['category'] ?? 'general') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Contenu du rapport *</label>
                <div class="report-editor">
                    <div class="editor-toolbar">
                        <div class="toolbar-group">
                            <select class="toolbar-select" data-command="formatBlock" title="Style de texte">
                                <option value="p">Paragraphe</option>
                                <option value="h1">Titre 1</option>
                                <option value="h2">Titre 2</option>
                                <option value="h3">Titre 3</option>
                                <option value="h4">Titre 4</option>
                                <option value="blockquote">Citation</option>
                                <option value="pre">Code</option>
                            </select>
                            <select class="toolbar-select" data-command="fontSize" title="Taille de police">
                                <option value="3">Normal</option>
                                <option value="1">Très petit</option>
                                <option value="2">Petit</option>
                                <option value="4">Grand</option>
                                <option value="5">Très grand</option>
                                <option value="6">Énorme</option>
                            </select>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" data-command="bold" title="Gras (Ctrl+B)"><i class="fas fa-bold"></i></button>
                            <button type="button" class="toolbar-btn" data-command="italic" title="Italique (Ctrl+I)"><i class="fas fa-italic"></i></button>
                            <button type="button" class="toolbar-btn" data-command="underline" title="Souligné (Ctrl+U)"><i class="fas fa-underline"></i></button>
                            <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Barré"><i class="fas fa-strikethrough"></i></button>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn toolbar-color" title="Couleur du texte">
                                <i class="fas fa-palette"></i>
                                <input type="color" class="toolbar-color-input" data-command="foreColor" value="#333333">
                            </button>
                            <button type="button" class="toolbar-btn toolbar-color" title="Surligner">
                                <i class="fas fa-highlighter"></i>
                                <input type="color" class="toolbar-color-input" data-command="hiliteColor" value="#FFFF00">
                            </button>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Aligner à gauche"><i class="fas fa-align-left"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Centrer"><i class="fas fa-align-center"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyRight" title="Aligner à droite"><i class="fas fa-align-right"></i></button>
                            <button type="button" class="toolbar-btn" data-command="justifyFull" title="Justifier"><i class="fas fa-align-justify"></i></button>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Liste à puces"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Liste numérotée"><i class="fas fa-list-ol"></i></button>
                            <button type="button" class="toolbar-btn" data-command="indent" title="Augmenter le retrait"><i class="fas fa-indent"></i></button>
                            <button type="button" class="toolbar-btn" data-command="outdent" title="Diminuer le retrait"><i class="fas fa-outdent"></i></button>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" data-command="insertHorizontalRule" title="Ligne horizontale"><i class="fas fa-minus"></i></button>
                            <button type="button" class="toolbar-btn" data-command="createLink" title="Insérer un lien"><i class="fas fa-link"></i></button>
                            <button type="button" class="toolbar-btn" data-command="unlink" title="Supprimer le lien"><i class="fas fa-unlink"></i></button>
                            <button type="button" class="toolbar-btn" data-command="insertTable" title="Insérer un tableau"><i class="fas fa-table"></i></button>
                        </div>
                        <div class="toolbar-separator"></div>
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" data-command="undo" title="Annuler (Ctrl+Z)"><i class="fas fa-undo"></i></button>
                            <button type="button" class="toolbar-btn" data-command="redo" title="Rétablir (Ctrl+Y)"><i class="fas fa-redo"></i></button>
                            <button type="button" class="toolbar-btn" data-command="removeFormat" title="Supprimer la mise en forme"><i class="fas fa-eraser"></i></button>
                        </div>
                    </div>
                    <div class="editor-content" contenteditable="true" id="reportEditor" data-placeholder="Rédigez votre rapport ici..."><?= $editReport ? sanitizeHtml($editReport['content'] ?? '') : '' ?></div>
                    <textarea name="content" id="reportContentHidden" style="display:none;" required><?= sanitize($editReport['content'] ?? '') ?></textarea>
                    <div class="editor-statusbar">
                        <span id="editorWordCount">0 mots</span>
                        <span id="editorCharCount">0 caractères</span>
                    </div>
                </div>
            </div>

            <div style="margin-top:15px; display:flex; gap:10px;">
                <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> <?= $editReport ? 'Modifier' : 'Enregistrer' ?></button>
                <?php if($editReport): ?>
                <a href="reports.php" class="btn-admin btn-admin-secondary"><i class="fas fa-times"></i> Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:25px;">
    <div class="card-body">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label>Rechercher</label>
                <input type="text" name="search" class="form-control" value="<?= sanitize($search) ?>" placeholder="Titre ou contenu...">
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="cat" class="form-control">
                    <option value="">Toutes</option>
                    <?php foreach($categories as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $filterCat === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="height:42px;"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="reports.php" class="btn-admin btn-admin-secondary" style="height:42px;"><i class="fas fa-redo"></i> Reset</a>
        </form>
    </div>
</div>

<!-- Reports List -->
<div class="card">
    <div class="card-header"><h3>Rapports (<?= count($reports) ?>)</h3></div>
    <div class="card-body">
        <?php if(empty($reports)): ?>
            <p style="text-align:center; color:#999; padding:30px;">Aucun rapport rédigé. Utilisez le formulaire ci-dessus pour en créer un.</p>
        <?php else: ?>
        <div style="display:grid; gap:15px;">
            <?php foreach($reports as $r): ?>
            <div style="background:#f8f9fa; border-radius:12px; padding:20px; border-left:4px solid <?= $catColors[$r['category']] ?? '#1B3A5C' ?>; display:flex; justify-content:space-between; align-items:flex-start; gap:15px;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                        <a href="reports.php?view=<?= $r['id'] ?>" style="font-weight:700; color:var(--primary,#1B3A5C); font-size:1.05rem; text-decoration:none;"><?= sanitize($r['title']) ?></a>
                        <span style="background:<?= $catColors[$r['category']] ?? '#1B3A5C' ?>20; color:<?= $catColors[$r['category']] ?? '#1B3A5C' ?>; padding:2px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; white-space:nowrap;">
                            <?= $categories[$r['category']] ?? $r['category'] ?>
                        </span>
                    </div>
                    <p style="color:#666; font-size:0.88rem; margin:0 0 8px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?= sanitize(mb_strimwidth($r['content'], 0, 150, '...')) ?>
                    </p>
                    <div style="display:flex; gap:12px; color:#aaa; font-size:0.8rem;">
                        <span><i class="fas fa-user"></i> <?= sanitize($r['author']) ?></span>
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                    </div>
                </div>
                <div style="display:flex; gap:5px; flex-shrink:0;">
                    <a href="reports.php?view=<?= $r['id'] ?>" class="btn-icon btn-edit" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="reports.php?export=<?= $r['id'] ?>" class="btn-icon" style="background:rgba(39,174,96,0.1); color:#27ae60;" title="Exporter"><i class="fas fa-download"></i></a>
                    <a href="reports.php?edit=<?= $r['id'] ?>" class="btn-icon btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                    <a href="reports.php?delete=<?= $r['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce rapport ?')"><i class="fas fa-trash"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
