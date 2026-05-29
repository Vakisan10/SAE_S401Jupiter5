<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib-tools/Auth/User.php';

// Charger le .env avec phpdotenv (createUnsafeImmutable pour que getenv() fonctionne)
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$config = require __DIR__ . '/../config/app.php';

if ($config['env'] === 'development') {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('Europe/Paris');

if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session']['name']);
    session_start();
}

require_once __DIR__ . '/../lib-tools/Core/Router.php';
require_once __DIR__ . '/../lib-tools/Helpers/helpers.php';
require_once __DIR__ . '/../lib-tools/Auth/User.php';
require_once __DIR__ . '/../lib-tools/Auth/CasUser.php';
require_once __DIR__ . '/../lib-tools/Auth/CasConfiguration.php';
require_once __DIR__ . '/../lib-tools/Auth/CasAuthenticator.php';
require_once __DIR__ . '/../lib-tools/Auth/AuthMiddleware.php';
require_once __DIR__ . '/../lib-tools/Auth/AuthorizationMiddleware.php';

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/dao/UserRepository.php';

require_once __DIR__ . '/controllers/PostalIutController.php';
require_once __DIR__ . '/controllers/PostalUnivController.php';
require_once __DIR__ . '/controllers/DepartementController.php';
require_once __DIR__ . '/controllers/FinanceController.php';
require_once __DIR__ . '/controllers/DirecteurController.php';
require_once __DIR__ . '/controllers/AdminController.php';

$publicRoutes = ['/', '/dev-login', '/login', '/logout'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$currentUser = null;

// Handle auth pages routing
if ($uri === '/dev-login' || $uri === '/dev-login.php') {
    require_once __DIR__ . '/../lib-tools/pages/dev-login.php';
    exit;
}

if ($uri === '/logout' || $uri === '/logout.php') {
    require_once __DIR__ . '/../lib-tools/pages/logout.php';
    exit;
}

if ($uri === '/login' || $uri === '/login.php') {
    require_once __DIR__ . '/../lib-tools/pages/login.php';
    exit;
}

if (!in_array($uri, $publicRoutes)) {
    if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
        if ($config['env'] === 'development') {
            header('Location: /dev-login');
            exit;
        } else {
            $casConfig = CasConfiguration::fromArray($config['cas'], $config['base_url']);
            $casAuth = new CasAuthenticator($casConfig);
            $casUser = $casAuth->authenticate();

            $user = UserRepository::findByUidCas($casUser->getLogin());
            if (!$user) {
                $role = in_array($casUser->getLogin(), $config['admin_uids'] ?? []) ? 'admin' : 'departement';
                $user = UserRepository::create($casUser->getLogin(), $casUser->getAttributes(), $role);
            }

            $_SESSION['user'] = $user;
            $_SESSION['authenticated'] = true;
            $currentUser = $user;
        }
    } else {
        $currentUser = $_SESSION['user'];
    }
}

$router = new Router();

// Route racine : redirige vers le dashboard approprié selon le rôle
$router->get('/', function() use ($currentUser, $config) {
    if (!$currentUser) {
        // Redirige vers login (qui gère dev vs prod)
        header('Location: /login');
        exit;
    }
    $redirects = [
        'admin' => '/admin/dashboard',
        'postal_iut' => '/postal/dashboard',
        'postal_univ' => '/postal-univ/dashboard',
        'finance' => '/finance/dashboard',
        'directeur' => '/directeur/dashboard',
        'departement' => '/departement/dashboard',
    ];
    $url = $redirects[$currentUser->getRole()] ?? '/postal/dashboard';
    header('Location: ' . $url);
    exit;
}, null);

$router->get('/postal/dashboard', 'PostalIutController', 'dashboard');
$router->get('/postal', 'PostalIutController', 'dashboard');

$router->get('/postal/colis/recus', 'PostalIutController', 'colisRecus');
$router->get('/postal/colis/remis', 'PostalIutController', 'colisRemis');
$router->get('/postal/colis/attente', 'PostalIutController', 'colisEnAttente');
$router->get('/postal/colis/details', 'PostalIutController', 'detailsColis');
$router->get('/postal/colis/details/:id', 'PostalIutController', 'detailsColis');

$router->get('/postal/colis/ajouter', 'PostalIutController', 'ajouterColis');
$router->post('/postal/colis/ajouter', 'PostalIutController', 'ajouterColis');

$router->get('/postal/colis/modifier', 'PostalIutController', 'modifierColis');
$router->get('/postal/colis/modifier/:id', 'PostalIutController', 'modifierColis');
$router->post('/postal/colis/update', 'PostalIutController', 'updateColis');

$router->get('/postal/colis/retirer', 'PostalIutController', 'retirerColis');
$router->get('/postal/colis/retirer/:id', 'PostalIutController', 'retirerColis');

$router->get('/postal/colis/recherche', 'PostalIutController', 'rechercheColis');
$router->get('/postal/colis/non-identifies', 'PostalIutController', 'colisNonIdentifies');

$router->get('/postal/confirmation', 'PostalIutController', 'confirmation');
$router->get('/postal/confirmer', 'PostalIutController', 'confirmerColis');

