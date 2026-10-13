<?php

session_start();

include("conexion.php");
include("planes_helper.php");

$csrfToken = obtenerTokenCsrf();
$planActual = isset($_SESSION['id'])
    ? obtenerPlanUsuario($conn, (int) $_SESSION['id'])
    : null;
$codigoPlanActual = ($planActual['active'] ?? false)
    ? $planActual['code']
    : null;
$chatDisponible = $planActual !== null
    && usuarioPuedeUsarChat($codigoPlanActual);
$interfazCompletaSammy = $planActual !== null
    && usuarioPuedeUsarInterfazSammy($codigoPlanActual);
$mensajesPlanes = [
    'login' => 'Inicia sesión y vuelve a elegir el plan para continuar.',
    'activo' => 'El plan quedó activado en tu cuenta.',
    'no_disponible' => 'No se pudo procesar el plan. Inténtalo de nuevo.',
    'no_degradar' => 'No puedes cambiar a gratis desde un plan pagado activo.'
];
$estadoPlan = $_GET['plan'] ?? '';
$mensajePlan = is_string($estadoPlan)
    ? ($mensajesPlanes[$estadoPlan] ?? '')
    : '';

?>

<!DOCTYPE html>
<html lang="es">

<head>
   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sam Software - En la Nube</title>
    <link rel="stylesheet" href="style.css?v=sammy-interactive-intro-2">
    <style>
.mensaje-overlay {
    position: fixed !important;
    inset: 0 !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    background: rgba(0, 0, 0, 0.4) !important;
    z-index: 99999 !important;
}

.mensaje-card {
    position: relative !important;
    width: 420px !important;
    padding: 40px !important;
    background: #f7f9fc !important;
    color: #19283d !important;
    border-radius: 25px !important;
    text-align: center !important;
    box-shadow: 0 20px 50px rgba(0,0,0,.3) !important;
}

.mensaje-card h2,
.mensaje-card p,
.mensaje-card span,
.mensaje-card strong,
.mensaje-card .mensaje-icono {
    color: inherit !important;
}

