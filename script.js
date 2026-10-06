

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.mode-toggle');
  const starsContainer = document.getElementById('stars');

  
  for (let i = 0; i < 60; i++) {
    const s = document.createElement('div');
    s.classList.add('star');
    s.style.top = `${Math.random() * 100}%`;
    s.style.left = `${Math.random() * 100}%`;
    s.style.animationDuration = `${3 + Math.random() * 4}s`;
    starsContainer.appendChild(s);
  }

  
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    toggle.textContent = document.body.classList.contains('dark') ? '☀️' : '🌙';
  });

  
  const loginBtn = document.getElementById('loginBtn');
  const registerBtn = document.getElementById('registerBtn');
  const modalLogin = document.getElementById('modal-login');
  const modalRegister = document.getElementById('modal-register');
  const closes = document.querySelectorAll('.close');

  loginBtn.onclick = () => modalLogin.style.display = 'flex';
  registerBtn.onclick = () => modalRegister.style.display = 'flex';

  closes.forEach(btn => btn.onclick = () => btn.parentElement.parentElement.style.display = 'none');

  window.onclick = e => {
    if (e.target === modalLogin || e.target === modalRegister) e.target.style.display = 'none';
  };

  const sammyImage = document.querySelector('.sammy-image');
  const sammyPresentation = document.getElementById('sammy-presentation');
  const sammyTextContainer = document.querySelector('.sammy-text');
  const chatWidget = document.getElementById('chat-widget');
  const toggleChatButton = document.getElementById('toggle-chat');
  let animationState = 'initial'; 
  let typewriterTimeout;

  
  sammyTextContainer.querySelector('p').style.animation = 'vibrate 0.5s linear infinite';

  sammyImage.addEventListener('click', () => {
    const welcomeText = 'Tu asistente virtual en la nube. Estoy aquí para ayudarte a navegar y descubrir todo lo que Sam Software tiene para ofrecer. ¡Exploremos juntos!'.split(' ');

    if (animationState === 'initial') {
      animationState = 'typing';
      sammyImage.classList.add('talking');
      sammyTextContainer.innerHTML = '<h2>Sammy</h2><p></p>';
      const p = sammyTextContainer.querySelector('p');
      let wordIndex = 0;

      function typeWord() {
        if (wordIndex < welcomeText.length) {
          const word = welcomeText[wordIndex];
          if (word === 'Sam') {
            p.innerHTML += ' Sam ';
            wordIndex++;
            typeWord();
          } else {
            let letterIndex = 0;
            function typeLetter() {
              if (letterIndex < word.length) {
                p.innerHTML += word.charAt(letterIndex);
                letterIndex++;
                typewriterTimeout = setTimeout(typeLetter, 50);
              } else {
                p.innerHTML += ' ';
                wordIndex++;
                typeWord();
              }
            }
            typeLetter();
          }
        } else {
          sammyImage.classList.remove('talking');
          animationState = 'finished';
        }
      }

      typeWord();
    } else if (animationState === 'typing') {
      clearTimeout(typewriterTimeout);
      const p = sammyTextContainer.querySelector('p');
      p.innerHTML = welcomeText.join(' ');
      sammyImage.classList.remove('talking');
      animationState = 'finished';
    } else if (animationState === 'finished') {
      sammyPresentation.classList.add('fade-out-up');
      sammyPresentation.addEventListener('animationend', () => {
        sammyPresentation.style.display = 'none';
        chatWidget.classList.remove('hidden');
        chatWidget.classList.add('fade-in-up');
        animationState = 'chat-active';
      }, { once: true });
    }
  });

  toggleChatButton.addEventListener('click', () => {
    chatWidget.classList.toggle('collapsed');
    if (chatWidget.classList.contains('collapsed')) {
      toggleChatButton.textContent = '+'; 
    } else {
      toggleChatButton.textContent = '-';
    }
  });
});

