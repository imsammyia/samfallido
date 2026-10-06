document.addEventListener('DOMContentLoaded', () => {

    const toggle = document.querySelector('.mode-toggle');
    const starsContainer = document.getElementById('stars');
    const seasonToggle = document.getElementById('season-toggle');
    const seasonIcon = document.querySelector('.season-icon');
    const seasonName = document.querySelector('.season-name');
    const seasonEffects = document.getElementById('season-effects');
    const THEME_KEY = 'sam-software-theme';
    const SEASON_KEY = 'sam-software-season';
    const SEASON_ORDER = ['spring', 'summer', 'autumn', 'winter'];

    const applySeason = (season) => {
        const seasonParticles = {
            spring: { name: 'Primavera', icon: '🌸', symbol: '✿', count: 18 },
            summer: { name: 'Verano', icon: '☀️', symbol: '', count: 12 },
            autumn: { name: 'Otoño', icon: '🍂', symbol: '🍂', count: 20 },
            winter: { name: 'Invierno', icon: '❄️', symbol: '❄', count: 24 }
        };
        const selectedSeason = seasonParticles[season]
            ? season
            : 'summer';
        const currentSeason = seasonParticles[selectedSeason];

        document.body.dataset.season = selectedSeason;

        if (seasonIcon) {
            seasonIcon.textContent = currentSeason.icon;
        }

        if (seasonName) {
            seasonName.textContent = currentSeason.name;
        }

        if (seasonToggle) {
            const label = `Cambiar estación. Actual: ${currentSeason.name}`;
            seasonToggle.setAttribute('aria-label', label);
            seasonToggle.title = label;
        }

        if (!seasonEffects) return;

        seasonEffects.replaceChildren();

        for (let index = 0; index < currentSeason.count; index++) {
            const particle = document.createElement('span');
            particle.className = 'season-particle';
            particle.dataset.season = selectedSeason;
            particle.textContent = seasonParticles[selectedSeason].symbol;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.animationDelay = `${-Math.random() * 20}s`;
            particle.style.animationDuration = `${12 + Math.random() * 16}s`;
            particle.style.fontSize = `${12 + Math.random() * 16}px`;
            seasonEffects.appendChild(particle);
        }
    };

    const applyTheme = (theme) => {

        const isDark = theme === 'dark';

        document.body.classList.toggle('dark', isDark);

        if (toggle) {
            toggle.textContent = isDark ? '☀️' : '🌙';
        }
    };

    try {

        const savedTheme =
            localStorage.getItem(THEME_KEY) || 'light';
        const savedSeason =
            localStorage.getItem(SEASON_KEY) || 'summer';

        applyTheme(savedTheme);
        applySeason(savedSeason);

    } catch (error) {

        applyTheme('light');
        applySeason('summer');

    }

    if (seasonToggle) {
        seasonToggle.addEventListener('click', () => {
            const currentSeason = document.body.dataset.season || 'summer';
            const currentIndex = SEASON_ORDER.indexOf(currentSeason);
            const nextSeason = SEASON_ORDER[(currentIndex + 1) % SEASON_ORDER.length];

            try {
                localStorage.setItem(SEASON_KEY, nextSeason);
            } catch (error) {
            }

            applySeason(nextSeason);
        });
    }

    window.addEventListener('storage', (event) => {
        if (event.key === SEASON_KEY && SEASON_ORDER.includes(event.newValue)) {
            applySeason(event.newValue);
        }
    });


    /* ESTRELLAS */
    

    const createShootingStar = () => {

        if (!starsContainer) return;

        const star = document.createElement('div');

        star.className = 'shooting-star';

        const startX = Math.random() * 100;
        const startY = Math.random() * 35;

        star.style.left = `${startX}%`;
        star.style.top = `${startY}%`;

        star.style.animationDuration =
            `${0.7 + Math.random() * 0.8}s`;

        star.style.animationDelay =
            `${Math.random() * 1.2}s`;

        starsContainer.appendChild(star);

        setTimeout(() => {
            star.remove();
        }, 3500);
    };


    if (starsContainer) {

        for (let i = 0; i < 60; i++) {

            const s = document.createElement('div');

            s.classList.add('star');

            s.style.top =
                `${Math.random() * 100}%`;

            s.style.left =
                `${Math.random() * 100}%`;

            s.style.animationDuration =
                `${3 + Math.random() * 4}s`;

            starsContainer.appendChild(s);
        }

        setInterval(createShootingStar, 900);
    }


    /* MODO OSCURO */
    

    if (toggle) {

        toggle.addEventListener('click', () => {

            const nextTheme =
                document.body.classList.contains('dark')
                    ? 'light'
                    : 'dark';

            try {

                localStorage.setItem(
                    THEME_KEY,
                    nextTheme
                );

            } catch (error) {

            }

            applyTheme(nextTheme);

        });
    }


    /* MODALES LOGIN / REGISTRO */
   

    const loginBtn =
        document.getElementById('loginBtn');

    const registerBtn =
        document.getElementById('registerBtn');

    const modalLogin =
        document.getElementById('modal-login');

    const modalRegister =
        document.getElementById('modal-register');

    const closes =
        document.querySelectorAll('.close');


    if (loginBtn && modalLogin) {

        loginBtn.addEventListener('click', () => {

            modalLogin.style.display = 'flex';

        });
    }

    const navigationParams = new URLSearchParams(window.location.search);
    if (
        modalLogin &&
        (navigationParams.get('plan') === 'login'
            || navigationParams.get('gestion') === 'login')
    ) {
        modalLogin.style.display = 'flex';
    }


    if (registerBtn && modalRegister) {

        registerBtn.addEventListener('click', () => {

            modalRegister.style.display = 'flex';

        });
    }

    document.querySelectorAll('.modal-switch').forEach(button => {
        button.addEventListener('click', () => {
            const nextModal = document.getElementById(button.dataset.modal);

            if (modalLogin) {
                modalLogin.style.display = 'none';
            }

            if (modalRegister) {
                modalRegister.style.display = 'none';
            }

            if (nextModal) {
                nextModal.style.display = 'flex';
            }
        });
    });


    closes.forEach(btn => {

        btn.addEventListener('click', () => {

            const modal =
                btn.closest('.modal');

            if (modal) {
                modal.style.display = 'none';
            }

        });

    });


    window.addEventListener('click', (e) => {

        if (
            e.target === modalLogin ||
            e.target === modalRegister
        ) {

            e.target.style.display = 'none';

        }

    });


    
    /* PRESENTACIÓN DE SAMMY */
    

    const sammyImage =
        document.querySelector('.sammy-image');

    const sammyPresentation =
        document.getElementById('sammy-presentation');

    const sammyTextContainer =
        document.querySelector('.sammy-text');

    const chatWidget =
        document.getElementById('chat-widget');

    const toggleChatButton =
        document.getElementById('toggle-chat');

    const sammyIntroCopy =
        document.getElementById('sammyIntroCopy');

    const sammyIntroOptions =
        document.getElementById('sammyIntroOptions');

    const sammyCharacter =
        document.getElementById('sammyCharacter');

    const startSammyPresentation = () => {
        if (!sammyPresentation || !sammyIntroCopy || !sammyIntroOptions) {
            return;
        }

        if (sammyPresentation.classList.contains('sammy-intro-started')) {
            return;
        }

        sammyPresentation.classList.add('sammy-intro-started');
        const fullText = sammyIntroCopy.dataset.fullText || '';
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        sammyIntroCopy.textContent = '';
        sammyCharacter.setAttribute('aria-expanded', 'true');

        if (prefersReducedMotion) {
            sammyIntroCopy.textContent = fullText;
            sammyPresentation.classList.add('sammy-intro-complete');
            sammyIntroCopy.setAttribute('aria-live', 'polite');
            return;
        }

        let characterIndex = 0;
        const typeNextCharacter = () => {
            if (characterIndex < fullText.length) {
                sammyIntroCopy.textContent += fullText.charAt(characterIndex);
                characterIndex += 1;
                window.setTimeout(typeNextCharacter, 24);
                return;
            }

            sammyIntroCopy.setAttribute('aria-live', 'polite');
            sammyPresentation.classList.add('sammy-intro-complete');
        };

        window.setTimeout(typeNextCharacter, 500);
    };

    if (sammyCharacter && sammyPresentation && sammyIntroCopy && sammyIntroOptions) {
        sammyCharacter.addEventListener('click', startSammyPresentation);
        sammyCharacter.addEventListener('animationend', (event) => {
            if (event.animationName === 'sammy-wave') {
                sammyCharacter.classList.remove('sammy-character-waving');
            }
        });

        sammyCharacter.addEventListener('click', () => {
            sammyCharacter.classList.add('sammy-character-waving');
        }, { once: true });

    }


    const welcomeText =
        'Tu asistente virtual en la nube. Estoy aquí para ayudarte a navegar y descubrir todo lo que Sam Software tiene para ofrecer. ¡Exploremos juntos!'
            .split(' ');


    if (chatWidget) {
        chatWidget.classList.add('hidden');
    }

    const launchSammyChatButton = document.getElementById('launchSammyChat');
    if (launchSammyChatButton && chatWidget) {
        launchSammyChatButton.addEventListener('click', () => {
            chatWidget.classList.remove('hidden', 'collapsed');
            if (chatInput) {
                chatInput.focus();
            }
        });
    }


    let sammyIntroState = 'initial';

    let sammyTypewriterTimeout = null;


    if (
        sammyTextContainer &&
        sammyTextContainer.querySelector('p') &&
        !document.querySelector('.sammy-chat-link')
    ) {

        sammyTextContainer
            .querySelector('p')
            .style.animation =
            'vibrate 0.5s linear infinite';

    }


    if (sammyImage && !document.querySelector('.sammy-chat-link')) {

        sammyImage.addEventListener('click', () => {

            if (sammyIntroState === 'closed') {
                return;
            }


            
            /* PRIMER CLICK */
           

            if (sammyIntroState === 'initial') {

                sammyIntroState = 'typing';

                sammyImage.classList.add('talking');


                if (!sammyTextContainer) {
                    return;
                }


                sammyTextContainer.innerHTML =
                    '<h2>Sammy</h2><p></p>';


                const p =
                    sammyTextContainer.querySelector('p');


                let wordIndex = 0;


                const AudioContext =
                    window.AudioContext ||
                    window.webkitAudioContext;


                const audioCtx =
                    new AudioContext();


                let charCount = 0;


                function typeWord() {

                    if (
                        !p ||
                        sammyIntroState !== 'typing'
                    ) {
                        return;
                    }


                    if (wordIndex < welcomeText.length) {

                        const word =
                            welcomeText[wordIndex];


                        if (word === 'Sam') {

                            p.innerHTML += ' Sam ';

                            wordIndex++;

                            typeWord();

                            return;
                        }


                        let letterIndex = 0;


                        function typeLetter() {

                            if (
                                !p ||
                                sammyIntroState !== 'typing'
                            ) {
                                return;
                            }


                            if (
                                letterIndex <
                                word.length
                            ) {

                                p.innerHTML +=
                                    word.charAt(letterIndex);

                                letterIndex++;

                                charCount++;


                                if (
                                    charCount % 3 === 0
                                ) {

                                    playSammyVoice(
                                        audioCtx
                                    );

                                }


                                sammyTypewriterTimeout =
                                    setTimeout(
                                        typeLetter,
                                        50
                                    );

                            } else {

                                p.innerHTML += ' ';

                                wordIndex++;

                                typeWord();

                            }

                        }


                        typeLetter();


                    } else {

                        sammyImage.classList.remove(
                            'talking'
                        );

                        sammyIntroState =
                            'finished';

                    }

                }


                typeWord();


         
            /* SEGUNDO CLICK */
            

            } else if (
                sammyIntroState === 'typing'
            ) {

                clearTimeout(
                    sammyTypewriterTimeout
                );


                if (sammyTextContainer) {

                    const p =
                        sammyTextContainer.querySelector(
                            'p'
                        );


                    if (p) {

                        p.innerHTML =
                            welcomeText.join(' ');

                    }

                }


                sammyImage.classList.remove(
                    'talking'
                );


                sammyIntroState =
                    'finished';


            
            /* TERCER CLICK */
          
            } else if (
                sammyIntroState === 'finished'
            ) {

                if (document.body.dataset.chatEnabled !== 'true') {
                    if (sammyTextContainer) {
                        sammyTextContainer.innerHTML =
                            '<h2>Chat de Sammy</h2><p>El chat está disponible con un plan pagado. <a href="#precios">Ver planes</a>.</p>';
                    }

                    window.location.hash = 'precios';
                    return;
                }

                sammyIntroState = 'closed';


                if (sammyPresentation) {

                    sammyPresentation.classList.add(
                        'fade-out-up'
                    );


                    sammyPresentation.addEventListener(
                        'animationend',
                        () => {

                            sammyPresentation.style.display =
                                'none';


                            if (chatWidget) {

                                chatWidget.classList.remove(
                                    'hidden'
                                );

                                chatWidget.classList.add(
                                    'fade-in-up'
                                );

                            }

                        },
                        {
                            once: true
                        }
                    );

                }

            }

        });

    }


   
    /* ABRIR / CERRAR CHAT */
   

    if (
        toggleChatButton &&
        chatWidget
    ) {

        toggleChatButton.addEventListener(
            'click',
            () => {

                chatWidget.classList.toggle(
                    'collapsed'
                );


                toggleChatButton.textContent =
                    chatWidget.classList.contains(
                        'collapsed'
                    )
                        ? '+'
                        : '-';

            }
        );

    }


    
    /* CARRUSEL DE PRECIOS */
   

    const pricingTrack =
        document.querySelector('.pricing-track');

    const pricingCards =
        document.querySelectorAll('.price-card');

    const pricingPrev =
        document.querySelector('.pricing-prev');

    const pricingNext =
        document.querySelector('.pricing-next');


    if (
        pricingTrack &&
        pricingCards.length
    ) {

        const groupSize = 3;

        const totalGroups =
            Math.max(
                1,
                Math.ceil(
                    pricingCards.length /
                    groupSize
                )
            );


        let currentGroup = 0;


        const updatePricingCarousel = () => {

            const gap = 36;

            const cardWidth =
                pricingCards[0].offsetWidth +
                gap;


            const offset =
                currentGroup *
                groupSize *
                cardWidth;


            pricingTrack.style.transform =
                `translateX(-${offset}px)`;


            pricingCards.forEach(
                (card, index) => {

                    const groupStart =
                        currentGroup *
                        groupSize;


                    const isInGroup =
                        index >= groupStart &&
                        index <
                        groupStart + groupSize;


                    card.classList.toggle(
                        'is-visible',
                        isInGroup
                    );


                    card.classList.remove(
                        'is-hovered',
                        'is-center'
                    );


                    if (!card.matches(':hover')) {

                        card.style.filter =
                            isInGroup
                                ? 'none'
                                : 'saturate(0.7) brightness(0.9)';


                        card.style.opacity =
                            isInGroup
                                ? '1'
                                : '0.72';


                        card.style.transform =
                            'translateY(0) scale(1)';

                    }

                }
            );

        };


        pricingCards.forEach(card => {

            card.addEventListener(
                'mouseenter',
                () => {

                    pricingCards.forEach(item => {

                        const isActive =
                            item === card;


                        item.classList.toggle(
                            'is-hovered',
                            isActive
                        );


                        item.style.filter =
                            isActive
                                ? 'none'
                                : 'saturate(0.7) brightness(0.9)';


                        item.style.opacity =
                            isActive
                                ? '1'
                                : '0.72';


                        item.style.transform =
                            isActive
                                ? 'translateY(-8px) scale(1.02)'
                                : 'translateY(0) scale(1)';

                    });

                }
            );


            card.addEventListener(
                'mouseleave',
                () => {

                    pricingCards.forEach(
                        item =>
                            item.classList.remove(
                                'is-hovered'
                            )
                    );


                    updatePricingCarousel();

                }
            );

        });


        pricingPrev?.addEventListener(
            'click',
            () => {

                currentGroup =
                    (
                        currentGroup -
                        1 +
                        totalGroups
                    ) % totalGroups;


                updatePricingCarousel();

            }
        );


        pricingNext?.addEventListener(
            'click',
            () => {

                currentGroup =
                    (
                        currentGroup +
                        1
                    ) % totalGroups;


                updatePricingCarousel();

            }
        );


        updatePricingCarousel();

    }

});



