<?php
require 'includes/db.php';
require 'includes/header.php';

// Initialiser les variables
$errors = [];
$success = '';
$lieu_id = null;
$nom = $description = $adresse = $image_url = $type_id = $horaires = '';
$transports = [];
$transport_details = [];
$image_file = null;

// Dossier pour les uploads
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Vérifier si l'ID du lieu est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-error-custom animate__animated animate__fadeIn" role="alert">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 16v.01M12 12v-4"/>
            </svg>
            ID de lieu invalide. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
          </div>';
    require 'includes/footer.php';
    exit;
}

$lieu_id = (int)$_GET['id'];

// Charger les données du lieu
$sql = "SELECT * FROM lieu WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$lieu_id]);
$lieu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lieu) {
    echo '<div class="alert alert-error-custom animate__animated animate__fadeIn" role="alert">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 16v.01M12 12v-4"/>
            </svg>
            Lieu non trouvé. <a href="index.php" class="alert-link">Retour à l\'accueil</a>
          </div>';
    require 'includes/footer.php';
    exit;
}

// Pré-remplir les champs
$nom = $lieu['nom'];
$description = $lieu['description'];
$adresse = $lieu['adresse'] ?? '';
$image_url = $lieu['image_url'] ?? '';
$type_id = $lieu['type_id'] ?? '';
$horaires = $lieu['horaires'] ?? '';

