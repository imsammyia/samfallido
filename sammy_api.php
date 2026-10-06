<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/planes_helper.php';

function sammyResponder($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function sammyObtenerConversacion($conn, $conversacionId, $usuarioId)
{
    $stmt = $conn->prepare(
        'SELECT id, titulo, fecha_creacion
         FROM conversaciones
         WHERE id = ? AND usuario_id = ?
         LIMIT 1'
    );

    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la consulta de conversación.');
    }

    $stmt->bind_param('ii', $conversacionId, $usuarioId);

    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('No se pudo consultar la conversación.');
    }

    $conversacion = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $conversacion ?: null;
}

function sammyNormalizarTexto($texto)
{
    $sinAcentos = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'Ü' => 'u', 'Ñ' => 'n'
    ]);

    $normalizado = preg_replace('/[^a-zA-Z0-9]+/', ' ', strtolower($sinAcentos));
    if ($normalizado === null) {
        throw new RuntimeException('No se pudo procesar el texto del mensaje.');
    }

    return trim($normalizado);
}

function sammyGenerarRespuestaLocal($mensaje)
{
    $texto = sammyNormalizarTexto($mensaje);
    $respuestas = [
        [
            ['quien eres', 'como te llamas', 'que puedes hacer'],
            'Soy Sammy, el asistente de Sam Software. En esta versión respondo localmente preguntas sobre la página, los servicios, los planes y el uso de tu cuenta. No estoy conectado a una IA externa ni a internet.'
        ],
        [
            ['quienes somos', 'que es sam software', 'quien es sam software'],
            'Sam Software es una empresa enfocada en soluciones tecnológicas y servicios en la nube. Desde esta página puedes conocer los planes, crear una cuenta y contactar al equipo.'
        ],
        [
            ['mision', 'proposito de la empresa'],
            'Nuestra misión es acercar herramientas digitales y servicios en la nube a personas y empresas con una experiencia sencilla y accesible.'
        ],
        [
            ['vision', 'futuro de la empresa'],
            'Nuestra visión es seguir creciendo como una plataforma tecnológica innovadora y útil para nuestros usuarios.'
        ],
        [
            ['valores', 'filosofia de la empresa'],
            'Nos importan la innovación, la seguridad, la eficiencia, la comunicación clara y el compromiso con nuestros usuarios.'
        ],
        [
            ['plan gratuito', 'plan gratis', 'plan free'],
            'El plan gratuito cuesta $0 al mes e incluye acceso general a la página, hosting básico y soporte comunitario. En esta versión no incluye el chat de Sammy.'
        ],
        [
            ['plan demo', 'plan de prueba'],
            'El plan Demo cuesta $5.99 al mes e incluye una versión premium limitada, 2 proyectos activos premium, acceso al chatbot clásico de Sammy y soporte básico.'
        ],
        [
            ['plan basico', 'plan basic'],
            'El plan Básico cuesta $15.99 al mes e incluye hasta 6 proyectos premium activos, soporte prioritario y acceso al chatbot clásico de Sammy.'
        ],
        [
            ['plan profesional', 'plan professional'],
            'El plan Profesional cuesta $27.99 al mes e incluye hosting, hasta 10 proyectos, soporte por correo, dominio, el chatbot de Sammy y la nueva interfaz completa.'
        ],
        [
            ['semi empresarial', 'semiempresarial'],
            'El plan Semi Empresarial cuesta $45.99 al mes e incluye hosting privado, proyectos ilimitados, soporte prioritario, el chatbot de Sammy y la nueva interfaz completa, además de dominio privado.'
        ],
        [
            ['plan empresarial', 'plan enterprise'],
            'El plan Empresarial cuesta $55.99 al mes e incluye infraestructura dedicada prioritaria, proyectos ilimitados, soporte privado 24/7, hosting mejorado, dominio, acceso anticipado a actualizaciones y la nueva interfaz completa de Sammy.'
        ],
        [
            ['planes', 'que plan', 'precios', 'cuanto cuesta', 'cuanto vale', 'suscripcion', 'mensualidad'],
            'Estos son los planes disponibles: Gratuito ($0), Demo ($5.99), Básico ($15.99), Profesional ($27.99), Semi Empresarial ($45.99) y Empresarial ($55.99) al mes. Demo y Básico incluyen el chatbot clásico; Profesional y los planes superiores también incluyen la nueva interfaz completa. Revisa los detalles en la sección «Planes».'
        ],
        [
            ['servicios', 'que ofrecen', 'hosting', 'dominio', 'proyectos premium'],
            'Sam Software ofrece servicios de hosting, proyectos premium, dominios según el plan y soporte. Las características disponibles dependen del plan contratado.'
        ],
        [
            ['seguridad', 'privacidad', 'proteger mis datos', 'contrasena'],
            'Para proteger tu cuenta, utiliza una contraseña única y no la compartas. Tus conversaciones se guardan en la base de datos de Sam Software bajo tu cuenta; esta versión local no las envía a un servicio de IA externo.'
        ],
        [
            ['contacto', 'contactar', 'soporte', 'hablar con alguien', 'correo'],
            'Puedes escribir al equipo desde el formulario de la sección «Contáctanos» de la página principal. No tengo una dirección de correo confirmada, así que prefiero no inventarla.'
        ],
        [
            ['como funciona', 'como me registro', 'crear una cuenta', 'iniciar sesion', 'como empezar'],
            'Puedes crear una cuenta desde «Registrarme» en la página principal, iniciar sesión y elegir un plan. Después abre «Sammy» para conversar; el historial se guardará asociado a tu cuenta.'
        ],
        [
            ['que es la nube', 'computacion en la nube', 'hosting en la nube'],
            'La computación en la nube permite usar servicios y guardar información a través de internet, sin depender únicamente de un dispositivo. En Sam Software se relaciona con servicios de hosting y soluciones digitales.'
        ],
        [
            ['como estas', 'que tal estas', 'como te va'],
            '¡Gracias por preguntar! Estoy listo para charlar y ayudarte ☁ ¿Cómo estás tú?'
        ],
        [
            ['estoy triste', 'me siento mal', 'estoy desanimado', 'necesito hablar'],
            'Siento que estés pasando por eso. Si quieres, cuéntame un poco más; puedo escucharte y ayudarte a pensar en un siguiente paso.'
        ],
        [
            ['buen trabajo', 'eres genial', 'eres increible', 'te quiero'],
            '¡Muchas gracias! 😊 Me alegra poder ayudarte. ¿Qué más te gustaría explorar?'
        ],
        [
            ['ayuda', 'como puedes ayudarme', 'en que me ayudas'],
            'Puedo orientarte sobre Sam Software, explicar los planes y servicios, ayudarte a navegar por la página o charlar sobre temas cotidianos. ¿Qué necesitas?'
        ],
        [
            ['hola', 'buenos dias', 'buenas tardes', 'buenas noches', 'que onda'],
            '¡Hola! ☁ Soy Sammy, el asistente local de Sam Software. Puedo orientarte sobre nuestros servicios, planes y cómo empezar. ¿Qué te gustaría saber?'
        ],
        [
            ['gracias', 'te agradezco', 'muy amable'],
            '¡De nada! ☁ Me alegra poder orientarte. ¿Hay algo más de Sam Software que quieras conocer?'
        ],
        [
            ['me aburro', 'estoy aburrido', 'estoy aburrida'],
            '¡Hagamos algo! Puedo contarte un chiste, hablar sobre tecnología o explicarte uno de los planes. ¿Qué prefieres? 😊'
        ],
        [
            ['adios', 'hasta luego', 'chao', 'nos vemos'],
            '¡Hasta pronto! Gracias por conversar conmigo ☁'
        ],
        [
            ['chiste', 'hazme reir'],
            '¿Por qué el ordenador fue al médico? Porque tenía un virus… ¡menos mal que no era nada grave! 😄'
        ]
    ];

    foreach ($respuestas as $grupo) {
        foreach ($grupo[0] as $frase) {
            if (preg_match('/(^| )' . preg_quote($frase, '/') . '( |$)/', $texto)) {
                return $grupo[1];
            }
        }
    }

    return 'Puedo ayudarte con información de Sam Software: quiénes somos, planes, servicios, seguridad y cómo empezar. En esta versión no tengo una IA general ni acceso a internet; prueba preguntarme por uno de esos temas o consulta las secciones de la página.';
}