/* SAMMY - BASE DE CONOCIMIENTO */

const sammyKnowledge = {
    introduccion: 'Soy Sammy ☁️, el asistente virtual de Sam Software. Puedo orientarte sobre la página, nuestros planes y servicios. También podemos conversar; si no sé algo, te lo diré con claridad.',
    quienes_somos: 'Sam Software es una empresa enfocada en soluciones tecnológicas y servicios en la nube. Esta página permite conocer nuestros planes, crear una cuenta y contactar con el equipo.',
    mision: 'Nuestra misión es acercar herramientas digitales y servicios en la nube a personas y empresas, con una experiencia sencilla y accesible.',
    vision: 'Nuestra visión es seguir creciendo como una plataforma tecnológica innovadora y útil para nuestros usuarios.',
    valores: 'Nos importan la innovación, la seguridad, la eficiencia, la comunicación clara y el compromiso con nuestros usuarios.',
    servicios: 'En esta página encontrarás hosting, proyectos premium, dominios según el plan, soporte y soluciones para empresas. Las características disponibles dependen del plan contratado.',
    planes: 'Estos son los planes que aparecen en la página: Gratuito ($0/mes), Demo ($5.99/mes), Básico ($15.99/mes), Profesional ($27.99/mes), Semi Empresarial ($45.99/mes) y Empresarial ($55.99/mes). Puedes ver sus características completas en la sección «Planes».',
    plan_gratuito: 'El plan Gratuito cuesta $0 al mes e incluye acceso general a la página, hosting básico y soporte comunitario.',
    plan_demo: 'El plan Demo cuesta $5.99 al mes e incluye una versión premium limitada, 2 proyectos premium activos, acceso al chatbot clásico de Sammy y soporte básico.',
    plan_basico: 'El plan Básico cuesta $15.99 al mes e incluye hasta 6 proyectos premium activos, soporte prioritario, acceso al chatbot clásico de Sammy e información sobre nuevos productos.',
    plan_profesional: 'El plan Profesional cuesta $27.99 al mes e incluye hosting, hasta 10 proyectos, soporte por correo, dominio, el chatbot de Sammy y la nueva interfaz completa.',
    plan_semi_empresarial: 'El plan Semi Empresarial cuesta $45.99 al mes e incluye hosting privado, proyectos ilimitados, soporte prioritario, el chatbot de Sammy y la nueva interfaz completa, además de dominio privado.',
    plan_empresarial: 'El plan Empresarial cuesta $55.99 al mes e incluye infraestructura dedicada prioritaria, proyectos ilimitados, soporte privado 24/7, hosting mejorado, dominio, acceso anticipado a actualizaciones y la nueva interfaz completa de Sammy.',
    seguridad: 'Para proteger tu cuenta, usa una contraseña única y no la compartas. Si tienes una pregunta específica sobre las medidas de seguridad o el tratamiento de datos, consulta la información oficial o contacta con el equipo.',
    contacto: 'Puedes escribirnos mediante el formulario de la sección «Contáctanos» de esta página. No tengo confirmación de una dirección de correo de soporte, así que prefiero no inventarla.',
    como_usar: 'Puedes navegar por las secciones de la página, revisar los planes y crear una cuenta desde «Registrarme». Algunas funciones, como guardar conversaciones del chat, requieren iniciar sesión y tener un plan que permita usarlo.',
    ayuda: '¡Claro! Puedo hablarte de Sam Software, explicar los planes y servicios, orientarte por la página o simplemente conversar. ¿Qué te gustaría saber?',
    saludo: '¡Hola! 😊 Soy Sammy. ¿Cómo va tu día? También puedo ayudarte con los planes, los servicios o cualquier duda sobre esta página.',
    bienestar: '¡Gracias por preguntar! Estoy listo para charlar y ayudarte ☁️ ¿Cómo estás tú?',
    despedida: '¡Hasta pronto! Gracias por conversar conmigo ☁️',
    agradecimiento: '¡De nada! 😊 Me alegra poder ayudarte.',
    animo: 'Siento que estés pasando por eso. Si quieres, cuéntame un poco más; puedo escucharte y ayudarte a pensar en un siguiente paso.',
    conversacion: '¡Hagamos algo! Puedo contarte un chiste, hablar de tecnología o explicarte cómo funciona alguno de los planes. ¿Qué prefieres? 😊',
    felicitacion: '¡Muchas gracias! 😊 Me alegra que te guste. ¿Qué más te gustaría explorar?',
    broma: '¿Por qué el ordenador fue al médico? Porque tenía un virus… ¡menos mal que no era nada grave! 😄',
    tecnologia: 'La tecnología en la nube permite usar servicios y guardar información a través de internet, sin depender únicamente de un dispositivo. En esta web hablamos de hosting, proyectos y servicios digitales.',
    limite: 'No tengo acceso a internet ni a una IA general: respondo con información y temas preparados para este chat. Puedo ayudarte con Sam Software o conversar sobre temas cotidianos; si me das un poco más de contexto, intentaré orientarte.',
    respeto: 'Prefiero que conversemos con respeto 😊. Si algo te molestó o necesitas ayuda, cuéntame y trataré de ayudarte.'
};



