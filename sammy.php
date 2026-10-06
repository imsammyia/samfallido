<?php

session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/planes_helper.php';

$usuarioId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
$planActual = $usuarioId !== null
    ? obtenerPlanUsuario($conn, $usuarioId)
    : null;
$interfazCompletaSammy = $planActual !== null
    && usuarioPuedeUsarInterfazSammy($planActual['code'] ?? 'free');
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$tokenCsrf = obtenerTokenCsrf();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f9ff">
    <title>Sammy — Sam Software</title>
    <link rel="stylesheet" href="sammy.css?v=9">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap">
</head>
<body
    data-chat-enabled="<?= $interfazCompletaSammy ? 'true' : 'false' ?>"
    data-user-name="<?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>"
    data-user-plan="<?= htmlspecialchars($planActual['name'] ?? 'Plan gratuito', ENT_QUOTES, 'UTF-8') ?>"
    data-csrf-token="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
<div class="sammy-sky" aria-hidden="true">
    <span class="sammy-sun"></span>
    <span class="sammy-moon"></span>
    <img src="img/nube.png" alt="" class="bg-cloud c1">
    <img src="img/nube.png" alt="" class="bg-cloud c2">
    <img src="img/nube.png" alt="" class="bg-cloud c3">
    <img src="img/nube.png" alt="" class="bg-cloud c4">
</div>
<div id="season-effects" aria-hidden="true"></div>
<?php if (!$interfazCompletaSammy): ?>
    <main class="access-page">
        <a class="back-link" href="index.php">← Volver a Sam Software</a>
        <section class="access-card">
            <div class="brand-mark access-mark" aria-hidden="true">✦</div>
            <p class="eyebrow">ASISTENTE DE SAM SOFTWARE</p>
            <h1>Conoce a <span>Sammy.</span></h1>
            <?php if ($usuarioId === null): ?>
                <p>Inicia sesión con una cuenta que tenga un plan con acceso al chat para comenzar una conversación.</p>
                <a class="primary-link" href="index.php?plan=login">Iniciar sesión</a>
            <?php elseif (usuarioPuedeUsarChat($planActual['code'] ?? 'free')): ?>
                <p>Tu plan tiene el chatbot clásico de Sammy. Profesional, Semi Empresarial y Empresarial incluyen además esta interfaz completa.</p>
                <a class="primary-link" href="index.php#sammy-presentation">Abrir el chatbot de mi plan</a>
            <?php else: ?>
                <p>El chatbot clásico está incluido desde Demo. Profesional, Semi Empresarial y Empresarial también incluyen esta interfaz completa.</p>
                <a class="primary-link" href="index.php#precios">Ver planes disponibles</a>
            <?php endif; ?>
        </section>
    </main>