const sammyKnowledge = {
  "introduccion": `
    Soy Sammy ☁️, el asistente virtual de Sam Software. 
    Mi objetivo es ayudarte a conocer nuestra página, nuestros servicios y resolver tus dudas sobre la nube.
  `,

  "quienes_somos": `
    Sam Software es una empresa innovadora enfocada en desarrollar soluciones tecnológicas en la nube.
    Combinamos creatividad, seguridad y velocidad para ofrecer plataformas accesibles desde cualquier lugar del mundo.
  `,

  "mision": `
    Nuestra misión es impulsar la transformación digital mediante herramientas en la nube seguras, rápidas e intuitivas,
    diseñadas para potenciar tanto a empresas como a usuarios individuales.
  `,

  "vision": `
    Nuestra visión es convertirnos en líderes del sector tecnológico,
    siendo reconocidos por nuestra innovación, servicio al cliente y compromiso con la sostenibilidad digital.
  `,

  "valores": `
    En Sam Software creemos en:
    🌟 Innovación constante
    🔒 Seguridad total
    ⚡ Eficiencia y rapidez
    💬 Comunicación transparente
    🤝 Compromiso con nuestros usuarios
  `,

  "servicios": `
    Ofrecemos una variedad de servicios en la nube, incluyendo:
    ☁️ Hosting escalable
    ⚙️ Automatización empresarial
    💼 Desarrollo de software a medida
    🔧 Soporte técnico personalizado
  `,

  "planes": `
    Tenemos tres planes diseñados para cada necesidad:
    💻 Básico — $15/mes, incluye hosting y soporte por correo.
    🚀 Profesional — $30/mes, con hosting, dominio y soporte prioritario.
    🏢 Empresarial — $60/mes, con infraestructura dedicada y soporte 24/7.
  `,

  "seguridad": `
    Implementamos encriptación avanzada y copias de seguridad constantes para proteger tus datos.
    Cada proyecto cuenta con monitoreo en tiempo real y protocolos de acceso seguro.
  `,

  "contacto": `
    Puedes contactarnos a través de nuestro formulario en la sección "Contáctanos" de la web,
    o escribirnos directamente a soporte@samsoftware.com ☁️
  `,

  "agradecimientos": `
    Gracias por confiar en Sam Software. Seguiremos mejorando para ofrecerte la mejor experiencia posible. 🌈
  `
};

const chatInput = document.querySelector('.chat-input input');
const chatMessages = document.querySelector('.chat-messages');

function addMessage(text, sender = 'user') {
  const msg = document.createElement('p');
  msg.textContent = text.trim();
  msg.classList.add(sender);
  chatMessages.appendChild(msg);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sammyReply(message) {
  const lower = message.toLowerCase();
  let reply = "No entiendo muy bien eso, ¿puedes explicarlo de otra forma?";

  
  if (lower.includes('hola') || lower.includes('buenas')) {
    reply = "¡Hola! Soy Sammy ☁️, tu asistente en la nube. ¿En qué puedo ayudarte?";
  } else if (lower.includes('adiós') || lower.includes('chao')) {
    reply = "¡Hasta pronto! ☁️";
  } else if (lower.includes('gracias')) {
    reply = "¡De nada! 😊 Estoy aquí para ayudarte.";
  }

  
  else if (lower.includes('quién') || lower.includes('quienes somos')) {
    reply = sammyKnowledge.quienes_somos;
  } else if (lower.includes('misión')) {
    reply = sammyKnowledge.mision;
  } else if (lower.includes('visión')) {
    reply = sammyKnowledge.vision;
  } else if (lower.includes('valores')) {
    reply = sammyKnowledge.valores;
  } else if (lower.includes('servicio')) {
    reply = sammyKnowledge.servicios;
  } else if (lower.includes('plan')) {
    reply = sammyKnowledge.planes;
  } else if (lower.includes('seguridad')) {
    reply = sammyKnowledge.seguridad;
  } else if (lower.includes('contacto') || lower.includes('correo')) {
    reply = sammyKnowledge.contacto;
  } else if (lower.includes('empresa') || lower.includes('sam software')) {
    reply = sammyKnowledge.introduccion;
  } else if (lower.includes('gracias')) {
    reply = sammyKnowledge.agradecimientos;
  }

  
  setTimeout(() => addMessage(reply, 'sammy'), 500);
}

chatInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter' && chatInput.value.trim() !== '') {
    const userMsg = chatInput.value.trim();
    addMessage(userMsg, 'user');
    chatInput.value = '';
    sammyReply(userMsg);
  }
});