/* CHAT */


const chatInput =
    document.querySelector('.chat-input input');

const chatMessages =
    document.querySelector('.chat-messages');



/* CONVERSACIÓN ACTUAL */


let conversacionActual = null;

let ultimoTemaSammy = null;



/* GUARDAR MENSAJE EN LA BASE DE DATOS */

/* CONVERSACIÓN ACTUAL */





/* GUARDAR MENSAJE EN LA BASE DE DATOS */


function guardarMensaje(mensaje, tipo) {

    const datos = new FormData();

    datos.append(
        'contenido',
        mensaje
    );

    datos.append(
        'tipo',
        tipo
    );

    if (conversacionActual !== null) {

        datos.append(
            'conversacion_id',
            conversacionActual
        );

    }

    fetch('guardar_mensaje.php', {

        method: 'POST',
        body: datos

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            if (
                conversacionActual === null &&
                data.conversacion_id
            ) {

                conversacionActual =
                    data.conversacion_id;

            }

            console.log(
                'Mensaje guardado correctamente',
                data
            );

        } else {

            console.error(
                'Error al guardar mensaje:',
                data.error
            );

        }

    })

    .catch(error => {

        console.error(
            'Error de conexión con guardar_mensaje.php:',
            error
        );

    });

}


/* AGREGAR MENSAJE VISUALMENTE */


