<?php
require_once '../include/config.php'; 

// Session & récupération messages 
session_start();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$error_from_action = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Initialisation listes & messages d'erreur
$messages_list = [];
$paiements_list = [];
$error_message = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // REQUÊTE POUR MESSAGES 
    $sql_messages = "SELECT m.id, m.sujet, m.message, TO_CHAR(m.date_envoi, 'DD/MM/YYYY à HH24:MI') 
    as date_formatee, m.lu, u.first_name AS prenom, u.last_name 
    AS nom, u.email FROM public.messages m JOIN public.users u ON m.user_id = u.id ORDER BY m.date_envoi DESC";
    $stmt_messages = $conn->query($sql_messages);
    $messages_list = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

    // REQUÊTE POUR PAIEMENTS
    $sql_paiements = "
        SELECT p.id, u.email AS email_utilisateur, c.titre AS titre_cours, p.reference_transaction, p.numero_telephone, p.statut, p.date_paiement
        FROM public.paiements p
        JOIN public.users u ON p.id_utilisateur = u.id
        JOIN public.cours c ON p.id_cours = c.id
        ORDER BY p.date_paiement DESC
    ";
    $stmt_paiements = $conn->query($sql_paiements);
    $paiements_list = $stmt_paiements->fetchAll(PDO::FETCH_ASSOC);


} catch(PDOException $e) { 
    $error_message = "Erreur de base de données : " . $e->getMessage(); 
} finally { 
    $conn = null; 
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/Tailwind.js"></script>
    <link rel="stylesheet" href="../assets/css/all.min.css"> 
    <link rel="stylesheet" href="../assets/css/index.css"> 
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">

<div class="flex w-full min-h-screen">
    <aside class="w-[260px] bg-white p-6 border-r text-primary-local flex-col shrink-0 hidden sm:flex">
        <div class="text-2xl font-bold text-primary-local text-center mb-12 flex items-center justify-center gap-2">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Admin</span>
        </div>
        <nav>
            <ul class="space-y-2">
                <li>
                    <a href="admin_page_dashboard.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="dashboard">
                    <i class="fa-solid fa-table-columns w-5 text-center mr-4"></i> Dashboard</a>
                </li>
                <li>
                    <a href="admin_page_formation.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="formation">
                    <i class="fa-solid fa-layer-group w-5 text-center mr-4"></i> Formation</a>
                </li>
                <li><a href="admin_page_paiement.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="users">
                    <i class="fa-solid fa-credit-card w-5 text-center mr-4"></i> Paiements</a>
                </li>
                <li><a href="#" class="nav-link flex items-center font-bold py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="messages">
                    <i class="fa-solid fa-envelope w-5 text-center mr-4"></i> Messages</a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-grow p-6 sm:p-10">

        
        <!-- PAGE: MESSAGES -->
        <div id="messages" class="page hidden">
             <h1 class="text-4xl font-bold text-primary-local">Messagerie</h1>
             <div class="mt-8">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <p><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <?php elseif (empty($messages_list)): ?>
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg text-center">
                        <p>Vous n'avez aucun message pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="divide-y divide-slate-200">
                            <?php foreach ($messages_list as $msg): ?>
                            <div class="p-6 hover:bg-slate-50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <?php if (!$msg['lu']): ?><span class="w-3 h-3 bg-primary-local rounded-full" title="Non lu"></span><?php endif; ?>
                                            <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($msg['sujet']); ?></h3>
                                        </div>
                                        <p class="text-sm text-secondary">De : <span class="font-semibold text-slate-700">
                                            <?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?></span> (<?php echo htmlspecialchars($msg['email']); ?>)</p>
                                    </div>
                                    <div class="text-right text-xs text-slate-500 shrink-0 ml-4">
                                        <?php echo $msg['date_formatee']; ?></div>
                                </div>
                                <div class="mt-4 pl-6 border-l-2 border-slate-300">
                                    <p class="text-slate-700 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p></div>
                                <div class="text-right mt-4">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=RE: <?php echo htmlspecialchars($msg['sujet']); ?>" class="text-sm bg-primary-local text-white font-medium px-4 py-2 rounded-lg hover:bg-primary-hover transition-colors">
                                        <i class="fa-solid fa-reply mr-2"></i> Répondre par email</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
             </div>
        </div>
    </main>
</div>

<script src="../assets/js/admin_scripts.js"></script>
</body>
</html>