// Charger les transports associés
$transportStmt = $pdo->prepare("
    SELECT t.id, t.nom, t.icone, tl.details 
    FROM transport t 
    LEFT JOIN transport_lieu tl ON t.id = tl.transport_id AND tl.lieu_id = ?
");
$transportStmt->execute([$lieu_id]);
$allTransports = $transportStmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($allTransports as $transport) {
    if ($transport['details'] !== null) {
        $transports[] = $transport['id'];
        $transport_details[$transport['id']] = $transport['details'];
    }
}

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $image_url = trim($_POST['image_url'] ?? $lieu['image_url']);
    $type_id = trim($_POST['type_id'] ?? '');
    $horaires = trim($_POST['horaires'] ?? '');
    $transports = isset($_POST['transports']) && is_array($_POST['transports']) ? $_POST['transports'] : [];
    $transport_details = isset($_POST['transport_details']) && is_array($_POST['transport_details']) ? $_POST['transport_details'] : [];

    // Gestion de l'image uploadée
    if (!empty($_FILES['image_file']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file = $_FILES['image_file'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = 'Type de fichier non autorisé (JPEG, PNG, GIF uniquement).';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'Le fichier dépasse la taille maximale de 5 Mo.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . $ext;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Supprimer l'ancienne image si elle existe et n'est pas une URL externe
                    if ($lieu['image_url'] && file_exists($lieu['image_url']) && strpos($lieu['image_url'], 'http') !== 0) {
                        unlink($lieu['image_url']);
                    }
                    $image_url = $destination;
                } else {
                    $errors[] = 'Erreur lors du téléchargement de l\'image.';
                }
            }
        }
    }

    // Validation des champs
    if (empty($nom)) {
        $errors[] = 'Le nom du lieu est requis.';
    }
    if (empty($description)) {
        $errors[] = 'La description est requise.';
    }
    if (empty($image_url)) {
        $errors[] = 'Une image (URL ou fichier) est requise.';
    }
    if (empty($horaires)) {
        $errors[] = 'Les horaires sont requis.';
    }
    if (!empty($type_id) && !is_numeric($type_id)) {
        $errors[] = 'Type de lieu invalide.';
    }
    foreach ($transports as $tid) {
        if (!is_numeric($tid)) {
            $errors[] = 'Type de transport invalide.';
            break;
        }
    }

    // Si aucune erreur, mettre à jour la base de données
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Mettre à jour le lieu
            $sql = "UPDATE lieu SET nom = ?, description = ?, adresse = ?, image_url = ?, type_id = ?, horaires = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $type_id_value = !empty($type_id) ? $type_id : null;
            $adresse_value = !empty($adresse) ? $adresse : null;
            $stmt->execute([$nom, $description, $adresse_value, $image_url, $type_id_value, $horaires, $lieu_id]);

            // Supprimer les anciens transports
            $deleteStmt = $pdo->prepare("DELETE FROM transport_lieu WHERE lieu_id = ?");
            $deleteStmt->execute([$lieu_id]);

            // Insérer les nouveaux transports
            if (!empty($transports)) {
                $transportStmt = $pdo->prepare("
                    INSERT INTO transport_lieu (lieu_id, transport_id, details) 
                    VALUES (?, ?, ?)
                ");
                foreach ($transports as $tid) {
                    $details = trim($transport_details[$tid] ?? '');
                    $transportStmt->execute([$lieu_id, $tid, $details ?: null]);
                }
            }

            $pdo->commit();
            $success = 'Lieu modifié avec succès ! <a href="index.php" class="alert-link">Retour à la liste</a>';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de la modification du lieu : ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid py-5">
  <!-- Bouton de retour -->
  <div class="floating-back mb-4">
    <a href="index.php" class="btn btn-custom btn-floating animate__animated animate__pulse animate__infinite">
      <i class="bi bi-arrow-left"></i> Retour
    </a>
  </div>

  <div class="form-container glass-effect p-4 p-md-5 rounded-4 animate__animated animate__fadeInUp">
    <h2 class="mb-4">Modifier le lieu : <?= htmlspecialchars($lieu['nom']) ?></h2>

    <!-- Messages d'erreur ou de succès -->
    <?php if ($success): ?>
      <div class="alert alert-success-custom animate__animated animate__fadeIn" role="alert">
        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M20 6L9 17l-5-5" stroke-width="2"/>
        </svg>
        <?= $success ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error-custom animate__animated animate__fadeIn" role="alert">
        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 16v.01M12 12v-4"/>
        </svg>
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="POST" id="edit-lieu-form" enctype="multipart/form-data">
      <div class="row g-4">
        <!-- Nom -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="nom" class="form-label">Nom du lieu</label>
            <input type="text" name="nom" id="nom" class="form-control shadow-sm" 
                   value="<?= htmlspecialchars($nom) ?>" required>
          </div>
        </div>

        <!-- Type -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="type_id" class="form-label">Type de lieu (optionnel)</label>
            <select name="type_id" id="type_id" class="form-select shadow-sm">
              <option value="">Aucun type</option>
              <?php foreach ($pdo->query("SELECT * FROM type_lieu") as $type): ?>
                <option value="<?= $type['id'] ?>" <?= $type_id == $type['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($type['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Description -->
        <div class="col-12">
          <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control shadow-sm" 
                      rows="4" required><?= htmlspecialchars($description) ?></textarea>
          </div>
        </div>

        <!-- Adresse -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="adresse" class="form-label">Adresse (optionnel)</label>
            <input type="text" name="adresse" id="adresse" class="form-control shadow-sm" 
                   value="<?= htmlspecialchars($adresse) ?>">
          </div>
        </div>

        <!-- Image -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="image_url" class="form-label">URL de l'image (optionnel si fichier)</label>
            <input type="url" name="image_url" id="image_url" class="form-control shadow-sm" 
                   value="<?= htmlspecialchars($image_url) ?>">
          </div>
          <div class="form-group mt-3">
            <label for="image_file" class="form-label">Ou téléchargez une nouvelle image</label>
            <input type="file" name="image_file" id="image_file" class="form-control shadow-sm" 
                   accept="image/jpeg,image/png,image/gif">
            <div id="image-preview" class="mt-3">
              <?php if ($image_url): ?>
                <img src="<?= htmlspecialchars($image_url) ?>" alt="Prévisualisation" 
                     class="img-fluid rounded shadow-sm" style="max-height: 200px;">
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Transports -->
        <div class="col-12">
          <div class="form-group">
            <label class="form-label">Transports disponibles (optionnel)</label>
            <div class="transport-options">
              <?php foreach ($allTransports as $transport): ?>
                <div class="transport-item mb-3">
                  <div class="form-check">
                    <input type="checkbox" name="transports[]" 
                           id="transport_<?= $transport['id'] ?>" 
                           value="<?= $transport['id'] ?>" 
                           class="form-check-input" 
                           <?= in_array($transport['id'], $transports) ? 'checked' : '' ?>>
                    <label for="transport_<?= $transport['id'] ?>" class="form-check-label">
                      <i class="bi <?= htmlspecialchars($transport['icone']) ?> me-1"></i>
                      <?= htmlspecialchars($transport['nom']) ?>
                    </label>
                  </div>
                  <input type="text" name="transport_details[<?= $transport['id'] ?>]" 
                         class="form-control shadow-sm mt-2 transport-details" 
                         placeholder="Détails (ex: Ligne 1, Station République)" 
                         value="<?= htmlspecialchars($transport_details[$transport['id']] ?? '') ?>" 
                         style="display: <?= in_array($transport['id'], $transports) ? 'block' : 'none' ?>;">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Horaires -->
        <div class="col-12">
          <div class="form-group">
            <label for="horaires" class="form-label">Horaires (un par ligne)</label>
            <textarea name="horaires" id="horaires" class="form-control shadow-sm" 
                      rows="5" placeholder="Ex: Lundi-Vendredi: 9h-18h
Samedi: 10h-14h
Dimanche: Fermé" 
                      required><?= htmlspecialchars($horaires) ?></textarea>
          </div>
        </div>

        <!-- Boutons -->
        <div class="col-12 text-end">
          <button type="reset" class="btn btn-reset me-2">Réinitialiser</button>
          <button type="submit" class="btn btn-submit ripple-effect">
            <i class="bi bi-check-circle-fill me-2"></i>Sauvegarder
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Inclure Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Inclure Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;800&display=swap');

/* Style global */
body {
  background: radial-gradient(circle at 30% 20%, #e9ecef 0%, #dfe6e9 50%, #b2bec3 100%);
  font-family: 'Manrope', sans-serif;
  color: #1a1a1a;
  animation: gradientPulse 30s ease infinite;
  overflow-x: hidden;
}

.dark-mode body {
  background: radial-gradient(circle at 30% 20%, #2d3436 0%, #1e272e 50%, #0a0e17 100%);
  color: #f1f3f5;
}

@keyframes gradientPulse {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.container-fluid {
  max-width: 1600px;
}

/* Bouton de retour */
.floating-back {
  position: sticky;
  top: 80px;
  z-index: 1000;
}

.btn-custom {
  background: linear-gradient(45deg, #2d3436, #636e72);
  color: white;
  border-radius: 30px;
  padding: 12px 24px;
  transition: all 0.4s ease;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.dark-mode .btn-custom {
  background: linear-gradient(45deg, #636e72, #adb5bd);
}

.btn-custom:hover {
  background: linear-gradient(45deg, #636e72, #2d3436);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.dark-mode .btn-custom:hover {
  background: linear-gradient(45deg, #adb5bd, #636e72);
}

.btn-floating {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Formulaire */
.form-container {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 20px 70px rgba(0, 0, 0, 0.25);
  position: relative;
  overflow: hidden;
}

.dark-mode .form-container {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-container::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
  animation: glow 10s ease infinite;
}

@keyframes glow {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

h2 {
  font-weight: 800;
  font-size: 2.2rem;
  background: linear-gradient(45deg, #2d3436, #00b894);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 2px 10px rgba(0, 184, 148, 0.3);
}

.dark-mode h2 {
  background: linear-gradient(45deg, #f1f3f5, #ff9f43);
}

.form-group {
  margin-bottom: 2rem;
}

.form-label {
  font-weight: 700;
  font-size: 1.15rem;
  color: #2d3436;
  margin-bottom: 10px;
  display: block;
  transition: color 0.3s ease;
}

.dark-mode .form-label {
  color: #f1f3f5;
}

.form-control:focus + .form-label,
.form-select:focus + .form-label {
  color: #00b894;
}

.dark-mode .form-control:focus + .form-label,
.dark-mode .form-select:focus + .form-label {
  color: #ff9f43;
}

.form-control, .form-select {
  border-radius: 15px;
  border: none;
  background: rgba(255, 255, 255, 0.9);
  padding: 14px 18px;
  font-size: 1rem;
  transition: all 0.4s ease;
  position: relative;
  z-index: 1;
}

.dark-mode .form-control, .dark-mode .form-select {
  background: rgba(255, 255, 255, 0.2);
  color: #f1f3f5;
}

.form-control:focus, .form-select:focus {
  background: white;
  box-shadow: 0 0 15px rgba(0, 184, 148, 0.5);
  transform: translateY(-2px);
}

.dark-mode .form-control:focus, .dark-mode .form-select:focus {
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 0 15px rgba(255, 159, 67, 0.5);
}

.form-control:invalid:not(:focus), .form-select:invalid:not(:focus) {
  box-shadow: 0 0 5px rgba(255, 107, 107, 0.5);
}

/* Transports */
.transport-options {
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  padding: 15px;
}

.dark-mode .transport-options {
  background: rgba(255, 255, 255, 0.1);
}

.transport-item {
  transition: all 0.3s ease;
}

.transport-item:hover {
  transform: translateX(5px);
}

.form-check-input {
  margin-right: 10px;
}

.form-check-label {
  font-size: 1rem;
  font-weight: 500;
  color: #2d3436;
  cursor: pointer;
}

.dark-mode .form-check-label {
  color: #f1f3f5;
}

.transport-details {
  border-radius: 10px;
  padding: 10px;
  font-size: 0.95rem;
}

/* Prévisualisation de l'image */
#image-preview img {
  transition: all 0.5s ease;
  opacity: 0.95;
  border: 2px solid rgba(0, 184, 148, 0.2);
}

.dark-mode #image-preview img {
  border: 2px solid rgba(255, 159, 67, 0.2);
}

#image-preview img:hover {
  opacity: 1;
  transform: scale(1.02);
}

/* Boutons */
.btn-submit, .btn-reset {
  border-radius: 30px;
  padding: 14px 28px;
  font-weight: 600;
  font-size: 1.1rem;
  position: relative;
  overflow: hidden;
  transition: all 0.4s ease;
}

.btn-submit {
  background: linear-gradient(45deg, #0984e3, #00b894);
  color: white;
}

.btn-reset {
  background: linear-gradient(45deg, #ff6b6b, #ff9f43);
  color: white;
}

.btn-submit:hover, .btn-reset:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.btn-submit::before, .btn-reset::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: width 0.6s ease, height 0.6s ease;
}

.btn-submit:hover::before, .btn-reset:hover::before {
  width: 300px;
  height: 300px;
}

.ripple-effect::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: ripple 0.6s ease-out;
}

@keyframes ripple {
  to { width: 200px; height: 200px; opacity: 0; }
}

/* Alertes */
.alert-success-custom, .alert-error-custom {
  border-radius: 16px;
  padding: 25px;
  color: white;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  gap: 15px;
}

.alert-success-custom {
  background: linear-gradient(45deg, #00b894, #0984e3);
}

.alert-error-custom {
  background: linear-gradient(45deg, #ff3f74, #ff9f43);
}

.alert-icon {
  width: 30px;
  height: 30px;
  stroke-width: 2;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); }
  100% { transform: scale(1); }
}

.alert-link {
  color: #74b9ff;
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .form-container {
    padding: 2rem;
  }

  h2 {
    font-size: 1.9rem;
  }

  .btn-submit, .btn-reset {
    width: 100%;
    margin-bottom: 10px;
  }

  .transport-item {
    margin-bottom: 1.5rem;
  }
}

@media (max-width: 576px) {
  .floating-back {
    position: static;
    margin-bottom: 20px;
  }

  .form-control, .form-select {
    font-size: 0.9rem;
    padding: 12px;
  }

  .form-label {
    font-size: 1rem;
  }
}
</style>

<script>
// Prévisualisation de l'image
function previewImage() {
    const urlInput = document.getElementById('image_url');
    const fileInput = document.getElementById('image_file');
    const preview = document.getElementById('image-preview');

    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation" 
                                   class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
        };
        reader.readAsDataURL(fileInput.files[0]);
    } else if (urlInput.value.trim() && /^https?:\/\//.test(urlInput.value)) {
        preview.innerHTML = `<img src="${urlInput.value}" alt="Prévisualisation" 
                               class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
    } else {
        preview.innerHTML = '';
    }
}

document.getElementById('image_url')?.addEventListener('input', previewImage);
document.getElementById('image_file')?.addEventListener('change', previewImage);

// Afficher/masquer les champs de détails des transports
document.querySelectorAll('.form-check-input[name="transports[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const detailsInput = this.closest('.transport-item').querySelector('.transport-details');
        detailsInput.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            detailsInput.value = '';
        }
    });
});

// Vibration au clic des boutons
document.querySelectorAll('.btn-submit, .btn-reset').forEach(btn => {
    btn.addEventListener('click', function() {
        this.style.animation = 'vibrate 0.1s linear';
        setTimeout(() => this.style.animation = '', 100);
    });
});

@keyframes vibrate {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

// Validation côté client
document.getElementById('edit-lieu-form')?.addEventListener('submit', function(e) {
    const imageUrl = document.getElementById('image_url').value.trim();
    const imageFile = document.getElementById('image_file').files.length;
    if (!imageUrl && !imageFile) {
        e.preventDefault();
        alert('Veuillez fournir une URL d\'image ou un fichier.');
    }
});
</script>

<?php require 'includes/footer.php'; ?>