.cerrar-mensaje {
    position: absolute !important;
    top: 12px !important;
    right: 12px !important;
    width: 32px !important;
    height: 32px !important;
    border: none !important;
    border-radius: 50% !important;
    background: rgba(0, 0, 0, 0.06) !important;
    color: #1d2a39 !important;
    font-size: 24px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

body.dark .mensaje-overlay {
    background: rgba(2, 8, 20, 0.68) !important;
}

body.dark .mensaje-card {
    background: linear-gradient(180deg, #1b2a3f 0%, #13243d 100%) !important;
    color: #edf6ff !important;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.45) !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
}

body.dark .cerrar-mensaje {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
}

body.dark .mensaje-card .mensaje-icono,
body.dark .mensaje-card h2,
body.dark .mensaje-card p,
body.dark .mensaje-card span,
body.dark .mensaje-card strong {
    color: #edf6ff !important;
}
</style>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Jost:ital,wght@0,100..900;1,100..900&family=Momo+Trust+Display&family=Outfit:wght@100..900&family=Playwrite+AU+QLD:wght@100..400&display=swap">
</head>

<body
    data-chat-enabled="<?= $chatDisponible ? 'true' : 'false' ?>"
    data-sammy-dashboard="<?= $interfazCompletaSammy ? 'true' : 'false' ?>">

  
    <div class="sky">
        <div class="sun"></div>
        <div class="moon"></div>
        <div id="stars"></div>
        <div id="season-effects" aria-hidden="true"></div>

        <img src="img/nube.png" class="bg-cloud c1" alt="nube">
        <img src="img/nube.png" class="bg-cloud c2" alt="nube">
        <img src="img/nube.png" class="bg-cloud c3" alt="nube">
        <img src="img/nube.png" class="bg-cloud c4" alt="nube">
    </div>

   
    <header class="header">
        <div class="logo">
            <img src="img/logo.png" alt="Sam Software Logo">
        </div>

        <nav class="nav">
            <a href="#inicio">Inicio</a>
            <a href="#sammy-presentation">Quiénes Somos</a>
            <a href="#caracteristicas">Servicios</a>
            <a href="gestion.php">Gestión</a>
            <a href="#contacto">Contacto</a>
            <a href="<?= $interfazCompletaSammy ? 'sammy.php' : '#sammy-presentation' ?>" class="sammy-link">Sammy</a>
        </nav>

        <div class="auth-buttons">
            <?php if (isset($_SESSION['id'])): ?>
                <div class="user-session">
                    <span class="user-welcome">
                        Hola,
                        <strong>
                            <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
                        </strong>
                    </span>
                    <a href="logout.php" class="logout-btn">
                        Cerrar sesión
                    </a>
                </div>
            <?php else: ?>
                <button id="loginBtn">Iniciar Sesión</button>
                <button id="registerBtn">Registrarme</button>
            <?php endif; ?>
        </div>

        <div class="appearance-controls">
            <div class="season-control">
                
                <button id="season-toggle" class="season-toggle" type="button" aria-label="Cambiar estación" title="Cambiar estación">
                    <span class="season-icon" aria-hidden="true">☀️</span>
                    <span class="season-name">Verano</span>
                </button>
            </div>
            <button class="mode-toggle" type="button" aria-label="Cambiar entre día y noche" title="Cambiar entre día y noche">🌙</button>
        </div>
    </header>

    <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
        <div class="mensaje-overlay">
            <div class="mensaje-card">
                <button class="cerrar-mensaje" onclick="this.closest('.mensaje-overlay').remove()">
                    &times;
                </button>

                <div class="mensaje-icono">☁️</div>

                <h2>¡Cuenta creada!</h2>

                <p>
                    Tu cuenta de
                    <strong>Sam Software</strong>
                    fue creada correctamente.
                </p>

                <span>
                    Ahora puedes iniciar sesión y comenzar a utilizar nuestros servicios.
                </span>

                <button
                    class="mensaje-boton"
                    onclick="
                        this.closest('.mensaje-overlay').remove();
                        const loginBtn = document.getElementById('loginBtn');
                        if (loginBtn) loginBtn.click();
                    ">
                    Iniciar sesión
                </button>
            </div>
        </div>
    <?php endif; ?>

   <?php if (isset($_GET['login']) && $_GET['login'] == 'exitoso'): ?>
    <div class="mensaje-overlay">
        <div class="mensaje-card">
            <button
                class="cerrar-mensaje"
                onclick="this.closest('.mensaje-overlay').remove()">
                &times;
            </button>

            <div class="mensaje-icono">
                ☁️
            </div>

            <h2>
                ¡Bienvenido!
            </h2>

            <p>
                Hola,
                <strong>
                    <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
                </strong>
            </p>

            <span>
                Has iniciado sesión correctamente en Sam Software.
            </span>

            <button
                class="mensaje-boton"
                onclick="this.closest('.mensaje-overlay').remove()">
                Continuar
            </button>
        </div>
    </div>
<?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-overlay">
            <div class="mensaje-card">
                <button class="cerrar-mensaje" onclick="this.closest('.mensaje-overlay').remove()">
                    &times;
                </button>

                <div class="mensaje-icono">⚠️</div>

                <h2>Algo salió mal</h2>

                <p>
                    <?php
                    if ($_GET['error'] == 'correo') {
                        echo "Ese correo ya está registrado.";
                    } elseif ($_GET['error'] == 'contraseña') {
                        echo "La contraseña es incorrecta.";
                    } elseif ($_GET['error'] == 'usuario') {
                        echo "No existe una cuenta con ese correo.";
                    } else {
                        echo "Ocurrió un error. Inténtalo nuevamente.";
                    }
                    ?>
                </p>

                <button class="mensaje-boton" onclick="this.closest('.mensaje-overlay').remove()">
                    Entendido
                </button>
            </div>
        </div>
    <?php endif; ?>

    <main class="page">
        <section class="hero" id="inicio">
            <div class="clouds">
                <img src="img/nube.png" alt="Nube principal" class="main-cloud">
                <img src="img/sammy.png" alt="Sammy flotando" class="sammy">
            </div>

            <div class="hero-text">
                <h1>
                    Bienvenidos a
                    <span>Sam Software</span>
                </h1>

                <p>
                    Soluciones en la nube con un toque de creatividad ☁️
                </p>
            </div>
        </section>

        <section class="sammy-presentation" id="sammy-presentation">
            <div class="sammy-image">
                <button
                    class="sammy-character"
                    id="sammyCharacter"
                    type="button"
                    aria-label="Interactuar con Sammy"
                    aria-expanded="false"
                    aria-controls="sammyIntroCopy sammyIntroOptions">
                    <img src="img/sammy2.png" alt="">
                    <span class="sammy-character-hint">Haz clic para saludarme</span>
                </button>
            </div>

            <div class="sammy-text">
                <h2>¡Hola, soy Sammy!</h2>
                <p
                    class="sammy-intro-copy"
                    id="sammyIntroCopy"
                    data-full-text="Soy tu asistente de Sam Software. Puedo ayudarte con nuestros servicios, planes y más. ¿Qué te gustaría explorar hoy?"
                    aria-live="off"></p>
                <p class="sammy-intro-hint" id="sammyIntroHint">Interactúa conmigo para conocerme.</p>
                <div class="sammy-intro-options" id="sammyIntroOptions" aria-label="Opciones para conversar con Sammy">
                    <p class="sammy-chat-prompt">¿Cómo quieres continuar?</p>
                    <?php if ($chatDisponible): ?>
                        <button class="sammy-chat-link" id="launchSammyChat" type="button">Abrir chatbot <span aria-hidden="true">→</span></button>
                    <?php endif; ?>
                    <?php if ($interfazCompletaSammy): ?>
                        <a class="sammy-dashboard-link" href="sammy.php">Abrir la interfaz completa <span aria-hidden="true">↗</span></a>
                    <?php endif; ?>
                    <?php if (!$chatDisponible): ?>
                        <a class="sammy-chat-link" href="#precios">Ver planes de Sammy <span aria-hidden="true">→</span></a>
                    <?php elseif (!$interfazCompletaSammy): ?>
                        <a class="sammy-dashboard-link" href="#precios">Conoce la interfaz completa <span aria-hidden="true">↗</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="caracteristicas" id="caracteristicas">
            <h2>Características</h2>

            <div class="feature-grid">
                <div class="feature">
                    <h3>🔒 Seguridad</h3>
                    <p>
                        Tus datos protegidos con encriptación avanzada y respaldo constante.
                    </p>
                </div>

                <div class="feature">
                    <h3>⚡ Velocidad</h3>
                    <p>
                        Implementaciones rápidas y sistemas optimizados para un rendimiento superior.
                    </p>
                </div>

                <div class="feature">
                    <h3>🌎 Accesibilidad</h3>
                    <p>
                        Conéctate desde cualquier lugar, en cualquier dispositivo, con total estabilidad.
                    </p>
                </div>
            </div>
        </section>

        <section class="pricing" id="precios">
            <h2>Planes</h2>

            <?php if ($mensajePlan !== ''): ?>
                <p class="plan-notice" role="status">
                    <?= htmlspecialchars($mensajePlan, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if ($planActual !== null): ?>
                <p class="plan-current">
                    Plan actual: <strong><?= htmlspecialchars($planActual['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                </p>
            <?php endif; ?>

            <div class="pricing-carousel">
                <button class="pricing-arrow pricing-prev" aria-label="Plan anterior">‹</button>

                <div class="pricing-track-wrap">
                    <div class="pricing-track">
                        <div class="price-card">
                            <h3>Plan gratuito</h3>
                            <p>$0/mes</p>
                            <ul>
                                <li>acceso general a la pagina</li>
                                <li>hosting basico </li>
                                <li>Soporte comunitario</li>
                            </ul>
                            <?= formularioPlan('free', $csrfToken, $codigoPlanActual) ?>
                        </div>

                        <div class="price-card">
                            <h3>Plan demo</h3>
                            <p>$5.99/mes</p>
                            <ul>
                                <li>Demo de prueba</li>
                                <li>version premium limitada</li>
                                <li>2 proyectos activos premiun</li>
                                <li>Acceso al chatbot clásico de Sammy</li>
                                <li> Soporte basico</li>
                            </ul>
                            <?= formularioPlan('demo', $csrfToken, $codigoPlanActual) ?>
                        </div>

                        <div class="price-card popular">
                            <h3>basico</h3>
                            <p>$15.99/mes</p>
                            <ul>
                                <li>Version premium limitada</li>
                                <li>6 proyectos activos premium</li>
                                <li>Soporte prioritario</li>
                                <li>Acceso al chatbot clásico de Sammy</li>
                                <li>Informacion sobre nuevos productos y servicios</li>
                            </ul>
                            <?= formularioPlan('basic', $csrfToken, $codigoPlanActual) ?>
                        </div>

                        <div class="price-card">
                            <h3>Profesional</h3>
                            <p>$27.99/mes</p>
                            <ul>
                                <li>version premium prioritario</li>
                                <li>Hosting incluido</li>
                                <li>10 proyecto</li>
                                <li>Soporte por correo</li>
                                <li>Dominio incluidos</li>
                                <li>Chatbot de Sammy y nueva interfaz completa</li>
                            </ul>
                            <?= formularioPlan('professional', $csrfToken, $codigoPlanActual) ?>
                        </div>

                        <div class="price-card popular">
                            <h3>Semi Empresarial</h3> 
                            <p>$45.99/mes</p>
                            <ul>
                                <li>Version premium deluxe</li>
                                <li>Hosting privado</li>
                                <li>Proyectos ilimitados</li>
                                <li>Soporte prioritario</li>
                                <li>Chatbot de Sammy y nueva interfaz completa</li>
                                <li>Descuentos exclusivos por usuario leal</li>
                                <li>Dominio web privado</li>
                            </ul>
                            <?= formularioPlan('semi_enterprise', $csrfToken, $codigoPlanActual) ?>
                        </div>

                        <div class="price-card">
                            <h3>Empresarial</h3>
                            <p>$55.99/mes</p>
                            <ul>
                                <li>Infraestructura dedicada prioritaria</li>
                                <li>Proyectos ilimitados</li>
                                <li>Soporte 24/7 privado</li>
                                <li>cifrado de seguridad deluxe</li>
                                <li>Descuestos y ofertas exclusivas</li>
                                <li>Dominio y hosting mejorado </li>
                                <li>personalizacion web exclusiva</li>
                                <li>Acceso anticipado a nuevas actualizaciones</li>
                                <li>Chatbot de Sammy y nueva interfaz completa</li>
                            </ul>
                            <?= formularioPlan('enterprise', $csrfToken, $codigoPlanActual) ?>
                        </div>
                    </div>
                </div>

                <button class="pricing-arrow pricing-next" aria-label="Siguiente plan">›</button>
            </div>
        </section>

        <section class="contact" id="contacto">
            <h2>Contáctanos</h2>

            <form class="contact-form">
                <input type="text" placeholder="Nombre">
                <input type="email" placeholder="Correo">
                <textarea placeholder="Mensaje"></textarea>
                <button type="submit">Enviar</button>
            </form>
        </section>
    </main>

    <div id="chat-widget" class="chat-widget hidden" aria-label="Chatbot de Sammy">
        <div class="chat-header">
            <h3>Sammy</h3>
        </div>

        <div class="chat-body">
            <div class="chat-sammy-expanded">
                <img src="img/sammy2.png" alt="Sammy en el chat">
            </div>

            <div class="chat-messages" aria-live="polite">
                <p class="bot-msg">¡Hola! ¿En qué puedo ayudarte hoy?</p>
            </div>

            <div class="chat-input">
                <input type="text" placeholder="Escribe tu mensaje..." aria-label="Escribe un mensaje para Sammy">
            </div>
        </div>

        <button id="toggle-chat" type="button" aria-label="Minimizar chatbot">−</button>

        <div class="chat-sammy-collapsed">
            <img src="img/sammy2.png" alt="Sammy minimizado">
        </div>
    </div>

    <footer>
        <p>© 2025 Sam Software | Todos los derechos reservados</p>
    </footer>

    <div id="modal-login" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>

            <h2>Iniciar Sesión</h2>

            <form action="login.php" method="POST">
                <input type="email" name="correo" placeholder="Correo electrónico" required>
                <input type="password" name="contraseña" placeholder="Contraseña" required>
                <button type="submit" name="iniciar">Entrar</button>
            </form>
            <p class="modal-switch-copy">¿No tienes cuenta? <button type="button" class="modal-switch" data-modal="modal-register">Crear cuenta</button></p>
        </div>
    </div>

    <div id="modal-register" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>

            <h2>Registrarme</h2>

            <form action="registro.php" method="POST">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="email" name="correo" placeholder="Correo" required>
                <input type="password" name="contraseña" placeholder="Contraseña" required>
                <button type="submit" name="registrarse">Crear cuenta</button>
            </form>
            <p class="modal-switch-copy">¿Ya tienes cuenta? <button type="button" class="modal-switch" data-modal="modal-login">Iniciar sesión</button></p>
        </div>
    </div>

    <script src="script.js?v=sammy-interactive-intro-4"></script>

</body>

</html>
