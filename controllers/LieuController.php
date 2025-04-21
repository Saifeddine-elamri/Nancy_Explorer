<?php
// controllers/LieuController.php

require_once BASE_PATH . '/models/TransportModel.php';
require_once __DIR__ . '/../models/Lieu.php';

class LieuController {
    private $lieuModel;

    public function __construct($pdo) {
        $this->lieuModel = new Lieu($pdo);
    }

    public function index() {
        // Get filters from $_GET
        $filters = $_GET;

        // Fetch data from the model
        $lieux = $this->lieuModel->getLieux($filters);
        $types = $this->lieuModel->getTypes(); // Fetch types for dropdown
        $suggestions = $this->lieuModel->getSearchSuggestions(); // Fetch search suggestions

        // Load the view with data
        require __DIR__ . '/../views/lieu/index.php';
    }

    // controllers/LieuController.php

    public function ajouter() {
        $errors = [];
        $success = '';
        $nom = $description = $adresse = $image_url = $type_id = $horaires = '';
        $transports = [];
        $transport_details = [];

        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $type_id = trim($_POST['type_id'] ?? '');
            $horaires = trim($_POST['horaires'] ?? '');
            $transports = $_POST['transports'] ?? [];
            $transport_details = $_POST['transport_details'] ?? [];

            // Image upload
            if (!empty($_FILES['image_file']['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024;
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

            // Validation
            if (empty($nom)) $errors[] = 'Le nom du lieu est requis.';
            if (empty($description)) $errors[] = 'La description est requise.';
            if (empty($image_url)) $errors[] = 'Une image (URL ou fichier) est requise.';
            if (empty($horaires)) $errors[] = 'Les horaires sont requis.';
            if (!empty($type_id) && !is_numeric($type_id)) $errors[] = 'Type de lieu invalide.';
            foreach ($transports as $tid) {
                if (!is_numeric($tid)) {
                    $errors[] = 'Type de transport invalide.';
                    break;
                }
            }

            // Insertion si tout est OK
            if (empty($errors)) {
                try {
                    $this->pdo->beginTransaction();

                    $stmt = $this->pdo->prepare("INSERT INTO lieu (nom, description, adresse, image_url, type_id, horaires) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $nom,
                        $description,
                        $adresse ?: null,
                        $image_url,
                        !empty($type_id) ? $type_id : null,
                        $horaires
                    ]);
                    $lieu_id = $this->pdo->lastInsertId();

                    if (!empty($transports)) {
                        $stmtTransport = $this->pdo->prepare("INSERT INTO transport_lieu (lieu_id, transport_id, details) VALUES (?, ?, ?)");
                        foreach ($transports as $tid) {
                            $details = trim($transport_details[$tid] ?? '');
                            $stmtTransport->execute([$lieu_id, $tid, $details ?: null]);
                        }
                    }

                    $this->pdo->commit();
                    $success = 'Lieu ajouté avec succès ! <a href="/index.php" class="alert-link">Retour à la liste</a>';
                    $nom = $description = $adresse = $image_url = $type_id = $horaires = '';
                    $transports = [];
                } catch (PDOException $e) {
                    $this->pdo->rollBack();
                    $errors[] = 'Erreur lors de l\'ajout du lieu : ' . $e->getMessage();
                }
            }
        }

        // Fetch types and transports for the form
        $types = $this->lieuModel->getTypes();
        $transports_disponibles = $this->lieuModel->getTransports();

        // Charger la vue
        require BASE_PATH . '/views/lieu/add_lieu.php';
    }

    public function show($id) {
        $lieu = $this->lieuModel->getLieuById($id);
    
        if (!$lieu) {
            http_response_code(404);
            echo "Lieu introuvable";
            return;
        }
    
        // Optionnel : charger les transports, etc.
    
        require __DIR__ . '/../views/lieu/show.php';
    }
    



}




