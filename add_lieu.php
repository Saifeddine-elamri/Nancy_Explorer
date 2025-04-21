<?php
require 'includes/db.php';
require 'includes/header.php';

// Initialiser les variables
$errors = [];
$success = '';
$nom = $description = $adresse = $image_url = $type_id = $horaires = '';
$transports = [];
$image_file = null;

// Dossier pour les uploads
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
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

    // Si aucune erreur, insérer dans la base de données
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insérer le lieu
            $sql = "INSERT INTO lieu (nom, description, adresse, image_url, type_id, horaires) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $type_id_value = !empty($type_id) ? $type_id : null;
            $stmt->execute([$nom, $description, $adresse ?: null, $image_url, $type_id_value, $horaires]);
            $lieu_id = $pdo->lastInsertId();

            // Insérer les transports
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
            $success = 'Lieu ajouté avec succès ! <a href="index.php" class="alert-link">Retour à la liste</a>';
            // Réinitialiser les champs
            $nom = $description = $adresse = $image_url = $type_id = $horaires = '';
            $transports = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur lors de l\'ajout du lieu : ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid py-5">
  <!-- Bouton de retour sans animation -->
  <div class="floating-back mb-4">
    <a href="index.php" class="btn btn-custom-no-animation">
      <i class="bi bi-arrow-left"></i> Retour
    </a>
  </div>

  <div class="form-container glass-effect p-4 p-md-5 rounded-4">
    <h2 class="mb-4">Ajouter un nouveau lieu</h2>

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
    <form method="POST" id="add-lieu-form" enctype="multipart/form-data">
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
            <label for="image_file" class="form-label">Ou téléchargez une image</label>
            <input type="file" name="image_file" id="image_file" class="form-control shadow-sm" 
                   accept="image/jpeg,image/png,image/gif">
            <div id="image-preview" class="mt-3">
              <?php if ($image_url): ?>
                <img src="<?= htmlspecialchars($image_url) ?>" alt="Image preview" class="img-fluid">
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Horaires -->
        <div class="col-12">
          <div class="form-group">
            <label for="horaires" class="form-label">Horaires</label>
            <textarea name="horaires" id="horaires" class="form-control shadow-sm" 
                      rows="4" required><?= htmlspecialchars($horaires) ?></textarea>
          </div>
        </div>

        <!-- Transports -->
        <div class="col-12">
          <div class="form-group">
            <label class="form-label">Transports disponibles (optionnel)</label>
            <div class="form-check">
              <?php foreach ($pdo->query("SELECT * FROM transport") as $transport): ?>
                <input class="form-check-input" type="checkbox" name="transports[]" value="<?= $transport['id'] ?>" 
                       id="transport_<?= $transport['id'] ?>" 
                       <?= in_array($transport['id'], $transports) ? 'checked' : '' ?>>
                <label class="form-check-label" for="transport_<?= $transport['id'] ?>">
                  <?= htmlspecialchars($transport['nom']) ?>
                </label>
                <input type="text" class="form-control mt-2" name="transport_details[<?= $transport['id'] ?>]" 
                       value="<?= htmlspecialchars($transport_details[$transport['id']] ?? '') ?>" placeholder="Détails supplémentaires">
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Ajouter le lieu</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Code CSS -->
<style>
/* Nouveau bouton de retour sans animation */
.btn-custom-no-animation {
  background: linear-gradient(45deg, #2d3436, #636e72);
  color: white;
  border-radius: 30px;
  padding: 12px 24px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.btn-custom-no-animation:hover {
  background: linear-gradient(45deg, #636e72, #2d3436);
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.floating-back {
  position: sticky;
  top: 80px;
  z-index: 1000;
}

.floating-back .btn-custom-no-animation {
  transition: background 0.3s ease, box-shadow 0.3s ease;
}
</style>