function addMessage(
    text,
    sender = 'user'
) {

    if (!chatMessages) return;


    const msg =
        document.createElement('p');


    msg.textContent =
        text.trim();


    msg.classList.add(sender);


    chatMessages.appendChild(msg);


    chatMessages.scrollTop =
        chatMessages.scrollHeight;

}



/* RESPUESTA DE SAMMY */


function sammyReply(message) {

    if (!chatMessages) return;

    const normalizeText = (text) => text
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const normalizedMessage = normalizeText(message);
    const matchesAny = (phrases) => phrases.some((phrase) => {
        const normalizedPhrase = normalizeText(phrase);
        return ` ${normalizedMessage} `.includes(` ${normalizedPhrase} `);
    });

    const intents = [
        { topic: 'respeto', phrases: ['estupido', 'estupida', 'idiota', 'maldito', 'maldita', 'perra', 'insulto', 'vete al diablo'] },
        { topic: 'despedida', phrases: ['adios', 'hasta luego', 'hasta pronto', 'chao', 'nos vemos', 'me voy'] },
        { topic: 'agradecimiento', phrases: ['gracias', 'te agradezco', 'muy amable', 'se agradece'] },
        { topic: 'animo', phrases: ['estoy triste', 'me siento mal', 'estoy desanimado', 'estoy desanimada', 'tuve un mal dia', 'necesito hablar'] },
        { topic: 'felicitacion', phrases: ['me gustas', 'eres genial', 'eres increible', 'buen trabajo', 'te quiero', 'te amo'] },
        { topic: 'bienestar', phrases: ['como estas', 'como te va', 'que tal estas', 'como te sientes', 'estas bien'] },
        { topic: 'saludo', phrases: ['hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches', 'hey', 'que onda'] },
        { topic: 'ayuda', phrases: ['ayuda', 'que puedes hacer', 'que sabes hacer', 'en que me ayudas', 'como puedes ayudarme'] },
        { topic: 'introduccion', phrases: ['quien eres', 'como te llamas', 'tu nombre', 'que eres', 'presentate'] },
        { topic: 'quienes_somos', phrases: ['quienes somos', 'quien es sam software', 'que es sam software', 'que es esta empresa', 'la empresa'] },
        { topic: 'mision', phrases: ['mision', 'proposito de la empresa', 'objetivo de sam software'] },
        { topic: 'vision', phrases: ['vision', 'a donde quieren llegar', 'futuro de la empresa'] },
        { topic: 'valores', phrases: ['valores', 'en que creen', 'filosofia de la empresa'] },
        { topic: 'plan_gratuito', phrases: ['plan gratuito', 'plan gratis', 'plan free'] },
        { topic: 'plan_demo', phrases: ['plan demo', 'plan de prueba', 'demo'] },
        { topic: 'plan_basico', phrases: ['plan basico', 'plan basic'] },
        { topic: 'plan_profesional', phrases: ['plan profesional', 'plan professional'] },
        { topic: 'plan_semi_empresarial', phrases: ['semi empresarial', 'semiempresarial'] },
        { topic: 'plan_empresarial', phrases: ['plan empresarial', 'plan enterprise'] },
        { topic: 'planes', phrases: ['planes', 'precio', 'precios', 'cuanto cuesta', 'cuanto vale', 'cuanto valen', 'que plan', 'suscripcion', 'mensualidad'] },
        { topic: 'servicios', phrases: ['servicios', 'que ofrecen', 'que incluye', 'hosting', 'dominio', 'proyectos premium', 'desarrollo de software', 'soporte tecnico'] },
        { topic: 'seguridad', phrases: ['seguridad', 'proteger mis datos', 'mis datos', 'privacidad', 'contrasena', 'cifrado'] },
        { topic: 'contacto', phrases: ['contacto', 'contactar', 'correo', 'email', 'soporte', 'hablar con alguien', 'formulario'] },
        { topic: 'como_usar', phrases: ['como funciona', 'como uso', 'como me registro', 'crear una cuenta', 'iniciar sesion', 'como empezar', 'como se usa'] },
        { topic: 'tecnologia', phrases: ['que es la nube', 'computacion en la nube', 'tecnologia', 'internet', 'hosting en la nube'] },
        { topic: 'broma', phrases: ['cuentame un chiste', 'dime un chiste', 'hazme reir', 'algo gracioso'] },
        { topic: 'conversacion', phrases: ['me aburro', 'estoy aburrido', 'estoy aburrida'] },
        { topic: 'agradecimientos', phrases: ['gracias por confiar', 'agradecimientos'] }
    ];

    let matchedIntent = intents.find((intent) => matchesAny(intent.phrases));
    let reply = matchedIntent
        ? sammyKnowledge[matchedIntent.topic]
        : null;

    if (
        !reply &&
        matchesAny(['mas', 'cuentame mas', 'explica mas', 'amplia', 'continua', 'y luego'])
    ) {
        reply = ultimoTemaSammy
            ? `Claro 😊 Te cuento un poco más: ${sammyKnowledge[ultimoTemaSammy]}`
            : '¡Claro! ¿Sobre qué tema quieres saber más? Puedo contarte sobre los planes, los servicios o cómo funciona la página.';
        matchedIntent = ultimoTemaSammy
            ? { topic: ultimoTemaSammy }
            : null;
    }

    if (!reply && matchesAny(['estoy bien', 'muy bien', 'todo bien', 'bien gracias'])) {
        reply = '¡Me alegra saberlo! 😊 ¿Qué te gustaría hacer o conversar hoy?';
    }

    if (!reply && matchesAny(['que haces', 'que haces ahora', 'estas haciendo'])) {
        reply = 'Estoy aquí charlando contigo ☁️. ¿Quieres preguntarme algo sobre Sam Software o hablar de otro tema?';
    }

    if (!reply && matchesAny(['quien te creo', 'quien te hizo', 'quien te programo'])) {
        reply = 'Soy Sammy, el asistente virtual de esta página. Mis respuestas se basan en temas preparados por Sam Software; no soy una IA conectada a internet.';
    }

    if (!reply && matchesAny(['clima', 'tiempo hace', 'noticias de hoy', 'resultado del partido'])) {
        reply = 'No puedo consultar información en tiempo real, como el clima, noticias o resultados. Si me dices otro tema, intentaré ayudarte con lo que sé.';
    }

    if (!reply) {
        reply = sammyKnowledge.limite;
    } else if (matchedIntent) {
        const topicsWithDetails = [
            'introduccion',
            'quienes_somos',
            'mision',
            'vision',
            'valores',
            'servicios',
            'planes',
            'plan_gratuito',
            'plan_demo',
            'plan_basico',
            'plan_profesional',
            'plan_semi_empresarial',
            'plan_empresarial',
            'seguridad',
            'contacto',
            'como_usar',
            'tecnologia'
        ];
        ultimoTemaSammy = topicsWithDetails.includes(matchedIntent.topic)
            ? matchedIntent.topic
            : null;
    }


  
    /* RESPUESTA CON EFECTO DE ESCRITURA */
    

    setTimeout(() => {

        const msg =
            document.createElement('p');


        msg.classList.add(
            'bot-msg'
        );


        chatMessages.appendChild(msg);


        const chatAvatar =
            document.querySelector(
                '.chat-sammy-expanded img'
            );


        if (chatAvatar) {

            chatAvatar.classList.add(
                'talking'
            );

        }


        const AudioContext =
            window.AudioContext ||
            window.webkitAudioContext;


        const audioCtx =
            new AudioContext();


        let i = 0;

        const speed = 40;


        function typeWriter() {

            if (i < reply.length) {

                msg.textContent +=
                    reply.charAt(i);


                i++;


                chatMessages.scrollTop =
                    chatMessages.scrollHeight;


                if (i % 3 === 0) {

                    playSammyVoice(
                        audioCtx
                    );

                }


                setTimeout(
                    typeWriter,
                    speed
                );


            } else {

                if (chatAvatar) {

                    chatAvatar.classList.remove(
                        'talking'
                    );

                }


                /*
                 * GUARDAR RESPUESTA DE SAMMY
                 */

                guardarMensaje(
                    reply,
                    'sammy'
                );


                setTimeout(
                    () => audioCtx.close(),
                    100
                );

            }

        }


        typeWriter();


    }, 500);

}



