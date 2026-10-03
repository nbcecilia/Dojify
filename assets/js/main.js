// ==========================================
// NOTIFICAÇÕES (MENU DROPDOWN DO HEADER)
// ==========================================
// ==========================================
// NOTIFICAÇÕES (MENU DROPDOWN DO HEADER)
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    const notificationMenu = document.querySelector('[data-notification-menu]');

    if (notificationMenu) {
        const notificationContainer = notificationMenu.closest('.dropdown') || notificationMenu;
        const storageKey = notificationMenu.dataset.storageKey;
        const notifications = Array.from(notificationMenu.querySelectorAll('[data-notification-id]'));
        let readIds = [];

        try {
            const storedIds = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
            if (Array.isArray(storedIds) && storedIds.every(function (id) { return typeof id === 'string'; })) {
                readIds = storedIds;
            } else {
                console.error('O estado salvo das notificações do aluno tem um formato inválido.');
            }
        } catch (error) {
            console.error('Não foi possível carregar o estado das notificações do aluno.', error);
        }

        function persistReadIds() {
            try {
                window.localStorage.setItem(storageKey, JSON.stringify(readIds));
            } catch (error) {
                console.error('Não foi possível salvar o estado das notificações do aluno.', error);
            }
        }

        function renderNotifications() {
            const unreadCount = notifications.filter(function (notification) {
                return !readIds.includes(notification.dataset.notificationId);
            }).length;

            const badge = notificationContainer.querySelector('[data-notification-unread-badge]');
            if (badge) {
                badge.textContent = String(unreadCount);
                badge.hidden = unreadCount === 0;
                badge.style.display = unreadCount === 0 ? 'none' : '';
                badge.classList.toggle('d-none', unreadCount === 0);
            }

            const count = notificationMenu.querySelector('[data-notification-unread-count]');
            if (count) {
                count.textContent = unreadCount + (unreadCount === 1 ? ' nova' : ' novas');
            }

            notifications.forEach(function (notification) {
                const isRead = readIds.includes(notification.dataset.notificationId);
                const readButton = notification.querySelector('[data-notification-mark-read]');
                notification.classList.toggle('opacity-75', isRead);

                if (readButton) {
                    const label = isRead ? 'Notificação lida' : 'Marcar como lida';
                    const icon = readButton.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-envelope-check', !isRead);
                        icon.classList.toggle('bi-check2-all', isRead);
                    }
                    readButton.setAttribute('title', label);
                    readButton.setAttribute('aria-label', label);
                    readButton.setAttribute('aria-pressed', isRead ? 'true' : 'false');

                    const tooltip = window.bootstrap && window.bootstrap.Tooltip.getInstance(readButton);
                    if (tooltip) {
                        tooltip.setContent({ '.tooltip-inner': label });
                    }
                }
            });
        }

        function markAsRead(notification) {
            if (!notification) {
                return;
            }

            const id = notification.dataset.notificationId;
            if (id && !readIds.includes(id)) {
                readIds.push(id);
                persistReadIds();
                renderNotifications();
            }
        }

        notificationMenu.addEventListener('click', function (event) {
            if (!(event.target instanceof Element)) {
                return;
            }

            const markButton = event.target.closest('[data-notification-mark-read]');
            if (markButton) {
                markAsRead(markButton.closest('[data-notification-id]'));
                return;
            }

            if (event.target.closest('[data-notification-mark-all]')) {
                notifications.forEach(function (notification) {
                    if (!readIds.includes(notification.dataset.notificationId)) {
                        readIds.push(notification.dataset.notificationId);
                    }
                });
                persistReadIds();
                renderNotifications();
                return;
            }

            const action = event.target.closest('[data-notification-action]');
            if (action) {
                markAsRead(action.closest('[data-notification-id]'));
            }
        });

        renderNotifications();

        if (window.bootstrap && window.bootstrap.Tooltip) {
            notificationMenu.querySelectorAll('[data-bs-toggle="tooltip"], [data-notification-tooltip]').forEach(function (element) {
                new window.bootstrap.Tooltip(element);
            });
        }
    }

    const btnNotificacao = document.getElementById('btnNotificacao');
    const dropdownNotificacoes = document.getElementById('dropdownNotificacoes');

    if (btnNotificacao && dropdownNotificacoes) {
        btnNotificacao.addEventListener('click', function (e) {
            e.stopPropagation(); // Evita que o clique feche imediatamente
            dropdownNotificacoes.classList.toggle('show');
        });

        // Fecha o dropdown se clicar fora dele
        document.addEventListener('click', function (e) {
            if (!dropdownNotificacoes.contains(e.target) && e.target !== btnNotificacao) {
                dropdownNotificacoes.classList.remove('show');
            }
        });
    }
});

// ==========================================
// MENSAGEM DE SUCESSO OU ERRO
// ==========================================

function mostrarMensagem(tipo, mensagem) {
    const mensagemExistente = document.querySelector('.toast');

    if (mensagemExistente) {
        mensagemExistente.remove();
    }

    const toast = document.createElement('div');

    toast.className = 'toast toast--' + tipo;
    toast.textContent = mensagem;

    document.body.appendChild(toast);

    setTimeout(function () {
        toast.classList.add('toast-show');
    }, 100);

    setTimeout(function () {
        toast.classList.remove('toast-show');

        setTimeout(function () {
            toast.remove();
        }, 300);

    }, 4000);
}


// ==========================================
// CONFIRMAÇÃO DE AÇÃO
// ==========================================

document.addEventListener('click', function (event) {
    const elemento = event.target.closest('[data-confirmar]');

    if (!elemento) {
        return;
    }

    const mensagem = elemento.getAttribute('data-confirmar');

    if (!confirm(mensagem)) {
        event.preventDefault();
    }
});


// ==========================================
// MENSAGENS VINDAS DA URL
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    const parametros = new URLSearchParams(window.location.search);

    const sucesso = parametros.get('sucesso');
    const erro = parametros.get('erro');

    if (sucesso) {
        let mensagem = 'Operação realizada com sucesso!';

        switch (sucesso) {
            case 'cadastrado':
                mensagem = 'Cadastro realizado com sucesso!';
                break;
            case 'professor_cadastrado':
                mensagem = 'Professor cadastrado com sucesso!';
                break;
            case 'atualizado':
                mensagem = 'Dados atualizados com sucesso!';
                break;
            case 'recebimento_registado':
                mensagem = 'Recebimento confirmado com sucesso!';
                break;
            case 'excluido':
                mensagem = 'Registro excluído com sucesso!';
                break;
        }

        mostrarMensagem('sucesso', mensagem);
    }

    if (erro) {
        let mensagem = 'Não foi possível realizar a operação.';

        switch (erro) {
            case 'falha_cadastro':
                mensagem = 'Não foi possível realizar o cadastro.';
                break;
            case 'falha_recebimento':
                mensagem = 'Não foi possível confirmar o recebimento.';
                break;
            case 'cpf_duplicado':
                mensagem = 'Este CPF já está cadastrado.';
                break;
        }

        mostrarMensagem('erro', mensagem);
    }
});