<?php else: ?>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="index.php" aria-label="Sam Software, volver al inicio">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span class="brand-name">Sammy<span>.local</span></span>
            </a>

            <div class="workspace">
                <div class="workspace-icon" aria-hidden="true">☁</div>
                <div class="workspace-copy">
                    <strong>Sam Software</strong>
                    <small><?= htmlspecialchars($planActual['name'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <span aria-hidden="true">⌄</span>
            </div>

            <p class="section-label">Espacio de trabajo</p>
            <nav class="nav-list" aria-label="Navegación principal">
                <button class="nav-item active" type="button" data-nav="Inicio">
                    <span aria-hidden="true">⌂</span><span>Inicio</span>
                </button>
                <button class="nav-item" type="button" data-nav="Conversaciones">
                    <span aria-hidden="true">▤</span><span>Conversaciones</span>
                </button>
                <button class="nav-item" type="button" data-nav="Biblioteca">
                    <span aria-hidden="true">▧</span><span>Biblioteca</span>
                </button>
                <button class="nav-item" type="button" data-nav="Explorar">
                    <span aria-hidden="true">✧</span><span>Explorar ideas</span>
                </button>
            </nav>

            <p class="section-label section-label-spaced">Personalización</p>
            <nav class="nav-list" aria-label="Personalización">
                <button class="nav-item" type="button" data-nav="Ajustes">
                    <span aria-hidden="true">⚙</span><span>Ajustes</span>
                </button>
                <button class="nav-item" type="button" data-nav="Ayuda">
                    <span aria-hidden="true">?</span><span>Centro de ayuda</span>
                </button>
            </nav>

            <p class="section-label section-label-spaced">Tus conversaciones</p>
            <div class="sidebar-conversations" id="sidebarConversations" aria-live="polite">
                <p class="conversation-empty">Cargando conversaciones…</p>
            </div>

            <div class="sidebar-spacer"></div>
            <div class="upgrade-card">
                <strong>Sammy, siempre a tu lado</strong>
                <p>Un asistente local para conocer los servicios y planes de Sam Software.</p>
                <a href="index.php#precios">Ver todos los planes <span aria-hidden="true">→</span></a>
            </div>
            <div class="profile">
                <div class="avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($nombreUsuario, 0, 1, 'UTF-8'), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="profile-copy">
                    <strong><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($planActual['name'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <a class="icon-button profile-menu" href="logout.php" aria-label="Cerrar sesión" title="Cerrar sesión">↗</a>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="icon-button mobile-menu" id="menuButton" type="button" aria-label="Abrir menú">☰</button>
                    <div class="breadcrumb">Sam Software <span aria-hidden="true">/</span> <strong id="currentSection">Inicio</strong></div>
                </div>
                <div class="top-actions">
                    <button class="season-toggle" id="seasonToggle" type="button" aria-label="Cambiar estación de la interfaz">
                        <span class="season-icon" aria-hidden="true">☀️</span>
                        <span id="seasonLabel">Verano</span>
                    </button>
                    <div class="online-pill"><span class="online-dot"></span> Asistente local activo</div>
                    <button class="primary-link new-conversation" id="newConversation" type="button">+ Nueva conversación</button>
                </div>
            </header>

            <main id="appMain">
                <section class="app-view" data-view-panel="Inicio">
                    <section class="hero" aria-labelledby="welcome">
                        <div class="eyebrow"><span aria-hidden="true">✧</span> TU ASISTENTE DE SAM SOFTWARE</div>
                        <h1 id="welcome">Hola, <?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>.<br>¿Qué tienes <span>en mente?</span></h1>
                        <p class="hero-copy">Soy Sammy. Puedo orientarte sobre la página, los servicios y los planes de Sam Software.</p>

                        <form class="composer" id="promptForm">
                            <textarea id="promptInput" aria-label="Escribe un mensaje para Sammy" placeholder="Escribe tu pregunta para Sammy…" maxlength="4000" required></textarea>
                            <div class="composer-tools">
                                <button class="tool-button" type="button" id="attachButton" aria-label="Adjuntar un archivo de texto">＋ Adjuntar texto</button>
                                <input id="attachmentInput" type="file" accept=".txt,.md,.csv,text/plain,text/markdown,text/csv" hidden>
                                <span class="attachment-label" id="attachmentLabel" hidden></span>
                                <span class="composer-spacer"></span>
                                <span class="model-select">Sammy local <span aria-hidden="true">⌄</span></span>
                                <button class="send-button" type="submit" aria-label="Enviar mensaje">→</button>
                            </div>
                        </form>
                    </section>

                    <section aria-labelledby="quickTitle">
                        <div class="quick-heading"><h2 id="quickTitle">Empieza con una idea</h2><span>Elige un atajo para preparar tu pregunta</span></div>
                        <div class="prompt-grid">
                            <button class="prompt-card" type="button" data-prompt="Explícame qué servicios ofrece Sam Software.">
                                <span class="prompt-icon violet" aria-hidden="true">✧</span>
                                <strong>Conoce nuestros servicios</strong><small>Hosting, proyectos y soporte</small>
                            </button>
                            <button class="prompt-card" type="button" data-prompt="¿Qué plan me conviene y qué incluye cada uno?">
                                <span class="prompt-icon cyan" aria-hidden="true">▤</span>
                                <strong>Compara los planes</strong><small>Encuentra el plan que se ajusta a ti</small>
                            </button>
                            <button class="prompt-card" type="button" data-prompt="¿Cómo puedo crear una cuenta y empezar a usar la página?">
                                <span class="prompt-icon mint" aria-hidden="true">✓</span>
                                <strong>Empieza paso a paso</strong><small>Te oriento para comenzar</small>
                            </button>
                        </div>
                    </section>

                    <div class="lower-grid">
                        <section class="panel" aria-labelledby="recentTitle">
                            <div class="panel-heading"><h2 class="section-title" id="recentTitle">Conversaciones recientes</h2><button class="panel-link" type="button" data-open-view="Conversaciones">Ver todas →</button></div>
                            <div id="recentConversations" class="conversation-list"><p class="conversation-empty">Cargando conversaciones…</p></div>
                        </section>
                        <section class="panel local-panel" aria-labelledby="localTitle">
                            <div class="panel-heading"><h2 class="section-title" id="localTitle">Modo local</h2><span class="local-indicator">Activo</span></div>
                            <p>Sammy responde con información preparada sobre Sam Software. Tus mensajes se guardan en la base de datos de tu cuenta.</p>
                            <p class="local-note">No usa una llave ni envía tus preguntas a un servicio externo.</p>
                        </section>
                    </div>
                </section>

                <section class="app-view conversation-view" data-view-panel="Conversación" hidden>
                    <div class="conversation-heading">
                        <div><p class="eyebrow">CHAT GUARDADO</p><h1 id="conversationTitle">Nueva conversación</h1></div>
                        <button class="delete-button" id="deleteConversation" type="button" disabled>Eliminar</button>
                    </div>
                    <div class="message-list" id="messageList" aria-live="polite" aria-label="Mensajes de la conversación"></div>
                    <form class="composer chat-composer" id="chatForm">
                        <textarea id="messageInput" aria-label="Escribe un mensaje para Sammy" placeholder="Continúa la conversación…" maxlength="4000" required></textarea>
                        <div class="composer-tools">
                            <button class="tool-button" type="button" id="attachChatButton">＋ Adjuntar texto</button>
                            <input id="chatAttachmentInput" type="file" accept=".txt,.md,.csv,text/plain,text/markdown,text/csv" hidden>
                            <span class="attachment-label" id="chatAttachmentLabel" hidden></span>
                            <span class="composer-spacer"></span>
                            <span class="model-select">Sammy local</span>
                            <button class="send-button" type="submit" aria-label="Enviar mensaje">→</button>
                        </div>
                    </form>
                </section>

                <section class="app-view" data-view-panel="Conversaciones" hidden>
                    <div class="page-heading"><p class="eyebrow">HISTORIAL</p><h1>Tus <span>conversaciones.</span></h1><p class="hero-copy">Retoma un chat anterior o inicia uno nuevo. Tus conversaciones se guardan en tu cuenta.</p></div>
                    <label class="history-search">⌕ <input id="conversationSearch" type="search" placeholder="Buscar conversaciones…" autocomplete="off"></label>
                    <div id="allConversations" class="conversation-list conversation-grid"><p class="conversation-empty">Cargando conversaciones…</p></div>
                </section>

                <section class="app-view" data-view-panel="Biblioteca" hidden>
                    <div class="page-heading"><p class="eyebrow">TU ESPACIO</p><h1>Biblioteca de <span>chats.</span></h1><p class="hero-copy">Aquí encuentras las conversaciones guardadas en tu cuenta.</p></div>
                    <div id="libraryConversations" class="conversation-list conversation-grid"><p class="conversation-empty">Cargando conversaciones…</p></div>
                </section>

                <section class="app-view" data-view-panel="Explorar" hidden>
                    <div class="page-heading"><p class="eyebrow">IDEAS PARA EMPEZAR</p><h1>¿Qué podemos <span>resolver?</span></h1><p class="hero-copy">Elige una idea para preparar una pregunta para Sammy.</p></div>
                    <div class="prompt-grid explore-grid">
                        <button class="prompt-card" type="button" data-prompt="Cuéntame quién es Sammy y cómo me puede ayudar."><span class="prompt-icon violet" aria-hidden="true">✧</span><strong>Conoce a Sammy</strong><small>Qué puedo hacer en modo local</small></button>
                        <button class="prompt-card" type="button" data-prompt="Explícame los planes de Sam Software y sus precios."><span class="prompt-icon cyan" aria-hidden="true">▤</span><strong>Revisa los planes</strong><small>Precios y características</small></button>
                        <button class="prompt-card" type="button" data-prompt="¿Cómo contacto al equipo de Sam Software?"><span class="prompt-icon mint" aria-hidden="true">✉</span><strong>Busca soporte</strong><small>Cómo contactar al equipo</small></button>
                        <button class="prompt-card" type="button" data-prompt="Dame consejos para proteger mi cuenta y mis datos."><span class="prompt-icon violet" aria-hidden="true">◇</span><strong>Cuida tu cuenta</strong><small>Buenas prácticas de seguridad</small></button>
                        <button class="prompt-card" type="button" data-prompt="¿Qué es el hosting en la nube? Explícamelo sencillo."><span class="prompt-icon cyan" aria-hidden="true">☁</span><strong>Aprende sobre la nube</strong><small>Una explicación sencilla</small></button>
                        <button class="prompt-card" type="button" data-prompt="Cuéntame un chiste corto."><span class="prompt-icon mint" aria-hidden="true">☺</span><strong>Hagamos una pausa</strong><small>Un poco de humor</small></button>
                    </div>
                </section>

                <section class="app-view" data-view-panel="Ajustes" hidden>
                    <div class="page-heading"><p class="eyebrow">PREFERENCIAS</p><h1>Ajusta tu <span>espacio.</span></h1><p class="hero-copy">Personaliza cómo se ve Sammy en este dispositivo.</p></div>
                    <section class="panel setting-row"><div><h2 class="section-title">Tema oscuro</h2><p>Guarda tu preferencia en este navegador.</p></div><label class="theme-switch"><input id="themeToggle" type="checkbox"><span class="theme-switch-track"></span><span class="sr-only">Activar tema oscuro</span></label></section>
                    <section class="panel setting-row"><div><h2 class="section-title">Respuestas locales</h2><p>Sammy utiliza contenido incluido en el proyecto y no llama a servicios externos.</p></div><span class="local-indicator">Sin API externa</span></section>
                </section>

                <section class="app-view" data-view-panel="Ayuda" hidden>
                    <div class="page-heading"><p class="eyebrow">ESTAMOS PARA AYUDARTE</p><h1>Centro de <span>ayuda.</span></h1><p class="hero-copy">Respuestas rápidas para aprovechar el espacio de Sammy.</p></div>
                    <div class="help-grid">
                        <article class="panel"><h2 class="section-title">¿Sammy usa inteligencia artificial externa?</h2><p>No. Esta versión responde localmente con información de Sam Software; no necesita una llave de IA ni conexión a un proveedor.</p></article>
                        <article class="panel"><h2 class="section-title">¿Dónde se guardan mis conversaciones?</h2><p>En las tablas de conversaciones y mensajes de la base de datos de Sam Software, asociadas a tu cuenta.</p></article>
                        <article class="panel"><h2 class="section-title">¿Qué pasa si Sammy no sabe algo?</h2><p>Te indicará sus límites y te orientará a las secciones de servicios, planes o contacto. No inventa una conexión a internet.</p></article>
                        <article class="panel"><h2 class="section-title">¿Necesitas hablar con el equipo?</h2><p>Visita la sección de contacto de la página principal.</p><a class="panel-link" href="index.php#contacto">Ir a contacto →</a></article>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="sammy.js?v=5" defer></script>
<?php endif; ?>
</body>
</html>