/* VOZ DE SAMMY */


function playSammyVoice(audioCtx) {

    if (!audioCtx) return;


    if (
        audioCtx.state === 'suspended'
    ) {

        audioCtx.resume();

    }


    const oscillator =
        audioCtx.createOscillator();


    const gainNode =
        audioCtx.createGain();


    oscillator.type =
        'sine';


    oscillator.frequency.value =
        400 + Math.random() * 200;


    gainNode.gain.setValueAtTime(
        0.05,
        audioCtx.currentTime
    );


    gainNode.gain.exponentialRampToValueAtTime(
        0.001,
        audioCtx.currentTime + 0.05
    );


    oscillator.connect(
        gainNode
    );


    gainNode.connect(
        audioCtx.destination
    );


    oscillator.start();


    oscillator.stop(
        audioCtx.currentTime + 0.05
    );

}



/* ENVIAR MENSAJE CON ENTER */


if (chatInput) {

    chatInput.addEventListener(
        'keypress',
        (e) => {

            if (
                e.key === 'Enter' &&
                chatInput.value.trim() !== ''
            ) {

                const userMsg =
                    chatInput.value.trim();


                /*
                 * MOSTRAR MENSAJE DEL USUARIO
                 */

                addMessage(
                    userMsg,
                    'user'
                );


                /*
                 * GUARDAR MENSAJE DEL USUARIO
                 */

                guardarMensaje(
                    userMsg,
                    'usuario'
                );


                /*
                 * LIMPIAR INPUT
                 */

                chatInput.value = '';


                /*
                 * HACER QUE SAMMY RESPONDA
                 */

                sammyReply(
                    userMsg
                );

            }

        }
    );

}