if (!isset($_SESSION['id'])) {
    sammyResponder(['success' => false, 'message' => 'Inicia sesión para usar Sammy.'], 401);
}

$usuarioId = (int) $_SESSION['id'];
$planActual = obtenerPlanUsuario($conn, $usuarioId);
if (!usuarioPuedeUsarInterfazSammy($planActual['code'] ?? 'free')) {
    sammyResponder([
        'success' => false,
        'message' => 'La interfaz completa de Sammy requiere Profesional, Semi Empresarial o Empresarial. Los planes Demo y Básico incluyen el chatbot clásico.',
        'requires_plan' => true
    ], 403);
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($action === 'list' && $method === 'GET') {
    try {
        $stmt = $conn->prepare(
            'SELECT c.id, c.titulo,
                    COALESCE(MAX(m.fecha), c.fecha_creacion) AS fecha_creacion
             FROM conversaciones c
             LEFT JOIN mensajes m ON m.conversacion_id = c.id
             WHERE c.usuario_id = ?
             GROUP BY c.id, c.titulo, c.fecha_creacion
             ORDER BY COALESCE(MAX(m.id), c.id) DESC
             LIMIT 100'
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la consulta de conversaciones.');
        }
        if (!$stmt->bind_param('i', $usuarioId) || !$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudieron cargar las conversaciones.');
        }

        $result = $stmt->get_result();
        $conversaciones = [];
        while ($row = $result->fetch_assoc()) {
            $conversaciones[] = $row;
        }
        $stmt->close();
    } catch (mysqli_sql_exception $error) {
        error_log('Sammy DB error: ' . $error->getMessage());
        sammyResponder(['success' => false, 'message' => 'No se pudieron cargar las conversaciones. Revisa que las tablas de Sammy estén configuradas.'], 500);
    } catch (RuntimeException $error) {
        error_log('Sammy operation error: ' . $error->getMessage());
        sammyResponder(['success' => false, 'message' => 'No se pudieron cargar las conversaciones.'], 500);
    }

    sammyResponder(['success' => true, 'conversations' => $conversaciones]);
}

if ($method !== 'POST') {
    sammyResponder(['success' => false, 'message' => 'Método no permitido.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    sammyResponder(['success' => false, 'message' => 'La solicitud no contiene datos válidos.'], 400);
}

if (!tokenCsrfValido($input['csrf_token'] ?? null)) {
    sammyResponder(['success' => false, 'message' => 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.'], 403);
}

try {
    if ($action === 'create') {
        $titulo = 'Nueva conversación';
        $stmt = $conn->prepare('INSERT INTO conversaciones (usuario_id, titulo) VALUES (?, ?)');
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la creación de la conversación.');
        }
        $stmt->bind_param('is', $usuarioId, $titulo);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo crear la conversación.');
        }
        $conversacionId = $stmt->insert_id;
        $stmt->close();
        sammyResponder([
            'success' => true,
            'conversation' => [
                'id' => $conversacionId,
                'titulo' => $titulo,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    if ($action === 'load') {
        $conversacionId = filter_var($input['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$conversacionId || $conversacionId < 1) {
            sammyResponder(['success' => false, 'message' => 'Selecciona una conversación válida.'], 400);
        }

        $conversacion = sammyObtenerConversacion($conn, $conversacionId, $usuarioId);
        if ($conversacion === null) {
            sammyResponder(['success' => false, 'message' => 'No se encontró esa conversación.'], 404);
        }

        $stmt = $conn->prepare(
            'SELECT id, tipo, contenido, fecha
             FROM mensajes
             WHERE conversacion_id = ?
             ORDER BY id ASC'
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la carga de mensajes.');
        }
        $stmt->bind_param('i', $conversacionId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudieron cargar los mensajes.');
        }
        $result = $stmt->get_result();
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        $stmt->close();

        sammyResponder([
            'success' => true,
            'conversation' => $conversacion,
            'messages' => $mensajes
        ]);
    }

    if ($action === 'send') {
        if (!isset($input['message']) || !is_string($input['message'])) {
            sammyResponder(['success' => false, 'message' => 'El mensaje debe ser texto.'], 400);
        }
        $contenido = trim($input['message']);
        if ($contenido === '') {
            sammyResponder(['success' => false, 'message' => 'Escribe un mensaje antes de enviarlo.'], 400);
        }
        if (mb_strlen($contenido, 'UTF-8') > 4000) {
            sammyResponder(['success' => false, 'message' => 'El mensaje no puede superar los 4,000 caracteres.'], 400);
        }

        $conversacionId = null;
        if (isset($input['conversation_id']) && $input['conversation_id'] !== '') {
            $conversacionId = filter_var($input['conversation_id'], FILTER_VALIDATE_INT);
            if (!$conversacionId || $conversacionId < 1) {
                sammyResponder(['success' => false, 'message' => 'Selecciona una conversación válida.'], 400);
            }
            if (sammyObtenerConversacion($conn, $conversacionId, $usuarioId) === null) {
                sammyResponder(['success' => false, 'message' => 'No se encontró esa conversación.'], 404);
            }
        }

        $textoTitulo = preg_replace('/\s+/u', ' ', $contenido);
        if ($textoTitulo === null) {
            throw new RuntimeException('No se pudo preparar el título de la conversación.');
        }
        $titulo = mb_substr($textoTitulo, 0, 50, 'UTF-8');
        if (mb_strlen($textoTitulo, 'UTF-8') > 50) {
            $titulo .= '…';
        }
        $respuesta = sammyGenerarRespuestaLocal($contenido);

        $conn->begin_transaction();

        if ($conversacionId === null) {
            $stmt = $conn->prepare('INSERT INTO conversaciones (usuario_id, titulo) VALUES (?, ?)');
            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la creación de la conversación.');
            }
            $stmt->bind_param('is', $usuarioId, $titulo);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo crear la conversación.');
            }
            $conversacionId = $stmt->insert_id;
            $stmt->close();
        } else {
            $stmt = $conn->prepare(
                'SELECT COUNT(*) AS total
                 FROM mensajes
                 WHERE conversacion_id = ?'
            );
            if (!$stmt) {
                throw new RuntimeException('No se pudo revisar la conversación.');
            }
            $stmt->bind_param('i', $conversacionId);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo revisar la conversación.');
            }
            $totalMensajes = (int) $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();
            if ($totalMensajes === 0) {
                $stmt = $conn->prepare('UPDATE conversaciones SET titulo = ? WHERE id = ? AND usuario_id = ?');
                if (!$stmt) {
                    throw new RuntimeException('No se pudo preparar la actualización del título.');
                }
                $stmt->bind_param('sii', $titulo, $conversacionId, $usuarioId);
                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new RuntimeException('No se pudo actualizar el título.');
                }
                $stmt->close();
            }
        }

        $stmt = $conn->prepare(
            'INSERT INTO mensajes (conversacion_id, tipo, contenido)
             VALUES (?, ?, ?)'
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar el guardado del mensaje.');
        }
        $tipoUsuario = 'user';
        $stmt->bind_param('iss', $conversacionId, $tipoUsuario, $contenido);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo guardar tu mensaje.');
        }
        $mensajeUsuarioId = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare(
            'INSERT INTO mensajes (conversacion_id, tipo, contenido)
             VALUES (?, ?, ?)'
        );
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar el guardado de la respuesta.');
        }
        $tipoSammy = 'bot';
        $stmt->bind_param('iss', $conversacionId, $tipoSammy, $respuesta);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo guardar la respuesta local.');
        }
        $mensajeSammyId = $stmt->insert_id;
        $stmt->close();

        $conn->commit();
        $conversacion = sammyObtenerConversacion($conn, $conversacionId, $usuarioId);
        sammyResponder([
            'success' => true,
            'conversation' => $conversacion,
            'messages' => [
                ['id' => $mensajeUsuarioId, 'tipo' => $tipoUsuario, 'contenido' => $contenido],
                ['id' => $mensajeSammyId, 'tipo' => $tipoSammy, 'contenido' => $respuesta]
            ]
        ]);
    }

    if ($action === 'delete') {
        $conversacionId = filter_var($input['conversation_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$conversacionId || $conversacionId < 1) {
            sammyResponder(['success' => false, 'message' => 'Selecciona una conversación válida.'], 400);
        }
        if (sammyObtenerConversacion($conn, $conversacionId, $usuarioId) === null) {
            sammyResponder(['success' => false, 'message' => 'No se encontró esa conversación.'], 404);
        }

        $conn->begin_transaction();
        $stmt = $conn->prepare('DELETE FROM mensajes WHERE conversacion_id = ?');
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la eliminación de mensajes.');
        }
        $stmt->bind_param('i', $conversacionId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudieron eliminar los mensajes.');
        }
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM conversaciones WHERE id = ? AND usuario_id = ?');
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la eliminación de la conversación.');
        }
        $stmt->bind_param('ii', $conversacionId, $usuarioId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo eliminar la conversación.');
        }
        $stmt->close();
        $conn->commit();
        sammyResponder(['success' => true]);
    }

    sammyResponder(['success' => false, 'message' => 'La acción solicitada no está disponible.'], 400);
} catch (mysqli_sql_exception $error) {
    $conn->rollback();
    error_log('Sammy DB error: ' . $error->getMessage());
    sammyResponder(['success' => false, 'message' => 'No se pudo completar la operación en la base de datos. Revisa que las tablas de Sammy estén configuradas.'], 500);
} catch (RuntimeException $error) {
    $conn->rollback();
    error_log('Sammy operation error: ' . $error->getMessage());
    sammyResponder(['success' => false, 'message' => 'No se pudo completar la operación. Inténtalo de nuevo.'], 500);
}