$router->get('/postal/historique', 'PostalIutController', 'historiqueGlobal');

// ===== POSTAL UNIV =====
$router->get('/postal-univ', 'PostalUnivController', 'dashboard');
$router->get('/postal-univ/dashboard', 'PostalUnivController', 'dashboard');
$router->get('/postal-univ/reception', 'PostalUnivController', 'receptionColis');
$router->post('/postal-univ/reception', 'PostalUnivController', 'receptionColis');
$router->get('/postal-univ/colis', 'PostalUnivController', 'listeColis');
$router->get('/postal-univ/transferer', 'PostalUnivController', 'transfererColis');
$router->get('/postal-univ/non-identifies', 'PostalUnivController', 'nonIdentifies');
$router->get('/postal-univ/historique', 'PostalUnivController', 'historique');

// ===== DEPARTEMENT =====
$router->get('/departement', 'DepartementController', 'dashboard');
$router->get('/departement/dashboard', 'DepartementController', 'dashboard');
$router->get('/departement/creer-devis', 'DepartementController', 'creerDevis');
$router->post('/departement/envoyer-devis', 'DepartementController', 'envoyerDevis');
$router->get('/departement/mes-devis', 'DepartementController', 'mesDevis');
$router->get('/departement/bons-commande', 'DepartementController', 'mesBonsCommande');
$router->get('/departement/mes-colis', 'DepartementController', 'mesColis');
$router->get('/departement/budget', 'DepartementController', 'budget');
$router->get('/departement/fournisseurs', 'DepartementController', 'fournisseurs');

// ===== FINANCE =====
$router->get('/finance', 'FinanceController', 'dashboard');
$router->get('/finance/dashboard', 'FinanceController', 'dashboard');
$router->get('/finance/valider-devis', 'FinanceController', 'validerDevis');
$router->get('/finance/rejeter-devis', 'FinanceController', 'rejeterDevis');
$router->get('/finance/devis', 'FinanceController', 'devisAVerifier');
$router->get('/finance/voir-devis', 'FinanceController', 'voirDevis');
$router->get('/finance/bons-commande', 'FinanceController', 'bonsCommande');
$router->get('/finance/budgets', 'FinanceController', 'budgets');

// ===== DIRECTEUR IUT =====
$router->get('/directeur', 'DirecteurController', 'dashboard');
$router->get('/directeur/dashboard', 'DirecteurController', 'dashboard');
$router->get('/directeur/signer-devis', 'DirecteurController', 'signerDevis');
$router->get('/directeur/devis', 'DirecteurController', 'devisASigner');
$router->get('/directeur/bons-commande', 'DirecteurController', 'bonCommande');
$router->get('/directeur/voir-devis', 'DirecteurController', 'voirDevis');

// ===== ADMIN =====
$router->get('/admin', 'AdminController', 'dashboard');
$router->get('/admin/dashboard', 'AdminController', 'dashboard');
$router->get('/admin/utilisateurs', 'AdminController', 'utilisateurs');
$router->post('/admin/update-utilisateur', 'AdminController', 'updateUtilisateur');
$router->get('/admin/fournisseurs', 'AdminController', 'fournisseurs');
$router->post('/admin/ajouter-fournisseur', 'AdminController', 'ajouterFournisseur');
$router->get('/admin/modifier-fournisseur', 'AdminController', 'modifierFournisseur');
$router->post('/admin/update-fournisseur', 'AdminController', 'updateFournisseur');
$router->get('/admin/departements', 'AdminController', 'departements');
$router->post('/admin/ajouter-departement', 'AdminController', 'ajouterDepartement');
$router->get('/admin/modifier-departement', 'AdminController', 'modifierDepartement');
$router->post('/admin/update-departement', 'AdminController', 'updateDepartement');
$router->get('/admin/devis', 'AdminController', 'devis');
$router->get('/admin/commandes', 'AdminController', 'commandes');
$router->get('/admin/colis', 'AdminController', 'colis');


try {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($currentUser && !in_array($uri, $publicRoutes)) {
        if (!AuthorizationMiddleware::check($uri, $currentUser)) {
            http_response_code(403);
            require_once __DIR__ . '/../lib-tools/pages/errors/403.php';
            exit;
        }
    }

    [$controllerClass, $methodName, $params] = $router->dispatch($method, $uri);

    if (is_callable($controllerClass)) {
        $controllerClass();
    } else {
        $controller = new $controllerClass();
        call_user_func_array([$controller, $methodName], $params);
    }

} catch (\Exception $e) {
    $code = $e->getCode();

    if ($code == 404) {
        http_response_code(404);
        require_once __DIR__ . '/../lib-tools/pages/errors/404.php';
    } elseif ($code == 403) {
        http_response_code(403);
        require_once __DIR__ . '/../lib-tools/pages/errors/403.php';
    } else {
        http_response_code(500);
        $errorMessage = $e->getMessage();
        $errorTrace = $e->getTraceAsString();
        require_once __DIR__ . '/../lib-tools/pages/errors/500.php';
    }
}
