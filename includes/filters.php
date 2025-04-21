<div class="filters glass-effect p-4 mb-5 rounded-4 animate__animated animate__fadeInDown">
  <div class="row g-3 align-items-center">
    <div class="col-md-6 col-lg-4">
      <form method="GET" id="type-form">
        <div class="input-group">
          <span class="input-group-text bg-transparent border-0">
            <i class="bi bi-tags-fill filter-icon"></i>
          </span>
          <select name="type" class="form-select shadow-sm" onchange="this.form.submit()">
            <option value="">🌍 Tous les lieux</option>
            <?php foreach ($pdo->query("SELECT * FROM type_lieu") as $type): ?>
              <option value="<?= $type['id'] ?>" <?= ($_GET['type'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($type['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <div class="col-md-6 col-lg-4">
      <form method="GET" id="search-form" class="search-wrapper">
        <div class="input-group">
          <span class="input-group-text bg-transparent border-0">
            <i class="bi bi-search search-icon"></i>
          </span>
          <input type="text" name="search" class="form-control shadow-sm" 
                 placeholder="Rechercher un lieu..." 
                 value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                 list="suggestions"
                 autocomplete="off">
          <datalist id="suggestions">
            <?php
            $suggestions = $pdo->query("SELECT DISTINCT nom FROM lieu LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($suggestions as $suggestion): ?>
              <option value="<?= htmlspecialchars($suggestion) ?>">
            <?php endforeach; ?>
          </datalist>
          <button type="submit" class="btn btn-search">
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>

    <div class="col-lg-4 text-lg-end">
      <a href="index.php" class="btn btn-reset shadow-sm ripple-effect me-2">
        <i class="bi bi-arrow-counterclockwise me-2"></i>Réinitialiser
      </a>
      <a href="add_lieu.php" class="btn btn-add shadow-sm ripple-effect">
        <i class="bi bi-plus-circle-fill me-2"></i>Ajouter un lieu
      </a>
    </div>
  </div>
</div>