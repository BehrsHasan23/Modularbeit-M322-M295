<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontakt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css.css">
    <style>
      body {
        background: #F9FFE1;
      }
      .kontakt-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 20px;
      }
      h1 {
        text-align: center;
        margin-bottom: 2rem;
        color: #2c3e50;
      }
    </style>
  </head>
  <body>
   <header class="p-3 text-bg-dark">
      <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
          
 
          <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
            <li><a href="index.php" class="nav-link px-2 text-white">Home</a></li>
            <li><a href="#" class="nav-link px-2 text-secondary">Kontakt</a></li>
            <li><a href="books.php" class="nav-link px-2 text-white">Books</a></li>
            
          </ul>
 
          <div class="text-end">
            <button type="button" class="btn btn-outline-light me-2"><a href="login.php" class="nav-link px-2">Login</a></button>
            <button type="button" class="btn btn-warning"><a href="signup.php" class="nav-link px-2 text-white">Sign-up</a></button>
          </div>
        </div>
      </div>
    </header>

    <div class="kontakt-container">
      <h1>Kontakt</h1>
      
      <form class="row g-3">
        <div class="col-md-6">
          <label for="vorname" class="form-label">Vorname*</label>
          <input type="text" class="form-control" id="vorname" required>
        </div>
        
        <div class="col-md-6">
          <label for="nachname" class="form-label">Nachname*</label>
          <input type="text" class="form-control" id="nachname" required>
        </div>
        
        <div class="col-12">
          <label for="problem" class="form-label">Problem*</label>
          <textarea class="form-control" id="problem" rows="4" required></textarea>
        </div>
        
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary btn-lg">Submit</button>
        </div>
      </form>
    </div>
    <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Spalte 1 -->
            <div class="col-md-4">
                <h5>Über uns</h5>
                <p>Wir lieben Bücher und teilen sie mit der Welt.</p>
            </div>
            <!-- Spalte 2 -->
            <div class="col-md-4">
                <h5>Links</h5>
                <ul class="list-unstyled">
                    <li><a href="ProjektWebsite.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="Kontakt.php" class="text-white text-decoration-none">Kontakt</a></li>
                    <li><a href="books.php" class="text-white text-decoration-none">Bücher</a></li>
                </ul>
            </div>
            <!-- Spalte 3 -->
            <div class="col-md-4">
                <h5>Kontakt</h5>
                <p>Email: info@buchseite.ch</p>
                <p>Telefon: +41 76 456 89</p>
            </div>
        </div>
        <hr class="border-light">
        <p class="mb-0">&copy; 2025 Buchseite. Alle Rechte vorbehalten.</p>
    </div>
</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>