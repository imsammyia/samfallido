document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const csrfToken = body.dataset.csrfToken;
  const userName = body.dataset.userName || 'Usuario';
  const sidebar = document.getElementById('sidebar');
  const toast = document.getElementById('toast');
  const messageList = document.getElementById('messageList');
  const conversationTitle = document.getElementById('conversationTitle');
  const deleteButton = document.getElementById('deleteConversation');
  const conversationSearch = document.getElementById('conversationSearch');
  const themeToggle = document.getElementById('themeToggle');
  const seasonToggle = document.getElementById('seasonToggle');
  const seasonLabel = document.getElementById('seasonLabel');
  const seasonOrder = ['spring', 'summer', 'autumn', 'winter'];
  const seasonStorageKey = 'sam-software-season';
  const themeStorageKey = 'sam-software-theme';
  let conversations = [];
  let activeConversationId = null;
  let toastTimer;

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('show'), 3200);
  }

  async function request(action, payload) {
    const options = payload
      ? {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ...payload, csrf_token: csrfToken })
        }
      : { method: 'GET' };
    const response = await fetch(`sammy_api.php?action=${encodeURIComponent(action)}`, options);
    let data;
    try {
      data = await response.json();
    } catch (error) {
      throw new Error('El servidor devolvió una respuesta que no se pudo leer.');
    }
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'No se pudo completar la solicitud.');
    }
    return data;
  }

  function setView(viewName) {
    document.querySelectorAll('[data-view-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.viewPanel !== viewName;
    });
    const activeNav = viewName === 'Conversación' ? 'Conversaciones' : viewName;
    document.querySelectorAll('[data-nav]').forEach((item) => {
      item.classList.toggle('active', item.dataset.nav === activeNav);
    });
    document.getElementById('currentSection').textContent = viewName;
    sidebar.classList.remove('open');
    if (viewName === 'Inicio') {
      activeConversationId = null;
    }
  }

  function formatDate(value) {
    if (!value) return 'Ahora';
    const date = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat('es-MX', {
      dateStyle: 'medium',
      timeStyle: 'short'
    }).format(date);
  }

  function makeConversationButton(conversation) {
    const button = document.createElement('button');
    button.className = 'conversation-item';
    button.type = 'button';
    button.dataset.conversationId = conversation.id;

    const icon = document.createElement('span');
    icon.className = 'conversation-symbol';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '▤';

    const copy = document.createElement('span');
    copy.className = 'conversation-copy';
    const title = document.createElement('strong');
    title.textContent = conversation.titulo;
    const date = document.createElement('small');
    date.textContent = formatDate(conversation.fecha_creacion);
    copy.append(title, date);
    button.append(icon, copy);
    return button;
  }

  function renderList(target, items, emptyText) {
    target.replaceChildren();
    if (items.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'conversation-empty';
      empty.textContent = emptyText;
      target.appendChild(empty);
      return;
    }
    items.forEach((conversation) => target.appendChild(makeConversationButton(conversation)));
  }

  function renderConversations() {
    const filter = conversationSearch ? conversationSearch.value.trim().toLocaleLowerCase('es') : '';
    const filtered = conversations.filter((conversation) =>
      conversation.titulo.toLocaleLowerCase('es').includes(filter)
    );
    renderList(
      document.getElementById('sidebarConversations'),
      conversations.slice(0, 8),
      'Todavía no tienes conversaciones.'
    );
    renderList(
      document.getElementById('recentConversations'),
      conversations.slice(0, 4),
      'Cuando converses con Sammy, tus chats aparecerán aquí.'
    );
    renderList(
      document.getElementById('allConversations'),
      filtered,
      filter ? 'No hay conversaciones que coincidan con tu búsqueda.' : 'Todavía no tienes conversaciones.'
    );
    renderList(
      document.getElementById('libraryConversations'),
      conversations,
      'Tu biblioteca se llenará cuando inicies una conversación.'
    );
  }

  async function refreshConversations() {
    const data = await request('list');
    conversations = data.conversations;
    renderConversations();
  }

  function renderMessage(message) {
    const row = document.createElement('article');
    const isUserMessage = message.tipo === 'user' || message.tipo === 'usuario';
    row.className = `message-row ${isUserMessage ? 'from-user' : 'from-sammy'}`;
    const avatar = document.createElement('span');
    avatar.className = 'message-avatar';
    avatar.textContent = isUserMessage ? userName.slice(0, 1).toLocaleUpperCase('es') : '✦';
    avatar.setAttribute('aria-hidden', 'true');

    const content = document.createElement('div');
    content.className = 'message-content';
    const sender = document.createElement('strong');
    sender.textContent = isUserMessage ? 'Tú' : 'Sammy';
    const text = document.createElement('p');
    text.textContent = message.contenido;
    const time = document.createElement('time');
    time.textContent = formatDate(message.fecha);
    content.append(sender, text, time);
    row.append(avatar, content);
    messageList.appendChild(row);
  }

  function renderMessages(messages) {
    messageList.replaceChildren();
    if (messages.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'empty-chat';
      const title = document.createElement('strong');
      title.textContent = `¡Hola, ${userName}!`;
      const copy = document.createElement('p');
      copy.textContent = 'Escribe tu primera pregunta y Sammy te responderá con la información local de Sam Software.';
      empty.append(title, copy);
      messageList.appendChild(empty);
      return;
    }
    messages.forEach(renderMessage);
    messageList.scrollTop = messageList.scrollHeight;
  }

  function updateCurrentConversation(conversation) {
    activeConversationId = Number(conversation.id);
    conversationTitle.textContent = conversation.titulo;
    deleteButton.disabled = false;
    setView('Conversación');
    document.getElementById('currentSection').textContent = 'Conversación';
  }

  async function openConversation(conversationId) {
    try {
      const data = await request('load', { conversation_id: Number(conversationId) });
      updateCurrentConversation(data.conversation);
      renderMessages(data.messages);
    } catch (error) {
      showToast(error.message);
    }
  }

  async function sendMessage(message, conversationId = null) {
    const submitButtons = document.querySelectorAll('.composer .send-button');
    submitButtons.forEach((button) => { button.disabled = true; });
    try {
      const data = await request('send', {
        message,
        conversation_id: conversationId
      });
      updateCurrentConversation(data.conversation);
      if (conversationId === null) renderMessages([]);
      data.messages.forEach(renderMessage);
      messageList.scrollTop = messageList.scrollHeight;
      document.getElementById('promptInput').value = '';
      document.getElementById('messageInput').value = '';
      document.getElementById('attachmentLabel').hidden = true;
      document.getElementById('chatAttachmentLabel').hidden = true;
      await refreshConversations();
      document.getElementById('messageInput').focus();
    } catch (error) {
      showToast(error.message);
    } finally {
      submitButtons.forEach((button) => { button.disabled = false; });
    }
  }

  document.querySelectorAll('[data-nav]').forEach((item) => {
    item.addEventListener('click', () => setView(item.dataset.nav));
  });

  document.querySelectorAll('[data-open-view]').forEach((button) => {
    button.addEventListener('click', () => setView(button.dataset.openView));
  });

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-conversation-id]');
    if (button) {
      openConversation(button.dataset.conversationId);
    }
  });

  document.querySelectorAll('[data-prompt]').forEach((button) => {
    button.addEventListener('click', () => {
      setView('Inicio');
      const input = document.getElementById('promptInput');
      input.value = button.dataset.prompt;
      input.focus();
    });
  });

  document.getElementById('promptForm').addEventListener('submit', (event) => {
    event.preventDefault();
    const input = document.getElementById('promptInput');
    const message = input.value.trim();
    if (message) {
      sendMessage(message);
    } else {
      showToast('Escribe tu pregunta antes de enviarla.');
      input.focus();
    }
  });

  document.getElementById('chatForm').addEventListener('submit', (event) => {
    event.preventDefault();
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (message) {
      sendMessage(message, activeConversationId);
    } else {
      showToast('Escribe tu mensaje antes de enviarlo.');
      input.focus();
    }
  });

  document.getElementById('newConversation').addEventListener('click', async () => {
    try {
      const data = await request('create', {});
      updateCurrentConversation(data.conversation);
      renderMessages([]);
      await refreshConversations();
      document.getElementById('messageInput').focus();
    } catch (error) {
      showToast(error.message);
    }
  });

  deleteButton.addEventListener('click', async () => {
    if (activeConversationId === null || !window.confirm('¿Eliminar esta conversación y todos sus mensajes?')) return;
    try {
      await request('delete', { conversation_id: activeConversationId });
      activeConversationId = null;
      deleteButton.disabled = true;
      await refreshConversations();
      setView('Conversaciones');
      showToast('La conversación se eliminó.');
    } catch (error) {
      showToast(error.message);
    }
  });

  if (conversationSearch) {
    conversationSearch.addEventListener('input', renderConversations);
  }

  document.getElementById('menuButton').addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  function setTheme(isDark) {
    body.classList.toggle('dark', isDark);
    if (themeToggle) themeToggle.checked = isDark;
    try {
      localStorage.setItem(themeStorageKey, isDark ? 'dark' : 'light');
    } catch (error) {
      showToast('No se pudo guardar la preferencia de tema en este navegador.');
    }
  }

  function applySeason(season) {
    const seasons = {
      spring: { label: 'Primavera', icon: '🌸', particles: 18, symbol: '✿' },
      summer: { label: 'Verano', icon: '☀️', particles: 12, symbol: '✦' },
      autumn: { label: 'Otoño', icon: '🍂', particles: 20, symbol: '🍂' },
      winter: { label: 'Invierno', icon: '❄️', particles: 24, symbol: '❄' }
    };
    const selected = seasons[season] ? season : 'summer';
    const current = seasons[selected];
    body.dataset.season = selected;
    if (seasonLabel) seasonLabel.textContent = current.label;
    if (seasonToggle) {
      const label = `Cambiar estación. Actual: ${current.label}`;
      seasonToggle.setAttribute('aria-label', label);
      seasonToggle.title = label;
      const icon = seasonToggle.querySelector('.season-icon');
      if (icon) icon.textContent = current.icon;
    }

    const container = document.getElementById('season-effects');
    if (!container) return;
    container.replaceChildren();
    for (let index = 0; index < current.particles; index++) {
      const particle = document.createElement('span');
      particle.className = 'season-particle';
      particle.dataset.season = selected;
      particle.textContent = current.symbol;
      particle.style.left = `${Math.random() * 100}%`;
      particle.style.animationDelay = `${-Math.random() * 20}s`;
      particle.style.animationDuration = `${11 + Math.random() * 16}s`;
      particle.style.fontSize = `${12 + Math.random() * 16}px`;
      container.appendChild(particle);
    }
  }

  if (themeToggle) {
    let savedTheme = 'light';
    try {
      savedTheme = localStorage.getItem(themeStorageKey) || 'light';
    } catch (error) {
      showToast('No se pudo leer la preferencia de tema de este navegador.');
    }
    setTheme(savedTheme === 'dark');
    themeToggle.addEventListener('change', () => setTheme(themeToggle.checked));
  }

  if (seasonToggle) {
    let savedSeason = 'summer';
    try {
      savedSeason = localStorage.getItem(seasonStorageKey) || 'summer';
    } catch (error) {
      savedSeason = 'summer';
    }
    applySeason(seasonOrder.includes(savedSeason) ? savedSeason : 'summer');
    seasonToggle.addEventListener('click', () => {
      const currentSeason = body.dataset.season || 'summer';
      const currentIndex = seasonOrder.indexOf(currentSeason);
      const nextSeason = seasonOrder[(currentIndex + 1) % seasonOrder.length];
      try {
        localStorage.setItem(seasonStorageKey, nextSeason);
      } catch (error) {
        // sin efectos si no es posible guardar la preferencia
      }
      applySeason(nextSeason);
    });
  }

  window.addEventListener('storage', (event) => {
    if (event.key === themeStorageKey && (event.newValue === 'dark' || event.newValue === 'light')) {
      setTheme(event.newValue === 'dark');
    }
    if (event.key === seasonStorageKey && event.newValue && seasonOrder.includes(event.newValue)) {
      applySeason(event.newValue);
    }
  });

  function connectTextAttachment(buttonId, inputId, labelId, textareaId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    const label = document.getElementById(labelId);
    const textarea = document.getElementById(textareaId);

    button.addEventListener('click', () => input.click());
    input.addEventListener('change', async () => {
      const file = input.files[0];
      if (!file) return;
      try {
        const text = await file.text();
        if (text.length > 3000) {
          throw new Error('El archivo supera el límite local de 3,000 caracteres.');
        }
        const attachmentText = `${textarea.value}${textarea.value ? '\n\n' : ''}[Contenido de ${file.name}]\n${text}`;
        if (attachmentText.length > 4000) {
          throw new Error('El texto adjunto superaría el límite de 4,000 caracteres del mensaje.');
        }
        textarea.value = attachmentText;
        label.textContent = `Adjunto: ${file.name}`;
        label.hidden = false;
        textarea.focus();
      } catch (error) {
        showToast(error.message || 'No se pudo leer el archivo de texto.');
      } finally {
        input.value = '';
      }
    });
  }

  connectTextAttachment('attachButton', 'attachmentInput', 'attachmentLabel', 'promptInput');
  connectTextAttachment('attachChatButton', 'chatAttachmentInput', 'chatAttachmentLabel', 'messageInput');

  refreshConversations().catch((error) => {
    showToast(error.message);
    renderConversations();
  });
});
